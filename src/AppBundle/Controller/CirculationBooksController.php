<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ApplicationForBook;
use AppBundle\Entity\Book;
use AppBundle\Entity\TakenBook;
use AppBundle\Entity\User;
use AppBundle\Entity\UserListBook;
use AppBundle\Controller\MyController;
use AppBundle\SearchBook\SearchData;

use AppBundle\Security\ApplicationStatus;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;



class CirculationBooksController extends MyController
{
    private $deleteCommandValue = null;
    private $acceptCommandValue = null;

    private function getStringDeadline($bookData)
    {
        // TODO : неправильный перевод даты в строковый формат
        $deadlines = array();
        foreach ($bookData as $data) {
            array_push($deadlines, $data->getDeadline()->format('Y-m-d H:i:s'));
        }

        return $deadlines;
    }

    private function getUsernames($userIds)
    {
        $users = $this->getThings($userIds, User::class);
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
        $bookData = $this->getThings($bookIds, Book::class);
        $userNames = $this->getUsernames($userIds);

        return array(
            'books' => $bookData,
            'deadlines' => $this->getStringDeadline($bookData),
            'users' => $userNames
        );
    }

    /**
     * @param $getId
     * @return mixed
     */
    private function getTakenBookTableData($getId)
    {
        $takenBooks = $this->findThingByCriteria(
            ' AppBundle\Entity\TakenBook',
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
        $givenBooks =  $this->findThingByCriteria(
            ' AppBundle\Entity\TakenBook',
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
        $applicationForBooks = $this->findThingByCriteria(
            ' AppBundle\Entity\ApplicationForBook',
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
     * @param $bookListName
     * @param $bookList
     * @return null|string
     */
    private function checkBookListName($bookListName, $bookList)
    {
        if ($bookListName == null) {
            return $this->getMessageAboutLackArgument('bookListName');
        }
        if (!in_array($bookListName, $bookList)) {
            return 'bookListName должен иметь одно из следующих значений '
                . implode(",", $bookList);
        }
        return null;
    }

    /**
     * @param $otherUser
     * @param $typeRequest
     * @return string
     */
    private function checkOtherUser($otherUser, $typeRequest)
    {
        if (($otherUser == null) and ($typeRequest != null)) {
            return $this->getMessageAboutLackArgument('otherUser');
        }
    }

    /**
     * @param $bookListName
     * @return null|string
     */
    private function checkCommands($bookListName)
    {
        if (($this->acceptCommandValue != null) and ($this->deleteCommandValue != null)) {
            return 'Нельзя одновременно использовать запросы delete и accept';
        }
        if (($this->acceptCommandValue != null) and ($bookListName != 'applications')) {
            return 'Использовать запрос accept можно только в каталоге applications';
        }
        if (($this->deleteCommandValue != null) and ($this->deleteCommandValue != null)) {
            $checkDeleteName = $this->checkExistBook($this->deleteCommandValue);
            $checkAcceptName = $this->checkExistBook($this->acceptCommandValue);

            if (!$checkDeleteName and !$checkAcceptName) {
                return 'В каталоге ' . $bookListName . ' нет книги ' . $this->deleteCommandValue;
            }
        }

        return null;
    }

    private function checkExistBook($bookName)
    {
        $book = $this->getOneThingByCriteria($bookName, 'name', Book::class);
        return ($book != null) ;
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
     * @Route("/circulationBooks", name="circulationBooks" )
     */
    public function showBookList()
    {
        $bookList = array(
            'takenBooks',
            'givenBooks',
            'applications',
        );


        $bookListName = $this->getParamFromGetRequest('bookListName');
        $errorMessage = $this->checkBookListName($bookListName, $bookList);
        if ($errorMessage != null) {
            return $this->createErrorPage($errorMessage);
        }

        $this->deleteCommandValue = $this->getParamFromGetRequest('delete');
        $this->acceptCommandValue = $this->getParamFromGetRequest('accept');
        $errorMessage = $this->checkCommands($bookListName);
        if ($errorMessage != null) {
            return $this->createErrorPage($errorMessage);
        }
        $typeRequest = $this->getRequestType();
        $bookName = $this->getCommandArgument($typeRequest);

        $otherUserName = $this->getParamFromGetRequest('otherUser');
        $errorMessage = $this->checkOtherUser($otherUserName, $typeRequest);
        if ($errorMessage != null) {
            return $this->createErrorPage($errorMessage);
        }

        $requestValue = array(
            'typeRequest' => $typeRequest,
            'bookName' => $bookName,
            'currentUser' => $this->getCurrentUser(),
            'otherUser' => $this->getOneThingByCriteria($otherUserName, 'username', User::class)
        );

        return $this->createPage($bookListName, $requestValue);
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
            case 'takenBooks':
                return $this->getTakenBookTableData($userId);
            case 'givenBooks':
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

        $em = $this->getDoctrine()->getManager();
        $em->remove($applicationForBook);

        $em->flush();

        return $this->giveBook($bookId, $applicantId, $ownerId);
    }

    private function getApplicationForBook($bookId, $applicantId, $ownerId)
    {
        $queryResult = $this->findThingByCriteria(
            ' AppBundle\Entity\ApplicationForBook',
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

    private function deleteBookFromList($bookId, $bookListName)
    {
        throw new Exception('checkDeleteName Not implemented');
    }

    private function executeRequest($requestValue, $bookListName, $currentUserData)
    {
        $book = $this->getOneThingByCriteria($requestValue['bookName'], 'name', Book::class);
        $bookId = $book->getId();

        if ($requestValue['typeRequest'] == 'delete') {
            return $this->deleteBookFromList($bookId, $bookListName);
        } else if ($requestValue['typeRequest'] == 'accept') {
            return $this->acceptBookFromList($bookId, $requestValue['otherUser']->getId(), $currentUserData->getId());
        }
        return false;
    }

    private function createPage($bookListName, $requestValue)
    {
        $currentUserData = $this->getCurrentUser();

        if ($requestValue['typeRequest'] != null) {
            if ($this->executeRequest($requestValue, $bookListName, $currentUserData)) {
                $this->redirectToRoute('circulationBooks', array('bookListName' => $bookListName));
            } else {
                return $this->createErrorPage('Запрос ' . $requestValue['typeRequest'] . ' неудался');
            }
        }

        $bookData = $this->getTableData($bookListName, $currentUserData->getId());

        // TODO : исправь перевод в строковый формат {# { bookData[i].deadline }}#}
        return $this->render(
            MyController::TEMPLATE_PATH,
            array(
                'serverUrl' => MyController::SERVER_URL,
                'currentUserName' => $this->getCurrentUserName($this->userAuthorized()),
                'pageName' => 'circulationBooks',
                'userLogin' => $this->userAuthorized(),
                'bookData' => $bookData,
                'bookListName' => $bookListName
            )
        );
    }


}
