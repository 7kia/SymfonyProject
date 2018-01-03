<?php

namespace AppBundle\DomainModel\Strategies;

use AppBundle\DatabaseManagement\DatabaseManager;

class StrategiesForBook
{
    public function __construct($doctrine)
    {
        $this->databaseManager = new DatabaseManager($doctrine);
    }

    public function findBooksByCategory($searchText, $category)
    {
        return $this->databaseManager->findThingByCriteria(
            'AppBundle\Entity\Book',
            array(
                $category => $searchText,
            )
        );
    }
}