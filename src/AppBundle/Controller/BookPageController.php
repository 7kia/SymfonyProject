<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ApplicationForBook;
use AppBundle\Entity\Book;
use AppBundle\Entity\User;
use AppBundle\Entity\UserListBook;
use AppBundle\Controller\MyController;
use AppBundle\DatabaseManagement\SearchData;
use AppBundle\DatabaseManagement\DatabaseManager;

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
    private $applicationStatusInfo = null;
    private $bookId;
    private $bookData;
    /**
     * @Route("/book_page", name="book_page" )
     */
    public function showPage(Request $request)
    {
        return $this->generatePage($request);
    }

    protected function getGenerationDataFromUrl()
    {
        $this->bookId = $this->getParamFromGetRequest('book_id');

        return array(
            'book_id' => $this->bookId,
        );
    }

    protected function getCommandDataFromUrl()
    {
        $bookId = $this->getParamFromGetRequest('book_id');
        $ownerId = $this->getParamFromGetRequest('send_application_to');
        $catalog = $this->getParamFromGetRequest('add_to_catalog');

        return array(
            'book_id' => $bookId,
            'send_application_to' => $ownerId,
            'add_to_catalog' => $catalog
        );
    }

    protected function checkGenerationDataForPage($generationDataForPage)
    {
        $bookData = $this->databaseManager->getOneThingByCriteria($generationDataForPage['book_id'], "id", Book::class);
        if ($bookData == null) {
            throw new Exception(
                'Книга с id \''
                . $generationDataForPage['book_id']
                . '\' не найдена '
            );
        }
    }

    protected function checkCommandData($commandData)
    {
        if ($commandData['send_application_to'] != null) {
            $this->checkOwner($commandData['send_application_to']);
        }

        if ($commandData['add_to_catalog'] != null) {
            $this->checkCatalog($commandData['add_to_catalog']);
        }
    }

    private function checkOwner($ownerId)
    {
        $owner = $this->databaseManager->getOneThingByCriteria(
            $ownerId,
            'id',
            User::class
        );
        if ($owner == null) {
            throw new Exception(
                'Пользователя с id '
                . $ownerId
                . 'не сущуствует'
            );
        }
    }

    private function checkCatalog($catalog)
    {
        $catalogs =  array(
            'read_later',
            'favorite_books'
        );
        if ($catalog != null) {
            if (!in_array($catalog, $catalogs)) {
                throw new Exception('Можно добавить только в ' . implode(',', $catalogs));
            }
        }
    }



    protected function commandProcessing($commandData)
    {
        $this->bookData = $this->databaseManager->getOneThingByCriteria(
            $this->bookId,
            "id",
            Book::class
        );

        $this->notificationMessage = '';
        if ($commandData['add_to_catalog'] != null) {
            if ($this->addBookToUserCatalog($this->bookData, $commandData['add_to_catalog'])) {
                $this->notificationMessage = 'Вы добавили эту книгу в свой каталог';
            } else {
                $this->notificationMessage = 'Эта книга там уже есть';
            }
        }

        $sendApplicationToOwner = ($commandData['send_application_to'] != null);
        if ($sendApplicationToOwner) {
            $this->applicationStatusInfo = $this->sendApplicationToOwner($commandData['send_application_to'], $this->bookData);
            if ($this->applicationStatusInfo == null) {
                throw new Exception(
                    'Книга с названием \'' . $commandData['bookId']
                    . '\' не имеет владельца с id \'' . $commandData['send_application_to'] . '\''
                );
            }
            $this->notificationMessage = 'Вы подали заявку';
        }
    }


    protected function generatePageData($request, $generationDataForPage)
    {
        $readUsers = $this->getReadUserData($this->bookData->getId());
        $ownerData = $this->getOwnerData($this->bookData->getId(), $readUsers);
        $ownerCount = count($ownerData);


        $currentUserInArray = $this->deleteCurrentUser($ownerData);
        if (($ownerData == null) and (!$currentUserInArray)) {
            throw new Exception(
                'Книга с названием \''
                . $generationDataForPage['bookId']
                . '\' не имеет владельцев'
            );
        }

        return array_merge(
            MyController::generatePageData($request, $generationDataForPage),
            array(
                'pageName' => 'book_page',
                'userLogin' => $this->userAuthorized(),
                'bookData' => $this->bookData,
                'ownerList' => $ownerData,
                'readUserList' => $readUsers,
                'applicationStatusInfo' => $this->applicationStatusInfo,
                'ownerCount' => $ownerCount,
                'readCount' => count($readUsers),
                'notificationMessage' => $this->notificationMessage
            )
        );
    }


    /**
     * @param $personalBooks
     * @param $readUsers
     * @return array
     */
    private function extractUserData($personalBooks, $readUsers)
    {
        $ownerData = array();
        foreach ($personalBooks as &$personalBook)
        {
            $owner = $this->databaseManager->getOneThingByCriteria(
                $personalBook->getUserId(),
                'id',
                User::class
            );

            if (!in_array($owner, $readUsers)) {

                array_push(
                    $ownerData,
                    array(
                        'name' => $owner->getUsername(),
                        'avatar' => $owner->getAvatar(),
                        'userId' => $owner->getId()
                    )
                );
            }

        }

        return $ownerData;
    }

    /**
     * @param $takenBooks
     * @return array
     */
    private function extractReadUserData($takenBooks)
    {
        $ownerData = array();
        foreach ($takenBooks as &$takenBook)
        {
            $owner = $this->databaseManager->getOneThingByCriteria(
                $takenBook->getApplicantId(),
                'id',
                User::class
            );
            array_push(
                $ownerData,
                array(
                    'name' => $owner->getUsername(),
                    'avatar' => $owner->getAvatar(),
                    'userId' => $owner->getId()
                )
            );
        }

        return $ownerData;
    }

    /**
     * @param $bookId
     * @param $readUsers
     * @return array
     */
    private function getOwnerData($bookId, $readUsers)
    {
        $personalBooks = $this->databaseManager->findThingByCriteria(
            'AppBundle\Entity\UserListBook',
            array(
                'bookId' => $bookId,
                'listName' => 'personal_books'
            )
        );

        return $this->extractUserData($personalBooks, $readUsers);
    }

    /**
     * @param $bookId
     * @return array
     */
    private function getReadUserData($bookId)
    {
        $takenBooks = $this->databaseManager->findThingByCriteria(
            'AppBundle\Entity\TakenBook',
            array(
                'bookId' => strval($bookId)
            )
        );

        return $this->extractReadUserData($takenBooks);
    }

    /**
     * @param $applicationStatus
     * @return string
     */
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

    /**
     * @param Book $addBook
     * @param $catalog
     * @return bool
     */
    private function addBookToUserCatalog(Book $addBook, $catalog)
    {
        $sameBook = $this->databaseManager->findThingByCriteria(
            'AppBundle\Entity\UserListBook',
            array(
                'bookId' => $addBook->getId(),
                'listName' => $catalog
            )
        );
        if ($sameBook != null) {
            return false;
        }

        $bookToCatalog = new UserListBook();
        $bookToCatalog->setBookId($addBook->getId());
        $bookToCatalog->setListName($catalog);
        $bookToCatalog->setUserId($this->getCurrentUser()->getId());

        $this->databaseManager->add($bookToCatalog);
        return true;
    }

    /**
     * @param $ownerId
     * @return null|string
     */
    private function sendApplicationToOwner($ownerId)
    {
        $foundOwner = $this->databaseManager->getOneThingByCriteria($ownerId, "id", User::class);
        if ($foundOwner == null) {
            return null;
        }

        $applicationStatus = $this->databaseManager->sendApplication(
            $foundOwner,
            $this->bookData,
            $this->getCurrentUser()
        );

        return $this->getApplicationInfo($applicationStatus);
    }

    /**
     * @param $bookOwners
     * @return bool
     */
    private function deleteCurrentUser(&$bookOwners)
    {
        $currentUser = $this->getCurrentUser();
        $key = array_search(
            array(
                'name' => $currentUser->getUsername(),
                'avatar' => $currentUser->getAvatar(),
                'userId' => $currentUser->getId()
            ),
            $bookOwners
        );


        if ($key !== false)
        {
            $firstPart = array_slice($bookOwners, 0, $key);
            $secondPart = array_slice($bookOwners, $key + 1, count($bookOwners) - 1);

            $bookOwners = array_merge($firstPart, $secondPart);

            return true;
        }
        return false;
    }
}
