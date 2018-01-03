<?php

namespace UserPagesBundle\Controller;

use AppBundle\DomainModel\Actions\ActionsForCirculationBook;
use AppBundle\DomainModel\PageDataGenerators\CirculationBookDataGenerator;
use AppBundle\DomainModel\PageDataGenerators\UserDataGenerator;
use AppBundle\Entity\ApplicationForBook;
use AppBundle\Entity\Book;
use AppBundle\Entity\TakenBook;
use AppBundle\Entity\User;
use AppBundle\Entity\UserListBook;
use AppBundle\Controller\MyController;
use AppBundle\DatabaseManagement\SearchData;
use AppBundle\DatabaseManagement\DatabaseManager;

use AppBundle\Security\ApplicationStatus;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;


class CirculationBooksController extends MyController
{
    private $deleteCommandValue = null;
    private $acceptCommandValue = null;

    private $circulationBookDataGenerator;
    private $actionsForCirculationBook;

    private function initComponents()
    {
        $this->actionsForCirculationBook = new ActionsForCirculationBook($this->getDoctrine());
        $this->circulationBookDataGenerator = new CirculationBookDataGenerator($this);

        $this->userDataGenerator = new UserDataGenerator($this);
    }

    /**
     * @Route("/circulation_books", name="circulation_books" )
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
        $bookListName = $this->getParamFromGetRequest('book_list_name');

        return array(
            'book_list_name' => $bookListName
        );
    }

    /**
     * @param $generationDataForPage
     */
    protected function checkGenerationDataForPage($generationDataForPage)
    {
        $this->checkBookListName($generationDataForPage['book_list_name']);
    }

    /**
     * @param $bookListName
     */
    private function checkBookListName($bookListName)
    {
        $bookList = array(
            'taken_books',
            'given_books',
            'applications',
        );

        if ($bookListName == null) {
            throw new Exception($this->getMessageAboutLackArgument('book_list_name'));
        }
        if (!in_array($bookListName, $bookList)) {
            throw new Exception(
                'book_list_name должен иметь одно из следующих значений '
                . implode(",", $bookList)
            );
        }
    }

    /**
     * @return array
     */
    protected function getCommandDataFromUrl()
    {
        $bookListName = $this->getParamFromGetRequest('book_list_name');

        $this->deleteCommandValue = $this->getParamFromGetRequest('delete');
        $this->acceptCommandValue = $this->getParamFromGetRequest('accept');
        $otherUserId = $this->getParamFromGetRequest('other_user');

        $typeRequest = $this->getRequestType();

        return array(
            'delete' => $this->deleteCommandValue,
            'accept' => $this->acceptCommandValue,
            'otherUserId' => $otherUserId,
            'typeRequest' => $typeRequest,
            'bookId' => $this->getCommandArgument($typeRequest),
            'book_list_name' => $bookListName
        );
    }

    /**
     * @return null|string
     */
    private function getRequestType()
    {
        if ($this->acceptCommandValue) {
            return 'accept';
        } else if($this->deleteCommandValue) {
            return 'delete';
        }
        return null;
    }

    /**
     * @param $typeRequest
     * @return null
     */
    private function getCommandArgument($typeRequest)
    {
        switch ($typeRequest)
        {
            case 'delete':
                return $this->deleteCommandValue;
                break;
            case 'accept':
                return $this->acceptCommandValue;
                break;
        }
        return null;
    }

    /**
     * @param $commandData
     */
    protected function checkCommandData($commandData)
    {
        $this->checkCommands($commandData['book_list_name']);
        $this->checkOtherUser($commandData['otherUserId'], $commandData['typeRequest']);
    }

    /**
     * @param $bookListName
     * @return null|string
     */
    private function checkCommands($bookListName)
    {
        if (($this->acceptCommandValue != null) and ($this->deleteCommandValue != null)) {
            throw new Exception('Нельзя одновременно использовать запросы delete и accept');
        }
        if (($this->acceptCommandValue != null) and ($bookListName != 'applications')) {
            throw new Exception('Использовать запрос accept можно только в каталоге applications');
        }
    }

    /**
     * @param $otherUser
     * @param $typeRequest
     */
    private function checkOtherUser($otherUser, $typeRequest)
    {
        if (($otherUser == null) and ($typeRequest != null)) {
            throw new Exception($this->getMessageAboutLackArgument('other_user'));
        }
    }

    protected function commandProcessing($commandData)
    {
        if ($commandData['typeRequest'] != null) {
            if ($this->executeRequest($commandData, $commandData['book_list_name'])) {
                $this->redirectData = array(
                    'route' =>'circulation_books',
                    'arguments' => array(
                        'book_list_name' => $commandData['book_list_name'],
                    )
                );

            } else {
                throw new Exception('Запрос ' . $commandData['typeRequest'] . ' неудался');
            }
        }
    }

    private function executeRequest($requestValue)
    {
        $currentUser = $this->userDataGenerator->getCurrentUser();

        if ($requestValue['typeRequest'] == 'delete') {
            return $this->actionsForCirculationBook->deleteBookFromList(
                $requestValue['bookId'],
                $requestValue['otherUserId'],
                $currentUser->getId()
            );
        } else if ($requestValue['typeRequest'] == 'accept') {
            return $this->actionsForCirculationBook->acceptBookFromList(
                $requestValue['bookId'],
                $requestValue['otherUserId'],
                $currentUser->getId()
            );
        }
        return false;
    }

    protected function generatePageData($request, $generationDataForPage)
    {
        $currentUserData = $this->userDataGenerator->getCurrentUser();

        // TODO : исправь перевод в строковый формат {# { bookData[i].deadline }}#}

        return array_merge(
            MyController::generatePageData($request, $generationDataForPage),
            array(
                'pageName' => 'circulation_books',
                'bookData' => $this->circulationBookDataGenerator->getTableData(
                    $generationDataForPage['book_list_name'],
                    $currentUserData->getId()
                ),
                'book_list_name' => $generationDataForPage['book_list_name']
            )
        );
    }

}
