<?php
/**
 * Created by PhpStorm.
 * User: Илья
 * Date: 06.12.2017
 * Time: 17:26
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Book;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MyController extends Controller
{
    const SERVER_URL = 'http://localhost:8000/';
    const TEMPLATE_PATH = 'template.html.twig';

    protected function getServerUrl()
    {
        return 'http://localhost:8000/';
    }

    protected function getTemplatePath()
    {
        return 'template.html.twig';
    }

    protected function userAuthorized()
    {
        $user = $this->getUser();
        return ($user != null);
    }

    protected function createErrorPage($errorMessage)
    {
        // TODO : create error page
        return $this->render(
            'error.html.twig',
            array(
                'errorMessage' => $errorMessage
            )
        );
    }

    protected function getMessageAboutLackArgument($argumentName)
    {
        return 'Не передан аргумент ' . $argumentName;
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
            return '7kia';
        }
    }

    protected function getCurrentUser()
    {
        if ($this->userAuthorized()) {
            return $this->getUser();
        } else {
            return  $this->getDoctrine()
                ->getRepository(User::class)
                ->findOneBy(array('username' => '7kia'));
        }
    }

    protected function findThingByCriteria(
        $searchPlace,
        $criteria
    )
    {
        $em = $this->getDoctrine()->getManager();

        $stringCriteria = '';
        while ($key = current($criteria)) {
            if (strlen($stringCriteria) > 1) {
                $stringCriteria = $stringCriteria . ' and ';
            }

            $stringCriteria = $stringCriteria . ' p.' . key($criteria) . '=\'' . $key . '\'';
            next($criteria);
        }

        $query = $em->createQuery(
            'SELECT p
                FROM ' . $searchPlace . ' p ' .
            'WHERE ' . $stringCriteria
        );
        return $query->execute();
    }

    /**
     * @param $searchText
     * @param $field
     * @param $class
     * @return object
     */
    protected function getOneThingByCriteria($searchText, $field, $class)
    {
        return $this->getDoctrine()
            ->getRepository($class)
            ->findOneBy(
                [$field => $searchText]
            );
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
                throw new Exception('Книга не найдена! Throw to UserBookCatalogController.extractBooks');
            }
        }

        return $books;
    }
}