<?php

namespace AppBundle\DomainModel\Actions;

use AppBundle\DomainModel\Rules\RulesForBook;
use AppBundle\DomainModel\Strategies\StrategiesForBook;

class ActionsForBook
{
    private $rulesForBook;
    private $strategiesForBook;

    public function __construct($doctrine)
    {
        $this->rulesForBook = new RulesForBook($doctrine);
        $this->strategiesForBook = new StrategiesForBook($doctrine);
    }


    public function findBooksByCategory($searchText, $category)
    {
        if ($this->rulesForBook->canSearchBookByCategory($category)) {
            return $this->strategiesForBook->findBooksByCategory($searchText, $category);
        }
        return null;
    }
}