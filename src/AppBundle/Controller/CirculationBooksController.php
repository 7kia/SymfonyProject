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
    private function extractUserData($personalBooks, $readUsers)
    {
        $ownerData = array();
        foreach ($personalBooks as &$personalBook)
        {
            $owner = $this->getOneThingByCriteria(
                $personalBook->getUserId(),
                'id',
                User::class
            );


            if (!in_array($owner, $readUsers)) {

                array_push(
                    $ownerData,
                    array(
                        'name' => $owner->getUsername(),
                        'avatar' => $owner->getAvatar()
                    )
                );
            }

        }

        return $ownerData;
    }

    private function extractReadUserData($takenBooks)
    {
        $ownerData = array();
        foreach ($takenBooks as &$takenBook)
        {
            $owner = $this->getOneThingByCriteria(
                $takenBook->getApplicantId(),
                'id',
                User::class
            );
            array_push(
                $ownerData,
                array(
                    'name' => $owner->getUsername(),
                    'avatar' => $owner->getAvatar()
                )
            );
        }

        return $ownerData;
    }

    private function getOwnerData($bookId, $readUsers)
    {
        $personalBooks = $this->findThingByCriteria(
            ' AppBundle\Entity\UserListBook',
            array(
                'bookId' => strval($bookId),
                 'listName' => 'personalBooks'
            )
        );

        return $this->extractUserData($personalBooks, $readUsers);
    }


    private function getReadUserData($bookId)
    {
        $takenBooks = $this->findThingByCriteria(
            ' AppBundle\Entity\TakenBook',
            array(
                'bookId' => strval($bookId)
            )
        );

        return $this->extractReadUserData($takenBooks);
    }

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

    /**
     * @param $getId
     * @return mixed
     */
    private function getTakenBooks($getId)
    {
        $takenBooks = $this->findThingByCriteria(
            ' AppBundle\Entity\TakenBook',
            array(
                'applicantId' => $getId
            )
        );

        $bookIds = array();
        foreach ($takenBooks as $takenBook) {
            array_push($bookIds, $takenBook->getBookId());
        }
        return $this->getBooks($bookIds);
    }

    /**
     * @param $getId
     * @return mixed
     */
    private function getGivenBooks($getId)
    {
        $givenBooks =  $this->findThingByCriteria(
            ' AppBundle\Entity\TakenBook',
            array(
                'ownerId' => $getId
            )
        );

        $bookIds = array();
        foreach ($givenBooks as $givenBook) {
            array_push($bookIds, $givenBook->getBookId());
        }
        return $this->getBooks($bookIds);
    }

    /**
     * @param $getId
     * @return mixed
     */
    private function getApplicationForBooks($getId)
    {
        $applicationForBooks = $this->findThingByCriteria(
            ' AppBundle\Entity\ApplicationForBook',
            array(
                'applicantId' => $getId
            )
        );

        $bookIds = array();
        foreach ($applicationForBooks as $applicationForBook) {
            array_push($bookIds, $applicationForBook->getBookId());
        }

        return $this->getBooks($bookIds);
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
                $bookData = $this->getTakenBooks($userData->getId());
                break;
            case 'givenBooks':
                $bookData = $this->getGivenBooks($userData->getId());
                break;
            case 'application':
                $bookData = $this->getApplicationForBooks($userData->getId());
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
        print_r($bookListName);
        print_r($bookList);

        $b = in_array($bookListName, $bookList);
        print_r($b);
        if (!in_array($bookListName, $bookList)) {
            $this->createErrorPage(
                'bookListName должен иметь одно из следующих значений '
                . implode(",", $bookList)
            );
        }

        return $this->createPage($ownerName, $bookListName);
    }



}
