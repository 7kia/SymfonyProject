<?php

// src/AppBundle/Controller/SecurityController.php
namespace AppBundle\Controller;



use AppBundle\Entity\Book;
use AppBundle\Entity\User;
use AppBundle\Entity\UserListBook;
use AppBundle\Controller\MyController;
use AppBundle\SearchBook\SearchData;

use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class BookPageController extends MyController
{

    /**
     * @param $bookName -
     * @return \Symfony\Component\HttpFoundation\Response
     */
    function createPage($bookName)
    {
        return $this->render(
            $this->getTemplatePath(),
            array(
                "serverUrl" => $this->getServerUrl(),
                "currentUserName" => $this->getCurrentUserName($this->userAuthorized()),
                "pageName" => "bookPage",
                "userLogin" => $this->userAuthorized(),
            )
        );
    }


    /**
     * @Route("/bookPage", name="bookPage" )
     */
    public function showBookList()
    {
        $bookName = $this->getParamFromGetRequest("bookName");

        return $this->createPage($bookName);
    }


}
