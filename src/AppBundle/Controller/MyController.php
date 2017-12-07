<?php
/**
 * Created by PhpStorm.
 * User: Илья
 * Date: 06.12.2017
 * Time: 17:26
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Book;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MyController extends Controller
{
    protected function getServerUrl()
    {
        return "http://localhost:8000/";
    }

    protected function createErrorPage($errorMessage)
    {
        // TODO : create error page
        return $this->render(
            'error.html.twig',
            array(
                "errorMessage" => $errorMessage
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

    protected function getCurrentUserName($userLogin)
    {
        if ($userLogin != false) {
            return $this->getUser()->getUsername();
        } else {
            return "7kia";
        }
    }

    protected function findBooksByCriteria(
        $searchPlace,
        $value1, $field1,
        $value2, $field2
    )
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            "SELECT p
                FROM " . $searchPlace . " p " .
                "WHERE p." . $field1 . "='" . $value1 . "' and p." . $field2 . "='" . $value2 . "'"
        );
        return $query->execute();
    }

    protected function extractBooks($catalog)
    {
        $repository = $this->getDoctrine()->getRepository(Book::class);

        $books = array();

        foreach ($catalog as &$catalogBook) {
            $foundBook = $repository->find($catalogBook->getBookId());

            if ($foundBook != null) {
                array_push($books, $foundBook);
            } else {
                throw new Exception("Книга не найдена! Throw to UserBookCatalogController.extractBooks");
            }
        }

        return $books;
    }
}