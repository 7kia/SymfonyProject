<?php
/**
 * Created by PhpStorm.
 * User: Илья
 * Date: 20.12.2017
 * Time: 14:41
 */

namespace AppBundle\DatabaseManagement;

use AppBundle\Entity\ApplicationForBook;
use AppBundle\Entity\Book;
use AppBundle\Entity\User;
use AppBundle\Entity\TakenBook;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DatabaseManager
{
    private $doctrineManager = null;//$em

    function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
        $this->doctrineManager = $doctrine->getManager();
    }



    public function add($object)
    {
        $this->doctrineManager->persist($object);
        $this->doctrineManager->flush();
    }

    public function remove($object)
    {
        $this->doctrineManager->remove($object);
        $this->doctrineManager->flush();
    }

    /**
     * @param $searchPlace
     * @param $criteria
     * @return mixed
     */
    public function findThingByCriteria(
        $searchPlace,
        $criteria
    )
    {

        $stringCriteria = '';
        while ($key = current($criteria)) {
            if (strlen($stringCriteria) > 1) {
                $stringCriteria = $stringCriteria . ' and ';
            }

            $stringCriteria = $stringCriteria . ' p.' . key($criteria) . '=\'' . $key . '\'';
            next($criteria);
        }

        $query = $this->doctrineManager->createQuery(
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
    public function getOneThingByCriteria($searchText, $field, $class)
    {
        return $this->doctrine->getRepository($class)
            ->findOneBy(
                [$field => $searchText]
            );
    }

    /**
     * @param $idList
     * @param $class
     * @return array
     */
    public function getThings($idList, $class)
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
     * @param $userId
     * @param $bookListName
     * @return mixed
     */
    public function findUserCatalog($userId, $bookListName)
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
    public function extractBooks($catalog)
    {
        $repository = $this->doctrine->getRepository(Book::class);

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
    public function sendApplication(User $foundOwner, Book $bookData, User $currentUser)
    {
        $applicationForBook = new ApplicationForBook();
        $applicationForBook->setBookId($bookData->getId());
        $applicationForBook->setApplicantId($currentUser->getId());
        $applicationForBook->setOwnerId($foundOwner->getId());

        // TODO : проверь добавку повторной заявки

        $this->doctrineManager->persist($applicationForBook);
        $this->doctrineManager->flush();
    }
}