<?php

// src/AppBundle/Controller/SecurityController.php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserBookCatalogController extends Controller
{
    function createPage($bookListName, $userLogin)
    {
        return $this->render(
            'userBookCatalog.html.twig',
            array(
                "pageName" => "bookList",
                "bookListTitle" => $bookListName,
                "userLogin" => $userLogin
            )
        );
    }

    function createErrorPage($errorMessage)
    {
        // TODO : create error page
        return $this->render(
            'template.html.twig',
            array(
                "pageName" => "bookList",
                "bookListTitle" => $errorMessage
            )
        );
    }

    // TODO : see might put in a separate file
    function getParamFromGetRequest($arg_name)
    {
        if(isset($_GET[$arg_name])) {
            return $_GET[$arg_name];
        }

        return null;
    }

    /**
     * @Route("/userBookCatalog", name="userBookCatalogs" )
     */
    public function showBookList()
    {
        $bookLists = array(
            "favoriteBooks",
            "readLater",
            "personalBooks"
        );

        $bookListName = $this->getParamFromGetRequest("bookListName");
        if ($bookListName == null) {
            $bookListName = "personalBooks";
        }

        $user = $this->getUser();
        $userLogin = ($user != null);

        if (in_array($bookListName, $bookLists)) {
            return $this->createPage($bookListName, $userLogin);
        }
        header('HTTP/1.0 404');
        createErrorPage("Ошибка, не передан аргумент  \'bookListName\'");
    }


}
