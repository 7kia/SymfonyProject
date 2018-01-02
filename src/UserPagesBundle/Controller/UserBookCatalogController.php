<?php

namespace UserPagesBundle\Controller;

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
     * @Route("/user_book_catalog", name="user_book_catalog" )
     */
    public function showPage(Request $request)
    {
        return $this->generatePage($request);
    }

    protected function getGenerationDataFromUrl()
    {
        $bookListName = $this->getParamFromGetRequest('book_list_name');
        $ownerId = $this->getParamFromGetRequest('owner_id');

        // Такое добавление данных нужно чтобы можно было перейти после авторизации
        // на эту страницу(с аргументами в Url не работает)
        if ($ownerId == null) {
            $ownerId = $this->getCurrentUser()->getId();
        }
        if ($bookListName == null) {
            $bookListName = 'personal_books';
        }

        return array(
            'book_list_name' => $bookListName,
            'owner_id' => $ownerId
        );

    }

    protected function getCommandDataFromUrl()
    {
        $dataFromUrl = $this->getGenerationDataFromUrl();

        $bookId = $this->getParamFromGetRequest('delete');

        return array_merge(
            $dataFromUrl,
            array(
                'delete' => $bookId
            )
        );
    }

    protected function checkGenerationDataForPage($generationDataForPage)
    {
        $this->checkBookListName($generationDataForPage['book_list_name']);
        $this->checkUserId($generationDataForPage['owner_id']);
    }

    private function checkBookListName(&$bookListName)
    {
        $bookList = array(
            'favorite_books',
            'read_later',
            'personal_books'
        );


        if (!in_array($bookListName, $bookList)) {
            throw new Exception(
                'Каталога с именем \''
                . $bookListName
                . '\' не существует'
            );
        }
    }

    private function checkUserId(&$ownerId)
    {
        if ($ownerId == null) {
            throw new Exception(
                $this->getMessageAboutLackArgument('owner_id')
            );
        }
        $owner = $this->databaseManager->getOneThingByCriterion($ownerId, 'id', User::class);
        if ($owner == null) {
            throw new Exception(
                'Пользователя с id \''
                . $ownerId
                . '\' не существует'
            );
        }
    }

    protected function checkCommandData($commandData)
    {
        $this->checkCurrentUserId($commandData['delete'], $this->getCurrentUser()->getId(), $commandData['owner_id']);
        $this->checkDeleteBookToCatalog(
            $commandData['delete'],
            $commandData['owner_id'],
            $commandData['book_list_name']
        );
    }


    private function checkCurrentUserId($deleteBook, $currentUserId, $ownerId)
    {
        if ($deleteBook) {
            $this->checkUserId($currentUserId);
            $this->checkUserId($ownerId);

            if ($currentUserId != $ownerId) {
                throw new Exception('Команда удаления книги из каталога доступна только владельцу книги');
            }
        }
    }

    private function checkDeleteBookToCatalog($deleteBook, $ownerId, $bookListName)
    {
        if ($deleteBook) {
            $book = $this->databaseManager->getOneThingByCriteria(
                array(
                    'bookId' => $deleteBook,
                    'userId' => $ownerId,
                    'listName' => $bookListName
                ),
                UserListBook::class
            );
            if ($book == null) {
                throw new Exception(
                    'Книги с id \''
                    . $deleteBook
                    . '\' не существует в каталоге '
                    . $bookListName
                    . ' у пользователя с id= '
                    . $ownerId
                );
            }
        }


    }

    protected function commandProcessing($commandData)
    {
        if ($commandData['delete']) {
            if ($this->executeDeleteRequest($commandData)) {
                $this->redirectData = array(
                    'route' =>'user_book_catalog',
                    'arguments' => array(
                        'book_list_name' => $commandData['book_list_name'],
                        'owner_id' => $commandData['owner_id']
                    )
                );

            } else {
                throw new Exception('Запрос ' . $commandData['typeRequest'] . ' неудался');
            }
        }
    }


    private function executeDeleteRequest($commandData)
    {
        $bookToCatalog = $this->databaseManager->getOneThingByCriteria(
            array(
                'bookId' => $commandData['delete'],
                'listName' => $commandData['book_list_name'],
                'userId' => $commandData['owner_id']
            ),
            UserListBook::class
        );

        $this->databaseManager->remove($bookToCatalog);
        return true;
    }

    protected function generatePageData($request, $generationDataForPage)
    {
        $user = $this->databaseManager->getOneThingByCriterion(
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
                'bookListName' => $generationDataForPage['book_list_name'],
                'ownerName' => $user->getUsername(),
                'ownerId' => $user->getId(),
                'bookCards' => $bookCards
            )
        );
    }




    private function getUserCatalog($ownerId, $bookListName)
    {
        $ownerUser =  $this->databaseManager->getOneThingByCriterion($ownerId, 'id', User::class);
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
