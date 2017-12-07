<?php

// src/AppBundle/Controller/SecurityController.php
namespace AppBundle\Controller;



use AppBundle\Entity\Book;
use AppBundle\Entity\User;
use AppBundle\Entity\UserListBook;
use AppBundle\Controller\MyController;


use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class BookCatalogController extends MyController
{

    private function getBookId($searchText, $field)
    {
        return $this->getDoctrine()
            ->getRepository(Book::class)
            ->findOneBy(
                [$field => $searchText]
            );
    }

    function createPage($searchText, $searchCategory)
    {
        $user = $this->getUser();
        $userLogin = ($user != null);

        $book = $this->getBookId($searchText, $searchCategory);

        $bookCards = array();
        if ($book != null) {
            array_push($bookCards, $book);
        }

        return $this->render(
            'userBookCatalog.html.twig',
            array(
                "serverUrl" => "http://localhost:8000/",
                "currentUserName" => $this->getCurrentUserName($userLogin),
                "pageName" => "bookCatalog",
                "userLogin" => $userLogin,
                "bookCards" => $bookCards
            )
        );
    }



    /**
     * @Route("/bookCatalog", name="bookCatalog" )
     */
    public function showBookList()
    {

        $searchText = $this->getParamFromGetRequest("searchText");
        $searchCategory = null;
        if ($searchText != null) {
            $searchCategory = $this->getParamFromGetRequest("searchCategory");

            $categories = array(
                "name",
                "author"
            );

            // TODO : посмотри позже как можно обработать ошибку
            if (!in_array($searchCategory, $categories)) {

                header('HTTP/1.0 500');
                // TODO : поправить вывод
                return $this->createErrorPage(
                    "Ошибка, категория поиска должно иметь любое из значений массива слева."
                    . print_r($categories)
                );
            }
        }


        return $this->createPage($searchText, $searchCategory);
    }


}
