<?php

// src/AppBundle/Security/User/WebserviceUser.php
namespace AppBundle\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

class WebserviceUser implements UserInterface, EquatableInterface
{
    
    private $username;
    private $password;
    
    private $name;
    private $surname;
    private $patronymic;
    
    private $salt;
    private $roles;
    
    public function __construct($username, $password, $salt, array $roles)
    {
        $this->username = $username;
        $this->password = $password;
        $this->salt = $salt;
        $this->roles = $roles;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function getUsername()
    {
        return $this->username;
    }
    
    public function getSurname()
    {
        return $this->surname;
    }
    
    public function getPatronymic()
    {
        return $this->patronymic;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function eraseCredentials()
    {
    }

    public function isEqualTo(UserInterface $user)
    {
        // TODO : WTF!, add other parameters
        if (!$user instanceof WebserviceUser) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->salt !== $user->getSalt()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        return true;
    }
}