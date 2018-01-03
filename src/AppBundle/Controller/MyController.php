<?php
/**
 * Created by PhpStorm.
 * User: Илья
 * Date: 06.12.2017
 * Time: 17:26
 */

namespace AppBundle\Controller;

use AppBundle\DatabaseManagement\DatabaseManager;
use AppBundle\DomainModel\PageDataGenerators\UserDataGenerator;
use AppBundle\Entity\ApplicationForBook;
use AppBundle\Entity\Book;
use AppBundle\Entity\User;
use AppBundle\Entity\TakenBook;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;

abstract class MyController extends Controller
{
    const SERVER_URL = 'http://localhost:8000/';
    const TEMPLATE_PATH = 'template.html.twig';

    protected $databaseManager;
    protected $redirectData = null;// TODO нужен = null
    protected $notificationMessage = null;
    protected $renderTemplate = MyController::TEMPLATE_PATH;

    protected $userDataGenerator;

    /**
     * @return null
     */
    protected function getGenerationDataFromUrl()
    {
        return null;
    }

    public function getDoctrine()
    {
        return parent::getDoctrine();
    }

    public function getUser()
    {
        return parent::getUser();
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function generatePage(Request $request)
    {
        try {
            $this->databaseManager = new DatabaseManager($this->getDoctrine());

            $generationDataForPage = $this->getGenerationDataFromUrl();
            $commandData = $this->getCommandDataFromUrl();


            $this->checkGenerationDataForPage($generationDataForPage);
            $this->checkCommandData($commandData);

            $this->commandProcessing($commandData);

            $this->handleFormElements($request);
            if ($this->redirectData != null) {
                return $this->redirectToUrl($this->redirectData);
            }

            $pageData = $this->generatePageData($request, $generationDataForPage);

            return $this->render(
                $this->renderTemplate,
                $pageData
            );
        } catch (Exception $exception) {
            return $this->createErrorPage($exception->getMessage());
        }
    }

    /**
     * @param $generationDataForPage
     */
    protected function checkGenerationDataForPage($generationDataForPage)
    {

    }

    /**
     * @param $commandData
     */
    protected function checkCommandData($commandData)
    {

    }

    /**
     * @param $request
     */
    protected function handleFormElements($request)
    {
    }

    /**
     * @param $redirectData
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function redirectToUrl($redirectData)
    {
        return $this->redirectToRoute(
            $redirectData['route'],
            $redirectData['arguments']
        );
    }

    /**
     * @param $request
     * @param $generationDataForPage
     * @return array
     */
    protected function generatePageData($request, $generationDataForPage)
    {
        return array(
            'serverUrl' => MyController::SERVER_URL,
            'currentUser' => $this->userDataGenerator->getCurrentUser(),
            'userLogin' => $this->userDataGenerator->userAuthorized(),
        );
    }

    /**
     * @return null
     */
    protected function getCommandDataFromUrl()
    {
        return null;
    }


    /**
     * @param $commandData
     */
    protected function commandProcessing($commandData)
    {

    }


    /**
     * @param $errorMessage
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function createErrorPage($errorMessage)
    {
        // TODO : create error page
        return $this->render(
            'error.html.twig',
            array(
                'errorMessage' => $errorMessage
            )
        );
    }

    /**
     * @param $argumentName
     * @return string
     */
    protected function getMessageAboutLackArgument($argumentName)
    {
        return 'Не передан аргумент ' . $argumentName;
    }

    // TODO : возможно её стоит переметить в другой файл
    /**
     * @param $arg_name
     * @return string|null
     */
    protected function getParamFromGetRequest($arg_name)
    {
        if (isset($_GET[$arg_name])) {
            return $_GET[$arg_name];
        }

        return null;
    }




}