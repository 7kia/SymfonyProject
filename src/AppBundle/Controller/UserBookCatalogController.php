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
    /**
     * @Route("/user_book_catalog", name="user_book_catalogs" )
     */
    public function showPage(Request $request)
    {
        return $this->generatePage($request);
    }

    protected function getGenerationDataFromUrl()
    {
        $bookListName = $this->getParamFromGetRequest('book_list_name');
        $ownerId = $this->getParamFromGetRequest('owner_id');
        return array(
            'book_list_name' => $bookListName,
            'owner_id' => $ownerId
        );
    }

    protected function checkGenerationDataForPage($generationDataForPage)
    {
        $this->checkBookListName($generationDataForPage['book_list_name']);
        $this->checkOwnerId($generationDataForPage['owner_id']);
    }

    private function checkBookListName(&$bookListName)
    {
        $bookList = array(
            'favorite_books',
            'read_later',
            'personal_books'
        );

        if ($bookListName == null) {
            $bookListName = 'personal_books';
        }
        if (!in_array($bookListName, $bookList)) {
            throw new Exception(
                'Каталога с именем \''
                . $bookListName
                . '\' не существует'
            );
        }
    }

    private function checkOwnerId(&$ownerId)
    {
        // TODO : на эту страницу можно будет зайти только авторизированному пользователю
        // пока для более быстрой отладки не будет ограничении по доступу
        if ($ownerId == null) {
            $ownerId = $this->getCurrentUser()->getId();
        }

        $owner = $this->databaseManager->getOneThingByCriteria($ownerId, 'id', User::class);
        if ($owner == null) {
            throw new Exception(
                'Пользователя с id \''
                . $ownerId
                . '\' не существует'
            );
        }
    }

    protected function generatePageData($request, $generationDataForPage)
    {
        $user = $this->databaseManager->getOneThingByCriteria(
            $generationDataForPage['owner_id'],
            'id',
            User::class
        );

        $bookCards = $this->getUserCatalog(
            $generationDataForPage['owner_id'],
            $generationDataForPage['book_list_name']
        );
        $catalogTitle = $this->getCatalogTitle(
            $generationDataForPage['book_list_name'],
            $user->getUsername()
        );

        return array_merge(
            MyController::generatePageData($request, $generationDataForPage),
            array(
                'pageName' => 'book_list',
                'bookListTitle' => $catalogTitle,
                'ownerName' => $user->getUsername(),
                'ownerId' => $user->getId(),
                'bookCards' => $bookCards
            )
        );
    }




    private function getUserCatalog($ownerId, $bookListName)
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
}
