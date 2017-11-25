<?php
// src/AppBundle/Repository/BookRepository.php
namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class BookRepository extends EntityRepository
{
    public function findAllOrderedByName()
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT p FROM AppBundle:Book p ORDER BY p.name ASC'
            )
            ->getResult();
    }

    /**
     * @param $start
     * @param $end
     * @return mixed
     */
    public function getBookRange($start, $end)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('id')
            ->from('Book', 'id')
            ->where('id.start >= :start')
            ->andWhere('id.end <= :end')
            ->setParameters(array('start' => $start, 'end' => $end));

        return $qb->getQuery()->getArrayResult();
    }
}