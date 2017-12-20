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
    function getUserCatalog($ownerName, $bookListName)
    {
        $ownerUser =  $this->getOneThingByCriteria($ownerName, 'username',User::class);
        $catalog = $this->findUserCatalog($ownerUser->getId(), $bookListName);
        $catalogBooks = $this->extractBooks($catalog);

        return $catalogBooks;
    }

    function createPage($bookListName, $ownerName)
    {
        if ($this->getOneThingByCriteria($ownerName, 'username',User::class) == null) {
            return $this->createErrorPage(
                'Пользователя с именем \''
                . $ownerName
                . '\' не существует'
            );
        }

        $bookCards = $this->getUserCatalog($ownerName, $bookListName);
        $catalogTitle = $bookListName . ' пользователя ' . $ownerName;

        return $this->render(
            MyController::TEMPLATE_PATH,
            array(
                'serverUrl' => MyController::SERVER_URL,
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
     * @Route("/userBook_catalog", name="user_book_catalogs" )
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
