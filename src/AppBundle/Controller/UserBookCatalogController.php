<?php

// src/AppBundle/Controller/SecurityController.php
namespace AppBundle\Controller;



use AppBundle\Entity\Book;
use AppBundle\Entity\User;
use AppBundle\Entity\UserListBook;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserBookCatalogController extends Controller
{
    private function findUserCatalog($userId, $bookListName)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            "SELECT p
                FROM AppBundle\Entity\UserListBook p
                WHERE p.userId = '" . $userId . '\' and p.listName = \'' . $bookListName .'\''
        );
        print_r($query->execute());
        return $query->execute();

    }

    private function extractBooks($catalog)
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

    function getUserCatalog($ownerName, $bookListName)
    {
        $user =  $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(
                ['username' => $ownerName]
            );
        print_r($user);

        $catalog = $this->findUserCatalog($user->getId(), $bookListName);

        $catalogBooks = $this->extractBooks($catalog);
//        $em = $this->getDoctrine()->getManager();
//        $qb = $em->createQueryBuilder();
//        $qb->
//        $query = $qb->select(
//
        print_r($catalogBooks);

        return $catalogBooks;
    }

    function getCurrentUserName($userLogin)
    {
        if ($userLogin != false) {
            return $this->getUser()->getUsername();
        } else {
            return "7kia";
        }
    }

    function createPage($bookListName, $ownerName)
    {
        $user = $this->getUser();
        $userLogin = ($user != null);

        // TODO : на эту страницу можно будет зайти только авторизированному пользователю
        // пока для более быстрой отладки зе будет ограничении по доступу
        if ($ownerName == null) {
            $ownerName = $this->getCurrentUserName($userLogin);
        }

        $bookCards = null;
        //if ($userLogin) {
            print_r($userLogin);
            $bookCards = $this->getUserCatalog($ownerName, $bookListName);
        //}

        $catalogTitle = $bookListName . " пользователя " . $ownerName;

        return $this->render(
            'userBookCatalog.html.twig',
            array(
                "pageName" => "bookList",
                "bookListTitle" => $catalogTitle,
                "userLogin" => $userLogin,
                "bookCards" => $bookCards
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

        $ownerName = $this->getParamFromGetRequest("ownerName");

        if (in_array($bookListName, $bookLists)) {
            return $this->createPage($bookListName, $ownerName);
        }
        header('HTTP/1.0 404');
        createErrorPage("Ошибка, не передан аргумент  \'bookListName\'");
    }




}
