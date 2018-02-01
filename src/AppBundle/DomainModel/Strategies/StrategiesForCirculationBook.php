<?php

namespace AppBundle\DomainModel\Strategies;

use AppBundle\DatabaseManagement\DatabaseManager;
use AppBundle\Entity\ApplicationForBook;
use AppBundle\Entity\TakenBook;
use AppBundle\Security\ApplicationStatus;

class StrategiesForCirculationBook
{
    /**
     * StrategiesForCirculationBook constructor.
     * @param $doctrine
     */
    public function __construct($doctrine)
    {
        $this->databaseManager = new DatabaseManager($doctrine);
    }

    /**
     * @param int $bookId
     * @param int $applicantId
     * @param int $ownerId
     * @return string
     */
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
     * @param ApplicationStatus $applicationStatus
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

    /**
     * @param int $bookId
     * @param int $applicantId
     * @param int $ownerId
     * @return bool
     */
    public function deleteBookFromList($bookId, $applicantId, $ownerId)
    {
        $applicationForBook = $this->databaseManager->getApplicationForBook($bookId, $applicantId, $ownerId);
        $this->databaseManager->remove($applicationForBook);

        return true;
    }

    /**
     * @param int $bookId
     * @param int $applicantId
     * @param int $ownerId
     * @return bool
     */
    public function acceptBookFromList($bookId, $applicantId, $ownerId)
    {
        $applicationForBook = $this->databaseManager->getApplicationForBook($bookId, $applicantId, $ownerId);
        $this->databaseManager->remove($applicationForBook);

        return $this->giveBook($bookId, $applicantId, $ownerId);
    }

    /**
     * @param int $bookId
     * @param int $applicantId
     * @param int $ownerId
     * @return bool
     */
    private function giveBook($bookId, $applicantId, $ownerId)
    {
        $takenBook = new TakenBook();
        $takenBook->setBookId($bookId);
        $takenBook->setApplicantId($applicantId);
        $takenBook->setOwnerId($ownerId);
        // TODO : установить deadline
        $takenBook->setDeadline(new \DateTime());

        $this->databaseManager->add($takenBook);

        return true;
    }
}