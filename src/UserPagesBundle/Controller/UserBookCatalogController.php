<?php

namespace UserPagesBundle\Controller;

use AppBundle\DomainModel\Actions\ActionsForUserBookCatalog;
use AppBundle\DomainModel\PageDataGenerators\BookDataGenerator;
use AppBundle\DomainModel\PageDataGenerators\UserBookCatalogDataGenerator;
use AppBundle\DomainModel\PageDataGenerators\UserDataGenerator;
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
    private $actionsForUserBookCatalog;
    private $bookDataGenerator;
    private $userBookCatalogDataGenerator;

    private function initComponents()
    {
        $this->actionsForUserBookCatalog = new ActionsForUserBookCatalog($this->getDoctrine());

        $this->bookDataGenerator = new BookDataGenerator($this);
        $this->userDataGenerator = new UserDataGenerator($this);
        $this->userBookCatalogDataGenerator = new UserBookCatalogDataGenerator($this);
    }

    /**
     * @Route("/user_book_catalog", name="user_book_catalog" )
     */
    public function showPage(Request $request)
    {
        $this->initComponents();
        return $this->generatePage($request);
    }

    protected function getGenerationDataFromUrl()
    {
        $bookListName = $this->getParamFromGetRequest('book_list_name');
        $ownerId = $this->getParamFromGetRequest('owner_id');

        // Такое добавление данных нужно чтобы можно было перейти после авторизации
        // на эту страницу(с аргументами в Url не работает)
        if ($ownerId == null) {
            $ownerId = $this->userDataGenerator->getCurrentUser()->getId();
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
                'delete' => $bookId,
                'currentUserId' => $this->userDataGenerator->getCurrentUser()->getId()
            )
        );
    }

    protected function checkGenerationDataForPage($generationDataForPage)
    {
        $this->checkMandatoryArgument('book_list_name', $generationDataForPage['book_list_name']);
        $this->checkMandatoryArgument('owner_id', $generationDataForPage['owner_id']);
    }


    protected function commandProcessing($commandData)
    {
        if ($commandData['delete']) {
            if ($this->actionsForUserBookCatalog->deleteBookFormCatalog(
                    $commandData['delete'],
                    $commandData['book_list_name'],
                    $commandData['owner_id'],
                    $commandData['currentUserId']
                )
            ) {
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



    protected function generatePageData($request, $generationDataForPage)
    {
        $user = $this->userDataGenerator->getUser($generationDataForPage['owner_id']);


        $bookCards = $this->userBookCatalogDataGenerator->getUserCatalog(
            $generationDataForPage['owner_id'],
            $generationDataForPage['book_list_name']
        );
        $catalogTitle = $this->userBookCatalogDataGenerator->getCatalogTitle(
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

}
