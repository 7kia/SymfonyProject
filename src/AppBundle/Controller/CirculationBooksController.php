<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ApplicationForBook;
use AppBundle\Entity\Book;
use AppBundle\Entity\TakenBook;
use AppBundle\Entity\User;
use AppBundle\Entity\UserListBook;
use AppBundle\Controller\MyController;
use AppBundle\DatabaseManagement\SearchData;
use AppBundle\DatabaseManagement\DatabaseManager;

use AppBundle\Security\ApplicationStatus;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;


class CirculationBooksController extends MyController
{
    private $deleteCommandValue = null;
    private $acceptCommandValue = null;
    private $bookListName = null;
    /**
     * @Route("/circulation_books", name="circulation_books" )
     */
    public function showPage(Request $request)
    {
        return $this->generatePage($request);
    }

    protected function getGenerationDataFromUrl()
    {
        $this->bookListName = $this->getParamFromGetRequest('book_list_name');

        return array(
            'book_list_name' => $this->bookListName
        );
    }

    protected function checkGenerationDataForPage($generationDataForPage)
    {
        $this->checkBookListName($generationDataForPage['book_list_name']);
    }

    /**
     * @param $bookListName
     */
    private function checkBookListName($bookListName)
    {
        $bookList = array(
            'taken_books',
            'given_books',
            'applications',
        );

        if ($bookListName == null) {
            throw new Exception($this->getMessageAboutLackArgument('book_list_name'));
        }
        if (!in_array($bookListName, $bookList)) {
            throw new Exception(
                'book_list_name должен иметь одно из следующих значений '
                . implode(",", $bookList)
            );
        }
    }

    protected function getCommandDataFromUrl()
    {
        $this->deleteCommandValue = $this->getParamFromGetRequest('delete');
        $this->acceptCommandValue = $this->getParamFromGetRequest('accept');
        $otherUserName = $this->getParamFromGetRequest('other_user');

        $typeRequest = $this->getRequestType();

        return array(
            'delete' => $this->deleteCommandValue,
            'accept' => $this->acceptCommandValue,
            'other_user' => $otherUserName,
            'typeRequest' => $typeRequest,
            'bookId' => $this->getCommandArgument($typeRequest),
            'currentUser' => $this->getCurrentUser(),
            'otherUserObject' => $this->databaseManager->getOneThingByCriteria($otherUserName, 'id', User::class)
        );
    }

    protected function checkCommandData($commandData)
    {
        $this->checkCommands($this->bookListName);
        $this->checkOtherUser($commandData['other_user'], $commandData['typeRequest']);
    }

    protected function commandProcessing($commandData)
    {
        if ($commandData['typeRequest'] != null) {
            if ($this->executeRequest($commandData, $this->bookListName)) {
                $this->redirectData = array(
                    'route' =>'circulation_books',
                    'arguments' => array(
                        'book_list_name' => $this->bookListName,

                    )
                );

            } else {
                throw new Exception('Запрос ' . $commandData['typeRequest'] . ' неудался');
            }
        }
    }

    protected function generatePageData($request, $generationDataForPage)
    {
        $currentUserData = $this->getCurrentUser();

        // TODO : исправь перевод в строковый формат {# { bookData[i].deadline }}#}

        return array_merge(
            MyController::generatePageData($request, $generationDataForPage),
            array(
                'pageName' => 'circulation_books',
                'userLogin' => $this->userAuthorized(),
                'bookData' => $this->getTableData($this->bookListName, $currentUserData->getId()),
                'book_list_name' => $this->bookListName
            )
        );
    }

    private function getStringDeadline($bookData)
    {
        // TODO : неправильный перевод даты в строковый формат
        $deadlines = array();
        foreach ($bookData as $data) {
            //print_r($data);
            array_push($deadlines, $data->getDeadline()->format('Y-m-d H:i:s'));
        }

        return $deadlines;
    }

    private function getUsernames($userIds)
    {
        $users = $this->databaseManager->getThings($userIds, User::class);
        $userNames = array();
        foreach ($users as $user) {
            if ($user != null) {
                array_push($userNames, $user->getUsername());
            }
        }

        return $userNames;
    }

    private function generateTableData($bookIds, $userIds)
    {
        $bookData = $this->databaseManager->getThings($bookIds, Book::class);
        $userNames = $this->getUsernames($userIds);

        return array(
            'books' => $bookData,
            'deadlines' => $this->getStringDeadline($bookData),
            'users' => $userNames,
            'userId' => $userIds
        );
    }

    /**
     * @param $getId
     * @return mixed
     */
    private function getTakenBookTableData($getId)
    {
        $takenBooks = $this->databaseManager->findThingByCriteria(
            'AppBundle\Entity\TakenBook',
            array(
                'applicantId' => $getId
            )
        );

        $bookIds = array();
        $userIds = array();
        foreach ($takenBooks as $takenBook) {
            array_push($bookIds, $takenBook->getBookId());
            array_push($userIds, $takenBook->getOwnerId());
        }
        return $this->generateTableData($bookIds, $userIds);
    }

    /**
     * @param $getId
     * @return mixed
     */
    private function getGivenBookTableData($getId)
    {
        $givenBooks =  $this->databaseManager->findThingByCriteria(
            'AppBundle\Entity\TakenBook',
            array(
                'ownerId' => $getId
            )
        );

        // TODO : убери дублирование
        $bookIds = array();
        $users = array();
        foreach ($givenBooks as $givenBook) {
            array_push($bookIds, $givenBook->getBookId());
            array_push($users, $givenBook->getApplicantId());
        }
        return $this->generateTableData($bookIds, $users);
    }

