<?php

namespace AppBundle\DomainModel\Rules;

use AppBundle\DatabaseManagement\DatabaseManager;
use Symfony\Component\Config\Definition\Exception\Exception;

class RulesForBook extends MyRule
{
    public function __construct($doctrine)
    {
        $this->databaseManager = new DatabaseManager($doctrine);
    }

    public function canSearchBookByCategory($category)
    {
        $categories = array(
            'name',
            'author'
        );

        if (!in_array($category, $categories)) {
            throw new Exception(
                'Категория поиска должна иметь одно из следующих значений '
                . implode(",", $categories)
            );
        }
        return true;
    }
}