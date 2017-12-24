<?php

// src/AppBundle/Controller/SecurityController.php
namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\UserListBook;
use AppBundle\Controller\MyController;
use AppBundle\DatabaseManagement\DatabaseManager;

use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserBookCatalogController extends MyController
{
    function getUserCatalog($ownerId, $bookListName)
    {
        $ownerUser =  $this->databaseManager->getOneThingByCriteria($ownerId, 'id', User::class);
        $catalog = $this->databaseManager->findUserCatalog($ownerUser->getId(), $bookListName);
        $catalogBooks = $this->databaseManager->extractBooks($catalog);

        return $catalogBooks;
    }

    private function getCatalogName($bookListName)
    {
        switch ($bookListName)
        {
            case 'favorite_books':
                return 'Любимые книги';
            case 'read_later':
                return 'Прочитать позже';
            case 'personal_books':
                return 'Личные книги';
        }
        return '';
    }


    private function getCatalogTitle($bookListName, $userName)
    {
        return $this->getCatalogName($bookListName) . ' пользователя ' . $userName;
    }

    private function createPage($bookListName, $ownerId)
    {
        $user = $this->databaseManager->getOneThingByCriteria($ownerId, 'id', User::class);
        if ($user == null) {
            return $this->createErrorPage(
                'Пользователя с id \''
                . $ownerId
                . '\' не существует'
            );
        }

        $bookCards = $this->getUserCatalog($ownerId, $bookListName);
        $catalogTitle = $this->getCatalogTitle($bookListName, $user->getUsername());

        return $this->render(
            MyController::TEMPLATE_PATH,
            array(
                'serverUrl' => MyController::SERVER_URL,
                'currentUser' => $this->getCurrentUser(),
                'pageName' => 'book_list',
                'bookListTitle' => $catalogTitle,
                'ownerName' => $user->getUsername(),
                'ownerId' => $user->getId(),
                'userLogin' => $this->userAuthorized(),
                'bookCards' => $bookCards
            )
        );
    }

    /**
     * @Route("/user_book_catalog", name="user_book_catalogs" )
     */
    public function showPage()
    {

        $this->databaseManager = new DatabaseManager($this->getDoctrine());

        $bookList = array(
            'favorite_books',
            'read_later',
            'personal_books'
        );

        $bookListName = $this->getParamFromGetRequest('book_list_name');
        if ($bookListName == null) {
            $bookListName = 'personal_books';
        }

        $ownerId = $this->getParamFromGetRequest('owner_id');
        // TODO : на эту страницу можно будет зайти только авторизированному пользователю
        // пока для более быстрой отладки не будет ограничении по доступу
        if ($ownerId == null) {
            $ownerId = $this->getCurrentUser()->getId();
        }

        if (in_array($bookListName, $bookList)) {
            return $this->createPage($bookListName, $ownerId);
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
