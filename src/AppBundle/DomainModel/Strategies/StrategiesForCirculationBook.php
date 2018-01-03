<?php

namespace AppBundle\DomainModel\Strategies;

use AppBundle\DatabaseManagement\DatabaseManager;
use AppBundle\Security\ApplicationStatus;

class StrategiesForCirculationBook
{
    public function __construct($doctrine)
    {
        $this->databaseManager = new DatabaseManager($doctrine);
    }

    public function sendApplicationToOwner(
        $bookId,
        $applicantId,
        $ownerId
    )
    {
        $applicationStatus = $this->databaseManager->sendApplication(
            $bookId,
            $applicantId,
            $ownerId
        );

        return $this->getApplicationInfo($applicationStatus);
    }

    /**
     * @param $applicationStatus
     * @return string
     */
    private function getApplicationInfo($applicationStatus)
    {
        switch ($applicationStatus) {
            case ApplicationStatus::REPEAT_SEND:
                return 'Заявка подана повторно';
                break;
            case ApplicationStatus::SEND_SUCCESSFUL:
                return 'Вы подали заявку';
                break;
            default:
                return 'Не удалось подать заявку';
                break;
        }
    }
}