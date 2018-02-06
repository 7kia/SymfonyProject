<?php

namespace AppBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class WebserviceUserProvider implements UserProviderInterface
{
    /**
     * @param string $username
     * @return \AppBundle\Security\User\WebserviceUser
     */
    public function loadUserByUsername($username)
    {
        // make a call to your webservice here
        $userData = new WebserviceUser($username, null, null, null);
        // pretend it returns an array on success, false if there is no user

        if ($userData) {
            $password = null;
            $salt = null;
            $roles = null;

            return new WebserviceUser($username, $password, $salt, $roles);
        }

        throw new UsernameNotFoundException(
            sprintf('Username "%s" does not exist.', $username)
        );
    }

    /**
     * @param UserInterface $user
     * @return \AppBundle\Security\User\WebserviceUser
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof WebserviceUser) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }
        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * @param string $class
     * @return bool
     */
    public function supportsClass($class)
    {
        return WebserviceUser::class === $class;
    }
}