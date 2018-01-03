<?php

namespace AppBundle\DomainModel\Actions;

use AppBundle\DomainModel\Rules\RulesForCirculationBook;
use AppBundle\DomainModel\Strategies\StrategiesForCirculationBook;


class ActionsForCirculationBook
{
    private $rulesForCirculationBook;
    private $strategiesForCirculationBook;

    public function __construct($doctrine)
    {
        $this->rulesForCirculationBook = new RulesForCirculationBook($doctrine);
        $this->strategiesForCirculationBook = new StrategiesForCirculationBook($doctrine);
    }

    /**
     * @param $bookId
     * @param $applicantId
     * @param $ownerId
     * @return null|string
     */
    public function sendApplicationToOwner($bookId, $applicantId, $ownerId)
    {
        if ($this->rulesForCirculationBook
            ->canSendApplicationToOwner(
                $bookId,
                $applicantId,
                $ownerId
            )
        ) {
            return $this->strategiesForCirculationBook
                ->sendApplicationToOwner(
                    $bookId,
                    $applicantId,
                    $ownerId
                );
        }
        return null;



    }


}