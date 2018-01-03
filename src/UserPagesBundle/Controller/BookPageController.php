<?php

namespace UserPagesBundle\Controller;

use AppBundle\DomainModel\Actions\ActionsForCirculationBook;
use AppBundle\DomainModel\Actions\ActionsForUserBookCatalog;
use AppBundle\DomainModel\PageDataGenerators\BookDataGenerator;
use AppBundle\DomainModel\PageDataGenerators\CirculationBookDataGenerator;
use AppBundle\DomainModel\PageDataGenerators\UserDataGenerator;
use AppBundle\Entity\ApplicationForBook;
use AppBundle\Entity\Book;
use AppBundle\Entity\User;
use AppBundle\Entity\UserListBook;
use AppBundle\Controller\MyController;
use AppBundle\DatabaseManagement\SearchData;
use AppBundle\DatabaseManagement\DatabaseManager;


use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class BookPageController extends MyController
{
    private $actionsForUserBookCatalog;
    private $actionsForCirculationBook;
    private $circulationBookDataGenerator;
    private $bookDataGenerator;

    private $applicationStatusInfo = null;

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

    protected function getGenerationDataFromUrl()
    {
        $this->bookId = $this->getParamFromGetRequest('book_id');

        return array(
            'book_id' => $this->bookId,
        );
    }

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

    protected function checkGenerationDataForPage($generationDataForPage)
    {
        if ($generationDataForPage['book_id'] == null) {
            throw new Exception(
                $this->getMessageAboutLackArgument('book_id')
            );
        }
    }

    protected function checkCommandData($commandData)
    {
        $existSendArgument = ($commandData['send_application_to'] != null);
        $existCatalogArgument = ($commandData['add_to_catalog'] != null);

        if (!$existSendArgument and $existCatalogArgument) {
            throw new Exception($this->getMessageAboutLackArgument('send_application_to'));
        }
        if ($existSendArgument and !$existCatalogArgument) {
            throw new Exception($this->getMessageAboutLackArgument('add_to_catalog'));
        }
    }

    protected function commandProcessing($commandData)
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
                $commandData['current_user_id'],
                $currentUserId
            );

            $this->notificationMessage = 'Вы подали заявку';
        }
    }


    protected function generatePageData($request, $generationDataForPage)
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
