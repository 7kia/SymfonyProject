<?php

// src/AppBundle/Controller/SecurityController.php
namespace AppBundle\Controller;



use AppBundle\Entity\Book;
use AppBundle\Entity\User;
use AppBundle\Entity\UserListBook;
use AppBundle\Controller\MyController;
use AppBundle\SearchBook\SearchData;

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
        print_r($personalBooks);
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

    /**
     * @param $bookName -
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function createPage($bookName)
    {
        $bookData = $this->getOneThingByCriteria($bookName, "name", Book::class);


        $readUsers = $this->getReadUserData($bookData->getId());

        $bookOwners = $this->getOwnerData($bookData->getId(), $readUsers);


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
            )
        );
    }


    /**
     * @Route("/bookPage", name="bookPage" )
     */
    public function showBookList()
    {
        $bookName = $this->getParamFromGetRequest('bookName');

        return $this->createPage($bookName);
    }



}
