<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ApplicationForBook;
use AppBundle\Entity\Book;
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
            array_push($users, $givenBook->getOwnerId());
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
                'applicantId' => $getId
            )
        );

        $bookIds = array();
        $users = array();
        foreach ($applicationForBooks as $applicationForBook) {
            array_push($bookIds, $applicationForBook->getBookId());
            array_push($users, $applicationForBook->getOwnerId());
        }

        return $this->generateTableData($bookIds, $users);
    }

    private function createPage($ownerName, $bookListName)
    {
        $userData = $this->getOneThingByCriteria($ownerName, "username", User::class);
        if ($userData == null) {
            return $this->createErrorPage(
                'Пользователь с никнэймом \''
                . $ownerName
                . '\' не найден'
            );
        }

        $bookData = null;
        switch ($bookListName)
        {
            case 'takenBooks':
                $bookData = $this->getTakenBookTableData($userData->getId());
                break;
            case 'givenBooks':
                $bookData = $this->getGivenBookTableData($userData->getId());
                break;
            case 'application':
                $bookData = $this->getApplicationTableData($userData->getId());
                break;
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


    /**
     * @Route("/circulationBooks", name="circulationBooks" )
     */
    public function showBookList()
    {
        $bookList = array(
            'takenBooks',
            'givenBooks',
            'application',
        );

        $ownerName = $this->getParamFromGetRequest('ownerName');
        if ($ownerName == null) {
            $this->createErrorPage($this->getMessageAboutLackArgument('ownerName'));
            // TODO : не хватает проверки существования пользователя
        }

        $bookListName = $this->getParamFromGetRequest('bookListName');
        if ($bookListName == null) {
            $this->createErrorPage($this->getMessageAboutLackArgument('bookListName'));
        }
        if (!in_array($bookListName, $bookList)) {
            $this->createErrorPage(
                'bookListName должен иметь одно из следующих значений '
                . implode(",", $bookList)
            );
        }

        return $this->createPage($ownerName, $bookListName);
    }


}
