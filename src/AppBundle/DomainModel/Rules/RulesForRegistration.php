<?php

namespace AppBundle\DomainModel\Rules;

use AppBundle\DatabaseManagement\DatabaseManager;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RulesForRegistration extends MyRule
{
    public function __construct($doctrine)
    {
        $this->databaseManager = new DatabaseManager($doctrine);
    }

    public function canRegisterUser($user, UserPasswordEncoderInterface $passwordEncoder)
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