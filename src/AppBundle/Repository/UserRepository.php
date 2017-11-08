<?php
// src/AppBundle/Repository/UserRepository.php
namespace AppBundle\Repository;

use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository implements UserLoaderInterface
{
    public function loadUserByUsername($username)
    {
        return $this->createQueryBuilder('u')
            ->where('u.username = :username OR u.email = :email')
            ->setParameter('username', $username)
            ->setParameter('email', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    // TODO : check work the function
    public function findAllOrderedByName()
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT p FROM AppBundle:User p ORDER BY p.name ASC'
            )
            ->getResult();
    }
    
    // TODO : check work the function
//    public function listAction()
//    {
//        $products = $this->getDoctrine()
//            ->getRepository(User::class)
//            ->findAllOrderedByName();
//    }
    
    
}