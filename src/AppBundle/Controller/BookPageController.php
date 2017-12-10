<?php

// src/AppBundle/Controller/SecurityController.php
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
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class BookPageController extends MyController
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

    private function getApplicationInfo($applicationStatus)
    {
        switch ($applicationStatus) {
            case ApplicationStatus::REPEAT_SEND:
                return 'Заявка подана повторно';
                break;
            case ApplicationStatus::SEND_FAILED:
                return 'Не удалось подать заявку';
                break;
            case ApplicationStatus::SEND_SUCCESSFUL:
                return 'Вы подали заявку';
                break;
        }
        return '';
    }

    private function sendApplication(User $foundOwner, Book $bookData, User $currentUser)
    {
        $applicationForBook = new ApplicationForBook();
        $applicationForBook->setBookId($bookData->getId());
        $applicationForBook->setApplicantId($currentUser->getId());
        $applicationForBook->setOwnerId($foundOwner->getId());

        // TODO : проверь добавку повторной заявки
        if (true) {

        }
        $em = $this->getDoctrine()->getManager();


        $em->persist($applicationForBook);
        $em->flush();
    }
    /**
     * @param $bookName
     * @param $ownerName
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function createPage($bookName, $ownerName)
    {
        $bookData = $this->getOneThingByCriteria($bookName, "name", Book::class);
        if ($bookData == null) {
            return $this->createErrorPage(
                'Книга с названием \''
                . $bookName
                . '\' не найдена '
            );
        }

        $readUsers = $this->getReadUserData($bookData->getId());
        $bookOwners = $this->getOwnerData($bookData->getId(), $readUsers);
        if ($bookOwners == null) {
            return $this->createErrorPage(
                'Книга с названием \''
                . $bookName
                . '\' не имеет владельцев'
            );
        }

        $applicationStatusInfo = '';
        $sendApplicationToOwner = ($ownerName != null);
        if ($sendApplicationToOwner) {
            $foundOwner = $this->getOneThingByCriteria($ownerName, "username", User::class);
            if ($foundOwner == null) {
                return $this->createErrorPage('Книга с названием \''
                    . $bookName
                    . '\' не имеет владельца с никнэймом \''
                    . $ownerName
                    . '\''
                );
            }

            $applicationStatus = $this->sendApplication(
                $foundOwner,
                $bookData,
                $this->getCurrentUser()
            );
            $applicationStatusInfo = $this->getApplicationInfo($applicationStatus);
        }


        return $this->render(
            $this->getTemplatePath(),
            array(
                'serverUrl' => $this->getServerUrl(),
                'currentUserName' => $this->getCurrentUserName($this->userAuthorized()),
                'pageName' => 'bookPage',
                'userLogin' => $this->userAuthorized(),
                'bookData' => $bookData,
                'ownerList' => $bookOwners,
                'readUserList' => $readUsers,
                'applicationStatusInfo' => $applicationStatusInfo
            )
        );
    }


    /**
     * @Route("/bookPage", name="bookPage" )
     */
    public function showBookList()
    {
        $bookName = $this->getParamFromGetRequest('bookName');
        $ownerName = $this->getParamFromGetRequest('sendApplicationTo');

        return $this->createPage($bookName, $ownerName);
    }

}
