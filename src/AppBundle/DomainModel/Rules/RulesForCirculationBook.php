<?php

namespace AppBundle\DomainModel\Rules;

use AppBundle\DatabaseManagement\DatabaseManager;
use AppBundle\Entity\ApplicationForBook;
use Symfony\Component\Config\Definition\Exception\Exception;

class RulesForCirculationBook extends MyRule
{
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

        $this->checkApplicationForBook($bookId, $applicantId, $ownerId);

        if ($applicantId == $ownerId) {
            throw new Exception('Нельзя послать заявку самому себе');
        }

        return true;
    }

    private function checkApplicationForBook($bookId, $applicantId, $ownerId)
    {
        $application = $this->databaseManager->getOneThingByCriteria(
            array(
                'bookId' => $bookId,
                'applicantId' => $applicantId,
                'ownerId' => $ownerId,
            ),
            ApplicationForBook::class
        );

        if ($application == null) {
            throw new Exception(
                'Нет заявки с id книги ='. $bookId .
                ' id заявителя =' . $applicantId .
                ' id владельца =' . $ownerId
            );
        }

    }


}