    /**
     * @param $getId
     * @return mixed
     */
    private function getApplicationTableData($getId)
    {
        $applicationForBooks = $this->databaseManager->findThingByCriteria(
            'AppBundle\Entity\ApplicationForBook',
            array(
                'ownerId' => $getId
            )
        );

        $bookIds = array();
        $users = array();
        foreach ($applicationForBooks as $applicationForBook) {
            array_push($bookIds, $applicationForBook->getBookId());
            array_push($users, $applicationForBook->getApplicantId());
        }

        return $this->generateTableData($bookIds, $users);
    }

    /**
     * @param $ownerName
     * @return null|string
     */
    private function checkOwnerName($ownerName)
    {
        // TODO : не хватает проверки существования пользователя
        if ($ownerName == null) {
            return $this->getMessageAboutLackArgument('ownerName');
        }
        return null;
    }


    /**
     * @param $otherUser
     * @param $typeRequest
     */
    private function checkOtherUser($otherUser, $typeRequest)
    {
        if (($otherUser == null) and ($typeRequest != null)) {
            throw new Exception($this->getMessageAboutLackArgument('other_user'));
        }
    }

    /**
     * @param $bookListName
     * @return null|string
     */
    private function checkCommands($bookListName)
    {
        if (($this->acceptCommandValue != null) and ($this->deleteCommandValue != null)) {
            throw new Exception('Нельзя одновременно использовать запросы delete и accept');
        }
        if (($this->acceptCommandValue != null) and ($bookListName != 'applications')) {
            throw new Exception('Использовать запрос accept можно только в каталоге applications');
        }
        if (($this->deleteCommandValue != null) and ($this->deleteCommandValue != null)) {
            $checkDeleteName = $this->checkExistBook($this->deleteCommandValue);
            $checkAcceptName = $this->checkExistBook($this->acceptCommandValue);

            if (!$checkDeleteName and !$checkAcceptName) {
                throw new Exception('В каталоге ' . $bookListName . ' нет книги ' . $this->deleteCommandValue);
            }
        }

    }

    private function checkExistBook($bookId)
    {
        $book = $this->databaseManager->getOneThingByCriteria($bookId, 'id', Book::class);
        return ($book != null);
    }

    private function getRequestType()
    {
        if ($this->acceptCommandValue) {
            return 'accept';
        } else if($this->deleteCommandValue) {
            return 'delete';
        }
        return null;
    }

    private function getCommandArgument($typeRequest)
    {
        switch ($typeRequest)
        {
            case 'delete':
                return $this->deleteCommandValue;
                break;
            case 'accept':
                return $this->acceptCommandValue;
                break;
        }
        return null;
    }




    /**
     * @param $bookListName
     * @param $userId
     * @return mixed|null
     */
    private function getTableData($bookListName, $userId)
    {
        switch ($bookListName)
        {
            case 'taken_books':
                return $this->getTakenBookTableData($userId);
            case 'given_books':
                return $this->getGivenBookTableData($userId);
            case 'applications':
                return $this->getApplicationTableData($userId);
        }
        return null;
    }


    /**
     * @param $bookId
     * @param $applicantId
     * @return bool
     */
    private function acceptBookFromList($bookId, $applicantId, $ownerId)
    {
        $applicationForBook = $this->getApplicationForBook($bookId, $applicantId, $ownerId);
        if ($applicationForBook == null) {
            return false;
        }

        $this->databaseManager->remove($applicationForBook);

        return $this->giveBook($bookId, $applicantId, $ownerId);
    }

    /**
     * @param $bookId
     * @param $applicantId
     * @param $ownerId
     * @return bool
     */
    public function giveBook($bookId, $applicantId, $ownerId)
    {
        $takenBook = new TakenBook();
        $takenBook->setBookId($bookId);
        $takenBook->setApplicantId($applicantId);
        $takenBook->setOwnerId($ownerId);
        // TODO : установить deadline
        $takenBook->setDeadline(new \DateTime());

        $this->doctrineManager->add($takenBook);

        return true;// TODO : пока не предесмотрена неудачная передача книги
    }

    private function deleteBookFromList($bookId, $applicantId, $ownerId)
    {
        $applicationForBook = $this->getApplicationForBook($bookId, $applicantId, $ownerId);
        if ($applicationForBook == null) {
            return false;
        }

        $this->databaseManager->remove($applicationForBook);

        return true;
    }

    private function getApplicationForBook($bookId, $applicantId, $ownerId)
    {
        $queryResult = $this->databaseManager->findThingByCriteria(
            'AppBundle\Entity\ApplicationForBook',
            array(
                'applicantId' => $applicantId,
                'ownerId' => $ownerId,
                'bookId' => $bookId
            )
        );
		// TODO : не придумал как извлечь 1 элемент([] не получается)
        $result = null;
        foreach ($queryResult as $item) {
            $result = $item;
        }

        return $result;
    }



    private function executeRequest($requestValue)
    {
        $book = $this->databaseManager->getOneThingByCriteria($requestValue['bookId'], 'id', Book::class);
        $bookId = $book->getId();

        if ($requestValue['typeRequest'] == 'delete') {
            return $this->deleteBookFromList(
                $bookId,
                $requestValue['otherUserObject']->getId(),
                $this->getCurrentUser()->getId()
            );
        } else if ($requestValue['typeRequest'] == 'accept') {
            return $this->acceptBookFromList(
                $bookId,
                $requestValue['otherUserObject']->getId(),
                $this->getCurrentUser()->getId()
            );
        }
        return false;
    }




}
