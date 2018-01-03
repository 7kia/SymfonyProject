<?php

namespace AppBundle\DomainModel\PageDataGenerators;

use AppBundle\Controller\MyController;
use AppBundle\Entity\Book;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\DatabaseManagement\DatabaseManager;

class BookDataGenerator
{
    protected $controller;
    protected $databaseManager;


    public function __construct(MyController $controller)
    {
        $this->controller = $controller;
        $this->databaseManager = new DatabaseManager($this->controller->getDoctrine());
    }

    public function getBookData($bookId)
    {
        return $this->databaseManager->getOneThingByCriterion($bookId, 'id', Book::class);
    }

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