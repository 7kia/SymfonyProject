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
        return $this->findBooksByCriteria(
            " AppBundle\Entity\UserListBook",
            $userId, "userId",
            $bookListName, "listName"
        );
    }

    private function getOwnerUser($ownerName)
    {
        return $this->getDoctrine()
                ->getRepository(User::class)
                ->findOneBy(
                    ['username' => $ownerName]
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
            return "7kia";
        }
    }

    function createPage($bookListName, $ownerName)
    {
        $user = $this->getUser();
        $userLogin = ($user != null);

        // TODO : на эту страницу можно будет зайти только авторизированному пользователю
        // пока для более быстрой отладки зе будет ограничении по доступу
        if ($ownerName == null) {
            $ownerName = $this->getCurrentUserName($userLogin);
        }

        $bookCards = null;

        //if ($userLogin) {
            $bookCards = $this->getUserCatalog($ownerName, $bookListName);
        //}

        $catalogTitle = $bookListName . " пользователя " . $ownerName;

        return $this->render(
            'userBookCatalog.html.twig',
            array(
                "serverUrl" => "http://localhost:8000/",
                "currentUserName" => $this->getCurrentUserName($userLogin),
                "pageName" => "bookList",
                "bookListTitle" => $catalogTitle,
                "ownerName" => $ownerName,
                "userLogin" => $userLogin,
                "bookCards" => $bookCards
            )
        );
    }

    /**
     * @Route("/userBookCatalog", name="userBookCatalogs" )
     */
    public function showBookList()
    {
        $bookLists = array(
            "favoriteBooks",
            "readLater",
            "personalBooks"
        );

        $bookListName = $this->getParamFromGetRequest("bookListName");
        if ($bookListName == null) {
            $bookListName = "personalBooks";
        }

        $ownerName = $this->getParamFromGetRequest("ownerName");

        if (in_array($bookListName, $bookLists)) {
            return $this->createPage($bookListName, $ownerName);
        }

        header('HTTP/1.0 404');
        return $this->createErrorPage("Ошибка, не передан аргумент  \'bookListName\'");
    }




}
