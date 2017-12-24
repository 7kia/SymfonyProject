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

    private function getOwnerData($bookId, $readUsers)
    {
        $personalBooks = $this->databaseManager->findThingByCriteria(
            'AppBundle\Entity\UserListBook',
            array(
                'bookId' => strval($bookId),
                 'listName' => 'personal_books'
            )
        );

        return $this->extractUserData($personalBooks, $readUsers);
    }


    private function getReadUserData($bookId)
    {
        $takenBooks = $this->databaseManager->findThingByCriteria(
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

    /**
     * @param $bookId
     * @param $ownerId
     * @param $catalog
     * @return array
     */
    private function generateDataForPage($bookId, $ownerId, $catalog)
    {
        $bookData = $this->databaseManager->getOneThingByCriteria($bookId, "id", Book::class);
        if ($bookData == null) {
            throw new Exception(
                'Книга с id \''
                . $bookId
                . '\' не найдена '
            );
        }

        $notificationMessage = '';
        if ($catalog != null) {
            if ($this->addBookToUserCatalog($bookData, $catalog)) {
                $notificationMessage = 'Вы добавили эту книгу в свой каталог';
            } else {
                $notificationMessage = 'Эта книга там уже есть';
            }

        }

        $readUsers = $this->getReadUserData($bookData->getId());
        $ownerData = $this->getOwnerData($bookData->getId(), $readUsers);
        $ownerCount = count($ownerData);


        $currentUserInArray = $this->deleteCurrentUser($ownerData);
        if (($ownerData == null) and (!$currentUserInArray)) {
            throw new Exception(
                'Книга с названием \''
                . $bookId
                . '\' не имеет владельцев'
            );
        }

        $applicationStatusInfo = null;
        $sendApplicationToOwner = ($ownerId != null);
        if ($sendApplicationToOwner) {
            $applicationStatusInfo = $this->sendApplicationToOwner($ownerId, $bookData);
            if ($applicationStatusInfo == null) {
                throw new Exception(
                    'Книга с названием \'' . $bookId
                    . '\' не имеет владельца с id \'' . $ownerId . '\''
                );
            }
            $notificationMessage = 'Вы подали заявку';
        }

        return array(
            'serverUrl' => MyController::SERVER_URL,
            'currentUser' => $this->getCurrentUser(),
            'pageName' => 'book_page',
            'userLogin' => $this->userAuthorized(),
            'bookData' => $bookData,
            'ownerList' => $ownerData,
            'readUserList' => $readUsers,
            'applicationStatusInfo' => $applicationStatusInfo,
            'ownerCount' => $ownerCount,
            'readCount' => count($readUsers),
            'notificationMessage' => $notificationMessage
        );
    }

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
     * @param $bookId
     * @param $ownerId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function createPage($bookId, $ownerId, $catalog)
    {
        try {
            $this->checkCatalog($catalog);

            $pageData = $this->generateDataForPage($bookId, $ownerId, $catalog);
            return $this->render(
                MyController::TEMPLATE_PATH,
                $pageData
            );
        } catch (Exception $exception) {
            return $this->createErrorPage($exception->getMessage());
        }
    }


    /**
     * @Route("/book_page", name="book_page" )
     */
    public function showBookList()
    {
        $this->databaseManager = new DatabaseManager($this->getDoctrine());

        $bookName = $this->getParamFromGetRequest('book_id');
        $ownerName = $this->getParamFromGetRequest('send_application_to');
        $catalog = $this->getParamFromGetRequest('add_to_catalog');

        return $this->createPage($bookName, $ownerName, $catalog);
    }

    /**
     * @param $ownerId
     * @param $bookData
     * @return null|string(ApplicationInfo)
     */
    private function sendApplicationToOwner($ownerId, $bookData)
    {
        $foundOwner = $this->databaseManager->getOneThingByCriteria($ownerId, "id", User::class);
        if ($foundOwner == null) {
            return null;
        }

        $applicationStatus = $this->databaseManager->sendApplication(
            $foundOwner,
            $bookData,
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
                'avatar' => $currentUser->getAvatar()
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


}
