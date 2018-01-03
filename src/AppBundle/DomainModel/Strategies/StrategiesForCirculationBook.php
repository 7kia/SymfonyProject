<?php

namespace AppBundle\DomainModel\Strategies;

use AppBundle\DatabaseManagement\DatabaseManager;
use AppBundle\Entity\TakenBook;
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

    public function deleteBookFromList($bookId, $applicantId, $ownerId)
    {
        $applicationForBook = $this->databaseManager->getApplicationForBook($bookId, $applicantId, $ownerId);
        $this->databaseManager->remove($applicationForBook);

        return true;
    }

    public function acceptBookFromList($bookId, $applicantId, $ownerId)
    {
        $applicationForBook = $this->databaseManager->getApplicationForBook($bookId, $applicantId, $ownerId);
        $this->databaseManager->remove($applicationForBook);

        return $this->giveBook($bookId, $applicantId, $ownerId);
    }

    /**
     * @param $bookId
     * @param $applicantId
     * @param $ownerId
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

        return true;// TODO : пока не предесмотрена неудачная передача книги
    }
}