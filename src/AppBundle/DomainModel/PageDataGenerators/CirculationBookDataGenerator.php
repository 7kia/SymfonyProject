<?php

namespace AppBundle\DomainModel\PageDataGenerators;

use AppBundle\Controller\MyController;
use AppBundle\Entity\ApplicationForBook;
use AppBundle\Entity\Book;
use AppBundle\Entity\TakenBook;
use AppBundle\Entity\User;
use AppBundle\DatabaseManagement\DatabaseManager;
use Symfony\Component\Config\Definition\Exception\Exception;

class CirculationBookDataGenerator
{
    /** @var  MyController */
    protected $controller;
    /** @var  DatabaseManager */
    protected $databaseManager;
    /** @var  UserDataGenerator */
    protected $userDataGenerator;

    /**
     * CirculationBookDataGenerator constructor.
     * @param MyController $controller
     */
    public function __construct(MyController $controller)
    {
        $this->controller = $controller;
        $this->databaseManager = new DatabaseManager($this->controller->getDoctrine());
        $this->userDataGenerator = new UserDataGenerator($controller);
    }

    /**
     * @param int $bookId
     * @return array
     */
    public function getReadUserData($bookId)
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
     * @param int $bookId
     * @return array
     * @internal param $readUsers
     */
    public function getOwnerData($bookId)
    {
        $readUsers = $this->getReadUserData($bookId);

        $personalBooks = $this->databaseManager->findThingByCriteria(
            'AppBundle\Entity\UserListBook',
            array(
                'bookId' => $bookId,
                'listName' => 'personal_books'
            )
        );

        $ownerData = $this->extractUserData($personalBooks, $readUsers);

        $currentUserInArray = $this->deleteCurrentUser($ownerData);
        if (($ownerData == null) and (!$currentUserInArray)) {
            throw new Exception(
                'Книга с id='
                . $bookId
                . ' не имеет владельцев'
            );
        }

        return $ownerData;
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
            $owner = $this->databaseManager->getOneThingByCriterion(
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
            $owner = $this->databaseManager->getOneThingByCriterion(
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
     * @param $bookOwners
     * @return bool
     */
    private function deleteCurrentUser(&$bookOwners)
    {
        $currentUser = $this->userDataGenerator->getCurrentUser();
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



    /**
     * @param $bookListName
     * @param $userId
     * @return mixed|null
     */
    public function getTableData($bookListName, $userId)
    {
        switch ($bookListName)
        {
            case 'taken_books':
                return $this->getTakenBookTableData($userId);
            case 'given_books':
                return $this->getGivenBookTableData($userId);
            case 'applications':
                return $this->getApplicationTableData($userId);
        }
        return null;
    }

    /**
     * @param int $getId
     * @return mixed
     */
    private function getTakenBookTableData($getId)
    {
        $takenBooks = $this->databaseManager->findThingByCriteria(
            'AppBundle\Entity\TakenBook',
            array(
                'applicantId' => $getId
            )
        );

        $bookIds = array();
        $userIds = array();
        /** @var TakenBook $takenBook */
        foreach ($takenBooks as $takenBook) {
            array_push($bookIds, $takenBook->getBookId());
            array_push($userIds, $takenBook->getOwnerId());
        }
        return $this->generateTableData($bookIds, $userIds);
    }

    /**
     * @param int $getId
     * @return mixed
     */
    private function getGivenBookTableData($getId)
    {
        $givenBooks =  $this->databaseManager->findThingByCriteria(
            'AppBundle\Entity\TakenBook',
            array(
                'ownerId' => $getId
            )
        );

        // TODO : убери дублирование
        $bookIds = array();
        $users = array();
        foreach ($givenBooks as $givenBook) {
            /** @var TakenBook $givenBook */
            array_push($bookIds, $givenBook->getBookId());
            array_push($users, $givenBook->getApplicantId());
        }
        return $this->generateTableData($bookIds, $users);
    }

    /**
     * @param $getId
     * @return mixed
     */
    private function getApplicationTableData($getId)
    {
        $applicationForBooks = $this->databaseManager->findThingByCriteria(
            'AppBundle\Entity\ApplicationForBook',
            array(
                'ownerId' => $getId
            )
        );

        $bookIds = array();
        $users = array();
        foreach ($applicationForBooks as $applicationForBook) {
            /** @var ApplicationForBook $applicationForBook */
            array_push($bookIds, $applicationForBook->getBookId());
            array_push($users, $applicationForBook->getApplicantId());
        }

        return $this->generateTableData($bookIds, $users);
    }


    /**
     * @param int $bookIds
     * @param int $userIds
     * @return array
     */
    private function generateTableData($bookIds, $userIds)
    {
        $bookData = $this->databaseManager->getThings($bookIds, Book::class);
        $userNames = $this->getUsernames($userIds);

        return array(
            'books' => $bookData,
            'deadlines' => $this->getStringDeadline($bookData),
            'users' => $userNames,
            'userId' => $userIds
        );
    }

    /**
     * @param array $bookData
     * @return array
     */
    private function getStringDeadline(array $bookData)
    {
        // TODO : неправильный перевод даты в строковый формат
        $deadlines = array();
        foreach ($bookData as $data) {
            array_push($deadlines, $data->getDeadline()->format('Y-m-d H:i:s'));
        }

        return $deadlines;
    }

    /**
     * @param array $userIds
     * @return array
     */
    private function getUsernames(array $userIds)
    {
        $users = $this->databaseManager->getThings($userIds, User::class);
        $userNames = array();
        foreach ($users as $user) {
            if ($user != null) {
                array_push($userNames, $user->getUsername());
            }
        }

        return $userNames;
    }

}