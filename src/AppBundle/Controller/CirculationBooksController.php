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
    private function getBooks($idList)
    {
        $books = array();
        foreach ($idList as $id) {
            $book = $this->getOneThingByCriteria(
                strval($id),
                'id',
                Book::class
            );
            if ($book != null) {
                array_push($books, $book);
            }
        }
        return $books;
    }

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
        $userNames = array();
        foreach ($userIds as $id) {
            $user = $this->getOneThingByCriteria(
                strval($id),
                'id',
                User::class
            );
            if ($user != null) {
                array_push($userNames, $user->getUsername());
            }
        }

        return $userNames;
    }

    private function generateTableData($bookIds, $userIds)
    {
        $bookData = $this->getBooks($bookIds);
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

    private function checkOtherUser($otherUser, $typeRequest)
    {
        if (($otherUser == null) and ($typeRequest != null)) {
            return $this->getMessageAboutLackArgument('otherUser');
        }
    }

    private function checkCommands($bookListName)
    {
        $errorMessage = null;
        if (($this->acceptCommand != null) and ($this->deleteCommand != null)) {
            $errorMessage = 'Нельзя одновременно использовать запросы delete и accept';
        }
        if (($this->acceptCommand != null) and ($bookListName != 'applications')) {
            $errorMessage = 'Нельзя использовать запрос accept в каталоге applications';
        }
        $checkDeleteName = $this->checkDeleteName($this->deleteCommand, $bookListName);
        $checkAcceptName = $this->checkAcceptName($this->acceptCommand, $bookListName);

        if (!$checkDeleteName and !$checkAcceptName) {
            $errorMessage = 'В каталоге ' . $bookListName . ' нет книги ' . $this->deleteCommand;
        }

        return $errorMessage;
    }

    private function getRequestType()
    {
        if ($this->acceptCommand) {
            return 'accept';
        } else if($this->deleteCommand) {
            return 'delete';
        }
        return null;
    }

    private function getCommandArgument($typeRequest)
    {
        switch ($typeRequest)
        {
            case 'delete':
                return $this->deleteCommand;
                break;
            case 'accept':
                return $this->acceptCommand;
                break;
        }
        return null;
    }

    // TODO : перенести переменные
    private $deleteCommand = null;
    private $acceptCommand = null;
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

        $ownerName = $this->getParamFromGetRequest('ownerName');
        $errorMessage = $this->checkOwnerName($ownerName);
        if ($errorMessage != null) {
            return $this->createErrorPage($errorMessage);
        }

        $bookListName = $this->getParamFromGetRequest('bookListName');
        $errorMessage = $this->checkBookListName($bookListName, $bookList);
        if ($errorMessage != null) {
            return $this->createErrorPage($errorMessage);
        }

        $this->deleteCommand = $this->getParamFromGetRequest('delete');
        $this->acceptCommand = $this->getParamFromGetRequest('accept');
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



        return $this->createPage($ownerName, $bookListName, $requestValue);
    }

    private function checkDeleteName($deleteName, $bookListName)
    {
        if ($deleteName == null) {
            return true;
        }
        throw new Exception('checkDeleteName Not implemented');
        return false;
    }

    private function checkAcceptName($acceptName, $bookListName)
    {
        if ($acceptName == null) {
            return true;
        }

        return false;
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
    // TODO : после того как всё заработает $ownerid заменить на текущего пользователя
    private function acceptBookFromList($bookId, $applicantId, $ownerId)
    {
        $applicationForBook = $this->getApplicationForBook($bookId, $applicantId, $ownerId);
        print_r($bookId);
        print_r($applicantId);
        print_r($ownerId);

        if ($applicationForBook == null) {
            return false;
        }
        print_r($applicationForBook);
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


    private function giveBook($bookId, $applicantId, $ownerId)
    {
        $em = $this->getDoctrine()->getManager();

        $takenBook = new TakenBook();
        $takenBook->setBookId($bookId);
        $takenBook->setApplicantId($applicantId);
        $takenBook->setOwnerId($ownerId);
        // TODO : установить deadline
        $takenBook->setDeadline(new \DateTime());


        $em->persist($takenBook);
        $em->flush();

        return true;// TODO : пока не предесмотрена неудачная передача книги
    }

    private function createPage($ownerName, $bookListName, $requestValue)
    {
        $userData = $this->getCurrentUser();//$this->getOneThingByCriteria($ownerName, 'username', User::class);//
        $bookData = $this->getTableData($bookListName, $userData->getId());

        if ($requestValue['typeRequest'] != null) {
            $successful = false;

            $book = $this->getOneThingByCriteria($requestValue['bookName'], 'name', Book::class);
            $bookId = $book->getId();

            if ($requestValue['typeRequest'] == 'delete') {
                $successful = $this->deleteBookFromList($bookId, $bookListName);
            } else if ($requestValue['typeRequest'] == 'accept') {

                $successful = $this->acceptBookFromList($bookId, $requestValue['otherUser']->getId(), $userData->getId());
            }
            if ($successful) {
//                $this->redirectToRoute(
//                    'circulationBooks',
//                    array(
//                        'ownerName' => $userData->getId(),
//                        'bookListName' => $bookListName
//                    )
//                );
            } else {
                $this->createErrorPage('Запрос ' . $requestValue['typeRequest'] . ' неудался');
            }

        }



        // TODO : исправь перевод в строковый формат {# { bookData[i].deadline }}#}
        return $this->render(
            $this->getTemplatePath(),
            array(
                'serverUrl' => $this->getServerUrl(),
                'currentUserName' => $this->getCurrentUserName($this->userAuthorized()),
                'pageName' => 'circulationBooks',
                'userLogin' => $this->userAuthorized(),
                'bookData' => $bookData,
                'bookListName' => $bookListName
            )
        );
    }



}
