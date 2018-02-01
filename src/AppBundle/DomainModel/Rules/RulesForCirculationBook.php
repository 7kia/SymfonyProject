<?php

namespace AppBundle\DomainModel\Rules;

use AppBundle\DatabaseManagement\DatabaseManager;
use AppBundle\Entity\ApplicationForBook;
use Symfony\Component\Config\Definition\Exception\Exception;

class RulesForCirculationBook extends MyRule
{
    /**
     * RulesForCirculationBook constructor.
     * @param $doctrine
     */
    public function __construct($doctrine)
    {
        $this->databaseManager = new DatabaseManager($doctrine);
    }

    /**
     * @param $bookId
     * @param $applicantId
     * @param $ownerId
     * @return bool
     */
    public function canSendApplicationToOwner($bookId, $applicantId, $ownerId)
    {
        $this->checkExistBook($bookId);
        $this->checkExistUser($applicantId);
        $this->checkExistUser($ownerId);

        if ($applicantId == $ownerId) {
            throw new Exception('Нельзя послать заявку самому себе');
        }

        return true;
    }

    /**
     * @param int $bookId
     * @param int $applicantId
     * @param int $ownerId
     * @return bool
     */
    public function canDeleteBookFromList($bookId, $applicantId, $ownerId)
    {
        $applicationForBook = $this->databaseManager->getApplicationForBook($bookId, $applicantId, $ownerId);
        return ($applicationForBook != null);
    }

    /**
     * @param int $bookId
     * @param int $applicantId
     * @param int $ownerId
     * @return bool
     */
    public function canAcceptBookFromList($bookId, $applicantId, $ownerId)
    {
        $applicationForBook = $this->databaseManager->getApplicationForBook($bookId, $applicantId, $ownerId);
        return ($applicationForBook != null);
    }
}