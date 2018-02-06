<?php

namespace UserPagesBundle\Controller;

use AppBundle\DomainModel\Actions\ActionsForCirculationBook;
use AppBundle\DomainModel\Actions\ActionsForUserBookCatalog;
use AppBundle\DomainModel\PageDataGenerators\BookDataGenerator;
use AppBundle\DomainModel\PageDataGenerators\CirculationBookDataGenerator;
use AppBundle\DomainModel\PageDataGenerators\UserDataGenerator;
use AppBundle\Controller\MyController;


use AppBundle\Security\ApplicationStatus;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class BookPageController extends MyController
{
    /** @var  ActionsForUserBookCatalog */
    private $actionsForUserBookCatalog;
    /** @var  ActionsForCirculationBook */
    private $actionsForCirculationBook;
    /** @var  CirculationBookDataGenerator */
    private $circulationBookDataGenerator;
    /** @var  BookDataGenerator */
    private $bookDataGenerator;
    /** @var ApplicationStatus */
    private $applicationStatusInfo = null;
    /** @var  int */
    private $bookId;

    private function initComponents()
    {
        $this->actionsForUserBookCatalog = new ActionsForUserBookCatalog($this->getDoctrine());
        $this->actionsForCirculationBook = new ActionsForCirculationBook($this->getDoctrine());

        $this->circulationBookDataGenerator = new CirculationBookDataGenerator($this);
        $this->bookDataGenerator = new BookDataGenerator($this);
        $this->userDataGenerator = new UserDataGenerator($this);
    }

    /**
     * @Route("/book_page", name="book_page" )
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showPage(Request $request)
    {
        $this->initComponents();
        return $this->generatePage($request);
    }

    /**
     * @return array
     */
    protected function getGenerationDataFromUrl()
    {
        $this->bookId = $this->getParamFromGetRequest('book_id');

        return array(
            'book_id' => $this->bookId,
        );
    }

    /**
     * @return array
     */
    protected function getCommandDataFromUrl()
    {
        $bookId = $this->getParamFromGetRequest('book_id');
        $ownerId = $this->getParamFromGetRequest('send_application_to');
        $catalog = $this->getParamFromGetRequest('add_to_catalog');

        return array(
            'book_id' => $bookId,
            'send_application_to' => $ownerId,
            'add_to_catalog' => $catalog,
            'current_user_id' => $this->userDataGenerator->getCurrentUser()->getId()
        );
    }

    /**
     * @param array $generationDataForPage
     */
    protected function checkGenerationDataForPage(array $generationDataForPage)
    {
        $this->checkMandatoryArgument('book_id', $generationDataForPage['book_id']);
    }

    /**
     * @param array $commandData
     */
    protected function checkCommandData(array $commandData)
    {
        $existSendArgument = ($commandData['send_application_to'] != null);
        $existCatalogArgument = ($commandData['add_to_catalog'] != null);

        if ($existSendArgument and $existCatalogArgument) {
            throw new Exception(
                'Можно использовать только 1 из аргументов
                 \'send_application_to\' или \'add_to_catalog\''
            );
        }
    }

    /**
     * @param array $commandData
     */
    protected function commandProcessing(array $commandData)
    {
        $currentUserId = $this->userDataGenerator->getCurrentUser()->getId();

        $this->notificationMessage = '';
        if ($commandData['add_to_catalog'] != null) {
            if ($this->actionsForUserBookCatalog->addBookToUserCatalog(
                    $commandData['book_id'],
                    $commandData['add_to_catalog'],
                    $currentUserId
                )
            ) {
                $this->notificationMessage = 'Вы добавили эту книгу в свой каталог';
            } else {
                $this->notificationMessage = 'Эта книга там уже есть';
            }
        }

        $sendApplicationToOwner = ($commandData['send_application_to'] != null);
        if ($sendApplicationToOwner) {
            $this->applicationStatusInfo = $this->actionsForCirculationBook->sendApplicationToOwner(
                $commandData['book_id'],
                $currentUserId,
                $commandData['send_application_to']
            );

            $this->notificationMessage = 'Вы подали заявку';
        }
    }


    /**
     * @param Request $request
     * @param array $generationDataForPage
     * @return array
     */
    protected function generatePageData(Request $request, array $generationDataForPage)
    {
        $readUsers = $this->circulationBookDataGenerator->getReadUserData($this->bookId);
        $ownerData = $this->circulationBookDataGenerator->getOwnerData($this->bookId);

        return array_merge(
            MyController::generatePageData($request, $generationDataForPage),
            array(
                'pageName' => 'book_page',
                'bookData' => $this->bookDataGenerator->getBookData($this->bookId),
                'ownerList' => $ownerData,
                'readUserList' => $readUsers,
                'ownerCount' => count($ownerData),
                'readCount' => count($readUsers),
                'applicationStatusInfo' => $this->applicationStatusInfo,
                'notificationMessage' => $this->notificationMessage
            )
        );
    }
}
