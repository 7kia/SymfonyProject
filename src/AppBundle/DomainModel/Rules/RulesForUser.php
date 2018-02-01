<?php

namespace AppBundle\DomainModel\Rules;

use AppBundle\DatabaseManagement\DatabaseManager;

class RulesForUser extends MyRule
{
    /**
     * RulesForUserBookCatalog constructor.
     * @param $doctrine
     */
    public function __construct($doctrine)
    {
        $this->databaseManager = new DatabaseManager($doctrine);
    }
}