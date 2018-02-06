<?php

namespace AppBundle\DomainModel\Actions;

use AppBundle\DomainModel\Rules\RulesForCirculationBook;
use AppBundle\DomainModel\Strategies\StrategiesForCirculationBook;

class ActionsForCirculationBook
{
    /** @var  RulesForCirculationBook */
    private $rulesForCirculationBook;
    /** @var  StrategiesForCirculationBook */
    private $strategiesForCirculationBook;

    /**
     * ActionsForCirculationBook constructor.
     * @param $doctrine
     */
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

    /**
     * @param int $bookId
     * @param int $applicantId
     * @param int $ownerId
     * @return bool
     */
    public function deleteApplicationBook($bookId, $applicantId, $ownerId)
    {
        if ($this->rulesForCirculationBook
            ->canDeleteApplicationBook(
                $bookId,
                $applicantId,
                $ownerId
            )
        ) {
            return $this->strategiesForCirculationBook
                ->deleteBookApplication(
                    $bookId,
                    $applicantId,
                    $ownerId
                );
        }
        return false;
    }

    /**
     * @param int $bookId
     * @param int $applicantId
     * @param int $ownerId
     * @return bool
     */
    public function acceptBookFromList($bookId, $applicantId, $ownerId)
    {
        if ($this->rulesForCirculationBook
            ->canAcceptBookFromList(
                $bookId,
                $applicantId,
                $ownerId
            )
        ) {
            return $this->strategiesForCirculationBook
                ->acceptBookFromList(
                    $bookId,
                    $applicantId,
                    $ownerId
                );
        }
        return false;
    }

    /**
     * @param int $bookId
     * @param int $applicantId
     * @param int $ownerId
     * @return bool
     */
    public function deleteTakenBook($bookId, $applicantId, $ownerId)
    {
        if ($this->rulesForCirculationBook
            ->canDeleteTakenBook(
                $bookId,
                $applicantId,
                $ownerId
            )
        ) {
            return $this->strategiesForCirculationBook
                ->deleteTakenBook(
                    $bookId,
                    $applicantId,
                    $ownerId
                );
        }
        return false;
    }

}