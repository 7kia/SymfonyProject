<?php

namespace AppBundle\DomainModel\Strategies;

use AppBundle\DatabaseManagement\DatabaseManager;

class StrategiesForBook
{
    /**
     * StrategiesForBook constructor.
     * @param $doctrine
     */
    public function __construct($doctrine)
    {
        $this->databaseManager = new DatabaseManager($doctrine);
    }

    /**
     * @param string $searchText
     * @param string $category
     * @return mixed
     */
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