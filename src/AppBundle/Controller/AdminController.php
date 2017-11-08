<?php

// src/AppBundle/Controller/SecurityController.php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AdminController extends Controller
{
     /**
     * @Route("/admin")
     */
    public function adminAction()
    {
        return $this->render(
                'admin.html.twig',
                array(
                )
        );
    }

}
