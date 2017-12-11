<?php

// src/AppBundle/Controller/SecurityController.php
namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\UserListBook;
use AppBundle\Controller\MyController;


use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserBookCatalogController extends MyController
{
    private function findUserCatalog($userId, $bookListName)
    {
        return $this->findThingByCriteria(
            ' AppBundle\Entity\UserListBook',
            array(
                 'userId' => $userId,
                 'listName' => $bookListName
            )
        );
    }

    private function getOwnerUser($ownerName)
    {
        return $this->getOneThingByCriteria(
            $ownerName,
            'username',
            User::class
        );
    }

    function getUserCatalog($ownerName, $bookListName)
    {
        $user =  $this->getOwnerUser($ownerName);
        $catalog = $this->findUserCatalog($user->getId(), $bookListName);
        $catalogBooks = $this->extractBooks($catalog);

        return $catalogBooks;
    }

    function getCurrentUserName($userLogin)
    {
        if ($userLogin != false) {
            return $this->getUser()->getUsername();
        } else {
            return '7kia';
        }
    }

    function createPage($bookListName, $ownerName)
    {
        if ($this->getOwnerUser($ownerName) == null) {
            $this->createErrorPage(
                'Пользователя с именем \''
                . $ownerName
                . '\' не существует'
            );
        }


        $bookCards = $this->getUserCatalog($ownerName, $bookListName);

        $catalogTitle = $bookListName . ' пользователя ' . $ownerName;

        return $this->render(
            $this->getTemplatePath(),
            array(
                'serverUrl' => $this->getServerUrl(),
                'currentUserName' => $this->getCurrentUserName($this->userAuthorized()),
                'pageName' => 'bookList',
                'bookListTitle' => $catalogTitle,
                'ownerName' => $ownerName,
                'userLogin' => $this->userAuthorized(),
                'bookCards' => $bookCards
            )
        );
    }

    /**
     * @Route("/userBookCatalog", name="userBookCatalogs" )
     */
    public function showPage()
    {
        $bookList = array(
            'favoriteBooks',
            'readLater',
            'personalBooks'
        );

        $bookListName = $this->getParamFromGetRequest('bookListName');
        if ($bookListName == null) {
            $bookListName = 'personalBooks';
        }

        $ownerName = $this->getParamFromGetRequest('ownerName');
        // TODO : на эту страницу можно будет зайти только авторизированному пользователю
        // пока для более быстрой отладки не будет ограничении по доступу
        if ($ownerName == null) {
            $ownerName = $this->getCurrentUserName($this->userAuthorized());
        }

        if (in_array($bookListName, $bookList)) {
            return $this->createPage($bookListName, $ownerName);
        } else {
            header('HTTP/1.0 404');
            return $this->createErrorPage(
                'Каталога с именем \''
                . $bookListName
                . '\' не существует'
            );
        }
    }




}
