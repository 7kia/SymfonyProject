<?php

namespace AppBundle\DomainModel\Actions;

use AppBundle\DomainModel\Rules\RulesForBook;
use AppBundle\DomainModel\Strategies\StrategiesForBook;

class ActionsForBook
{
    /** @var  RulesForBook */
    private $rulesForBook;
    /** @var  StrategiesForBook */
    private $strategiesForBook;

    /**
     * ActionsForBook constructor.
     * @param $doctrine
     */
    public function __construct($doctrine)
    {
        $this->rulesForBook = new RulesForBook($doctrine);
        $this->strategiesForBook = new StrategiesForBook($doctrine);
    }

    /**
     * @param $searchText
     * @param $category
     * @return mixed|null
     */
    public function findBooksByCategory($searchText, $category)
    {
        if ($this->rulesForBook->canSearchBookByCategory($category)) {
            return $this->strategiesForBook->findBooksByCategory($searchText, $category);
        }
        return null;
    }
}