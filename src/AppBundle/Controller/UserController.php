<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;
use Doctrine\DBAL\Driver\Connection;

class UserController extends Controller
{
    public function indexAction(Connection $conn)
    {
        $users = $conn->fetchAll('SELECT * FROM users');

        // ...
    }
}