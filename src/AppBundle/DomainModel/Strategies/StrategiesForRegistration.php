<?php

namespace AppBundle\DomainModel\Strategies;

use AppBundle\DatabaseManagement\DatabaseManager;
use AppBundle\Entity\User;

class StrategiesForRegistration
{
    public function __construct($doctrine)
    {
        $this->databaseManager = new DatabaseManager($doctrine);
    }

    public function registerUser(User $user, $passwordEncoder)
    {
        $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
        $user->setPassword($password);
        $user->setAvatar('');
        $user->setIsAdmin(false);

        $this->databaseManager->add($user);
    }
}