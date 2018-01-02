<?php

namespace AdminPagesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('AdminPagesBundle:Default:index.html.twig');
    }
}
