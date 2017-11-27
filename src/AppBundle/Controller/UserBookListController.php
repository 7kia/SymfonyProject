<?php

// src/AppBundle/Controller/SecurityController.php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserBookListController extends Controller
{
    function createPage($bookListName)
    {
        return $this->render(
            'userBookList.html.twig',
            array(
                "pageName" => "bookList",
                "bookListTitle" => $bookListName
            )
        );
    }

    function getParamFromGetRequest($arg_name)
    {
        if(isset($_GET[$arg_name])) {
            return $_GET[$arg_name];
        }

        throw new InvalidArgumentException($arg_name);
    }

    /**
     * @Route("/userBookList/{bookListName}", name="bookList" )
     */
    public function showBookList(Request $request, $bookListName)
    {
        $bookLists = array(
            "favoriteBooks",
            "readLater",
            "personalBooks"
        );

        // TODO : fix style
        if(in_array($bookListName, $bookLists)) {
            return $this->createPage($bookListName);
        } else {
            header('HTTP/1.0 404');
        }

        return $this->render(
            'template.html.twig',
            array(
                "pageName" => "bookList",
                "bookListTitle" => $bookListName
            )
        );
    }


}
