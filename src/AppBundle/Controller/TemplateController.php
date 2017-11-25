<?php

// src/AppBundle/Controller/SecurityController.php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class TemplateController extends Controller
{
    /**
     * @Route("/template")
     */
    public function indexAction(Request $request)
    {
        return $this->render(
            'template.html.twig',
            array(
                "pageName" => "bookList"
            )
        );
    }

}
