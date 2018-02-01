<?php

namespace AppBundle\DomainModel\PageDataGenerators;

use AppBundle\Controller\MyController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\User;
use AppBundle\DatabaseManagement\DatabaseManager;

class UserDataGenerator
{
    /** @var MyController  */
    protected $controller;
    /** @var DatabaseManager  */
    protected $databaseManager;

    /**
     * UserDataGenerator constructor.
     * @param MyController $controller
     */
    public function __construct(MyController $controller)
    {
        $this->controller = $controller;
        $this->databaseManager = new DatabaseManager($this->controller->getDoctrine());
    }

    /**
     * @param int $userId
     * @return object
     */
    public function getUser($userId)
    {
        return $this->databaseManager->getOneThingByCriterion(
            $userId,
            'id',
            User::class
        );
    }

    /**
     * @return mixed|object
     */
    public function getCurrentUser()
    {
        if ($this->userAuthorized()) {
            return $this->controller->getUser();
        } else {
            return $this->databaseManager->getOneThingByCriterion('7kia', 'username', User::class);
        }
    }

    /**
     * @return bool
     */
    public function userAuthorized()
    {
        return ($this->controller->getUser() != null);
    }

    /**
     * @param bool $userLogin
     * @return string
     */
    public function getCurrentUserName($userLogin)
    {
        if ($userLogin != false) {
            return $this->controller->getUser()->getUsername();
        } else {
            return '7kia';
        }
    }
}