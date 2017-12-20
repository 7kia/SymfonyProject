<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ApplicationForBook;
use AppBundle\Entity\Book;
use AppBundle\Entity\User;
use AppBundle\Entity\UserListBook;
use AppBundle\Controller\MyController;
use AppBundle\SearchBook\SearchData;

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

    private function generateDataForPage($bookId, $ownerId)
    {
        $bookData = $this->getOneThingByCriteria($bookId, "id", Book::class);
        if ($bookData == null) {
            throw new Exception(
                'Книга с id \''
                . $bookId
                . '\' не найдена '
            );
        }

        $readUsers = $this->getReadUserData($bookData->getId());
        $bookOwners = $this->getOwnerData($bookData->getId(), $readUsers);
        $ownerCount = count($bookOwners);


        $currentUserInArray = $this->deleteCurrentUser($bookOwners);
        if (($bookOwners == null) and (!$currentUserInArray)) {
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
        }

        return array(
            'serverUrl' => MyController::SERVER_URL,
            'currentUserName' => $this->getCurrentUserName($this->userAuthorized()),
            'pageName' => 'bookPage',
            'userLogin' => $this->userAuthorized(),
            'bookData' => $bookData,
            'ownerList' => $bookOwners,
            'readUserList' => $readUsers,
            'applicationStatusInfo' => $applicationStatusInfo,
            'ownerCount' => $ownerCount,
            'readCount' => count($readUsers)
        );
    }


    /**
     * @param $bookId
     * @param $ownerId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function createPage($bookId, $ownerId)
    {
        try {
            $pageData = $this->generateDataForPage($bookId, $ownerId);
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
        $bookName = $this->getParamFromGetRequest('book_id');
        $ownerName = $this->getParamFromGetRequest('send_application_to');

        return $this->createPage($bookName, $ownerName);
    }

    /**
     * @param $ownerName
     * @param $bookData
     * @return null|string(ApplicationInfo)
     */
    private function sendApplicationToOwner($ownerName, $bookData)
    {
        $foundOwner = $this->getOneThingByCriteria($ownerName, "username", User::class);
        if ($foundOwner == null) {
            return null;
        }

        $applicationStatus = $this->sendApplication(
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

}
