<?php
/**
 * Created by PhpStorm.
 * User: Илья
 * Date: 06.12.2017
 * Time: 17:26
 */

namespace AppBundle\Controller;

use AppBundle\Entity\ApplicationForBook;
use AppBundle\Entity\Book;
use AppBundle\Entity\User;
use AppBundle\Entity\TakenBook;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MyController extends Controller
{
    const SERVER_URL = 'http://localhost:8000/';
    const TEMPLATE_PATH = 'template.html.twig';

    /**
     * @return bool
     */
    protected function userAuthorized()
    {
        return ($this->getUser() != null);
    }

    /**
     * @param $errorMessage
     * @return \Symfony\Component\HttpFoundation\Response
     */
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

    /**
     * @param $argumentName
     * @return string
     */
    protected function getMessageAboutLackArgument($argumentName)
    {
        return 'Не передан аргумент ' . $argumentName;
    }

    // TODO : возможно её стоит переметить в другой файл
    /**
     * @param $arg_name
     * @return string|null
     */
    protected function getParamFromGetRequest($arg_name)
    {
        if (isset($_GET[$arg_name])) {
            return $_GET[$arg_name];
        }

        return null;
    }

    /**
     * @param $userLogin
     * @return string
     */
    // TODO : посмотри точно ли нужна
    protected function getCurrentUserName($userLogin)
    {
        if ($userLogin != false) {
            return $this->getUser()->getUsername();
        } else {
            return '7kia';
        }
    }

    /**
     * @return mixed|object
     */
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

    /**
     * @param $searchPlace
     * @param $criteria
     * @return mixed
     */
    // TODO : надо бы вынести функции поиска и работы с БД из контроллера, проблема - нужены EntityManager и Repository
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

    /**
     * @param $idList
     * @return array
     */
    protected function getThings($idList, $class)
    {
        $books = array();
        foreach ($idList as $id) {
            $book = $this->getOneThingByCriteria(
                strval($id),
                'id',
                $class
            );
            if ($book != null) {
                array_push($books, $book);
            }
        }
        return $books;
    }


    /**
     * @param $bookId
     * @param $applicantId
     * @param $ownerId
     * @return bool
     */
    protected function giveBook($bookId, $applicantId, $ownerId)
    {
        $em = $this->getDoctrine()->getManager();

        $takenBook = new TakenBook();
        $takenBook->setBookId($bookId);
        $takenBook->setApplicantId($applicantId);
        $takenBook->setOwnerId($ownerId);
        // TODO : установить deadline
        $takenBook->setDeadline(new \DateTime());

        $em->persist($takenBook);
        $em->flush();

        return true;// TODO : пока не предесмотрена неудачная передача книги
    }

    /**
     * @param $userId
     * @param $bookListName
     * @return mixed
     */
    protected function findUserCatalog($userId, $bookListName)
    {
        return $this->findThingByCriteria(
            ' AppBundle\Entity\UserListBook',
            array(
                'userId' => $userId,
                'listName' => $bookListName
            )
        );
    }

    /**
     * @param $catalog
     * @return array
     */
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

    /**
     * @param User $foundOwner
     * @param Book $bookData
     * @param User $currentUser
     */
    protected function sendApplication(User $foundOwner, Book $bookData, User $currentUser)
    {
        $applicationForBook = new ApplicationForBook();
        $applicationForBook->setBookId($bookData->getId());
        $applicationForBook->setApplicantId($currentUser->getId());
        $applicationForBook->setOwnerId($foundOwner->getId());

        // TODO : проверь добавку повторной заявки

        $em = $this->getDoctrine()->getManager();
        $em->persist($applicationForBook);
        $em->flush();
    }
}