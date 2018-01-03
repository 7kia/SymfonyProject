<?php

namespace AppBundle\DomainModel\PageDataGenerators;

use AppBundle\Controller\MyController;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\DatabaseManagement\DatabaseManager;
use Symfony\Component\Config\Definition\Exception\Exception;

class CirculationBookDataGenerator
{
    protected $controller;
    protected $databaseManager;
    protected $userDataGenerator;

    public function __construct(MyController $controller)
    {
        $this->controller = $controller;
        $this->databaseManager = new DatabaseManager($this->controller->getDoctrine());
        $this->userDataGenerator = new UserDataGenerator($controller);
    }

    /**
     * @param $bookId
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
     * @param $bookId
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
}