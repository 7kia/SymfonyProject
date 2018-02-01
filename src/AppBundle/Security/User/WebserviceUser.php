<?php

// src/AppBundle/Security/User/WebserviceUser.php
namespace AppBundle\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

class WebserviceUser implements UserInterface, EquatableInterface
{
    /** @var  string */
    private $username;
    /** @var  string */
    private $password;
    /** @var  array */
    private $roles;
    private $salt;

    /**
     * WebserviceUser constructor.
     * @param string $username
     * @param string $password
     * @param $salt
     * @param array $role
     */
    public function __construct($username, $password, $salt, array $role)
    {
        $this->username = $username;
        $this->password = $password;
        $this->roles = $role;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return mixed
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    public function eraseCredentials()
    {
    }

    /**
     * @param UserInterface $user
     * @return bool
     */
    public function isEqualTo(UserInterface $user)
    {
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