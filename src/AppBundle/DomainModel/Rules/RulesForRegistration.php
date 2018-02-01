<?php

namespace AppBundle\DomainModel\Rules;

use AppBundle\DatabaseManagement\DatabaseManager;
use AppBundle\Entity\User;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RulesForRegistration extends MyRule
{
    /**
     * RulesForRegistration constructor.
     * @param $doctrine
     */
    public function __construct($doctrine)
    {
        $this->databaseManager = new DatabaseManager($doctrine);
    }

    /**
     * @param User $user
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return bool
     */
    public function canRegisterUser(User $user, UserPasswordEncoderInterface $passwordEncoder)
    {
        if ($user == null) {
            throw new Exception('Данные для нового пользователя не созданы');
        }
        if ($passwordEncoder == null) {
            throw new Exception('Шифрователь пользовательских данных не создан');
        }
        return true;
    }
}