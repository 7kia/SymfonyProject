<?php

namespace AppBundle\DomainModel\PageDataGenerators;

use AppBundle\Controller\MyController;
use AppBundle\Entity\Book;
use AppBundle\DatabaseManagement\DatabaseManager;

class BookDataGenerator
{
    /** @var  MyController */
    protected $controller;
    /** @var  DatabaseManager */
    protected $databaseManager;

    /**
     * BookDataGenerator constructor.
     * @param MyController $controller
     */
    public function __construct(MyController $controller)
    {
        $this->controller = $controller;
        $this->databaseManager = new DatabaseManager($this->controller->getDoctrine());
    }

    /**
     * @param int $bookId
     * @return object
     */
    public function getBookData($bookId)
    {
        return $this->databaseManager->getOneThingByCriterion($bookId, 'id', Book::class);
    }

    /**
     * @param $searchText
     * @param $category
     * @return mixed
     */
    public function findBooksByCategory($searchText, $category)
    {
        return $this->databaseManager->findThingByCriteria(
            'AppBundle\Entity\Book',
            array(
                $category => $searchText
            )
        );
    }

}