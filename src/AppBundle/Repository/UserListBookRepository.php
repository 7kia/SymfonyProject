<?php
// src/AppBundle/Repository/UserRepository.php
namespace AppBundle\Repository;

use AppBundle\Entity\UserListBook;
use Doctrine\ORM\EntityRepository;

class UserListBookRepository extends  ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserListBook::class);
    }

    /**
     * @param $userId
     * @param $bookListName
     * @return UserListBook[]
     */
    public function findUserCatalog($userId, $bookListName)
    {

        // automatically knows to selects Products
        // the "p" is an alias you'll use in the rest of the query
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.userId = :userId and p.listName = bookListName')
            ->setParameter('userId', $userId)
            ->setParameter('bookListName', $bookListName)
            ->getQuery();
        print_r($qb->execute());
        return $qb->execute();

    }
}