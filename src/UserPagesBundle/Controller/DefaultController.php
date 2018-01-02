<?php

namespace UserPagesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('UserPagesBundle:Default:index.html.twig');
    }
}
