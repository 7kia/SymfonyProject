<?php
/**
 * Created by PhpStorm.
 * User: Илья
 * Date: 06.12.2017
 * Time: 17:26
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MyController extends Controller
{
    protected function createErrorPage($errorMessage)
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
    protected function getParamFromGetRequest($arg_name)
    {
        if (isset($_GET[$arg_name])) {
            return $_GET[$arg_name];
        }

        return null;
    }
}