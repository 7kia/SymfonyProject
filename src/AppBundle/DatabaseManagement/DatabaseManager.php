<?php

namespace AppBundle\DatabaseManagement;

use AppBundle\Entity\ApplicationForBook;
use AppBundle\Entity\Book;
use AppBundle\Entity\User;
use AppBundle\Entity\UserListBook;
use AppBundle\Security\ApplicationStatus;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;

class DatabaseManager
{
    /** @var DoctrineManager */
    private $doctrineManager = null;

    /**
     * DatabaseManager constructor.
     * @param $doctrine
     */
    function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
        $this->doctrineManager = $doctrine->getManager();
    }

    /**
     * @param $object
     */
    public function add($object)
    {
        $this->doctrineManager->persist($object);
        $this->doctrineManager->flush();
    }

    /**
     * @param $object
     */
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
        $stringCriteria = $this->generateCriteriaString($criteria);

        $query = $this->doctrineManager->createQuery(
            'SELECT p FROM ' . $searchPlace . ' p ' .
            'WHERE ' . $stringCriteria
        );
        return $query->execute();
    }

    /**
     * @param $criteria
     * @return string
     */
    private function generateCriteriaString($criteria)
    {
        $stringCriteria = '';
        while ($key = current($criteria)) {
            if (strlen($stringCriteria) > 1) {
                $stringCriteria = $stringCriteria . ' and ';
            }

            $stringCriteria = $stringCriteria . ' p.' . key($criteria) . '=\'' . $key . '\'';
            next($criteria);
        }

        return $stringCriteria;
    }

    /**
     * @param $searchText
     * @param $field
     * @param $class
     * @return object
     */
    public function getOneThingByCriterion($searchText, $field, $class)
    {
        return $this->doctrine->getRepository($class)
            ->findOneBy(
                [$field => $searchText]
            );
    }

    /**
     * @param $criteria
     * @param $class
     * @return mixed
     */
    public function getOneThingByCriteria($criteria, $class)
    {
        return $this->doctrine->getRepository($class)
            ->findOneBy(
                $criteria
            );
    }

    /**
     * @param array $idList
     * @param $class
     * @return array
     */
    public function getThings(array $idList, $class)
    {
        $books = array();
        foreach ($idList as $id) {
            $book = $this->getOneThingByCriterion(
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
     * @param int $userId
     * @param string $bookListName
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
     * @param array $catalog
     * @return array
     */
    public function extractBooks($catalog)
    {
        $repository = $this->doctrine->getRepository(Book::class);

        $books = array();

        /** @var UserListBook $catalogBook */
        foreach ($catalog as &$catalogBook) {
            $foundBook = $repository->find($catalogBook->getBookId());

            if ($foundBook != null) {
                array_push($books, $foundBook);
            } else {
                throw new Exception('Книга не найдена!');
            }
        }

        return $books;
    }

    /**
     * @param int $bookId
     * @param int $applicantId
     * @param int $ownerId
     * @return int
     */
    public function sendApplication(
        $bookId,
        $applicantId,
        $ownerId
    )
    {
        $application = $this->getOneThingByCriteria(
            array(
                'bookId' => $bookId,
                'applicantId' => $applicantId,
                'ownerId' => $ownerId
            ),
            ApplicationForBook::class
        );

        if ($application)
        {
            return ApplicationStatus::REPEAT_SEND;
        }

        $book = $this->getOneThingByCriterion($bookId, 'id', Book::class);
        $applicant = $this->getOneThingByCriterion($applicantId,'id', User::class);
        $owner = $this->getOneThingByCriterion($ownerId,'id', User::class);

        $applicationForBook = new ApplicationForBook();
        $applicationForBook->setBookId($book->getId());
        $applicationForBook->setApplicantId($applicant->getId());
        $applicationForBook->setOwnerId($owner->getId());

        $this->doctrineManager->persist($applicationForBook);
        $this->doctrineManager->flush();

        return ApplicationStatus::SEND_SUCCESSFUL;
    }

    /**
     * @param int $bookId
     * @param int $applicantId
     * @param int $ownerId
     * @return mixed
     */
    public function getApplicationForBook($bookId, $applicantId, $ownerId)
    {
        return $this->getOneThingByCriteria(
            array(
                'applicantId' => $applicantId,
                'ownerId' => $ownerId,
                'bookId' => $bookId
            ),
            ApplicationForBook::class
        );
    }
}