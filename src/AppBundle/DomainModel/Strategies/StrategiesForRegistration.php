<?php

namespace AppBundle\DomainModel\Strategies;

use AppBundle\DatabaseManagement\DatabaseManager;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class StrategiesForRegistration
{
    /**
     * StrategiesForRegistration constructor.
     * @param $doctrine
     */
    public function __construct($doctrine)
    {
        $this->databaseManager = new DatabaseManager($doctrine);
    }


    /**
     * @param User $user
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function registerUser(User $user, $passwordEncoder)
    {
        $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
        $user->setPassword($password);
        $user->setAvatar('');
        $user->setIsAdmin(false);

        $this->databaseManager->add($user);
    }
}