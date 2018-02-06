<?php

namespace AppBundle\Controller;

use AppBundle\DatabaseManagement\DatabaseManager;
use AppBundle\DomainModel\PageDataGenerators\UserDataGenerator;
use AppBundle\Entity\User;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;

abstract class MyController extends Controller
{
    /** @var  DatabaseManager */
    protected $databaseManager;
    /** @var  array */
    protected $redirectData = null;
    /** @var string */
    protected $notificationMessage = null;
    /** @var string */
    protected $renderTemplate = 'template.html.twig';
    /** @var UserDataGenerator */
    protected $userDataGenerator;

    /**
     * @return array
     */
    protected function getGenerationDataFromUrl()
    {
        return array();
    }

    /**
     * @return \Doctrine\Bundle\DoctrineBundle\Registry
     */
    public function getDoctrine()
    {
        return parent::getDoctrine();
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return parent::getUser();
    }

    /**
     * @param $argumentName
     * @param $value
     */
    protected function checkMandatoryArgument($argumentName, $value)
    {
        if ($value == null) {
            throw new Exception(
                $this->getMessageAboutLackArgument($argumentName)
            );
        }
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
     * @param array|null $generationDataForPage
     */
    protected function checkGenerationDataForPage(array $generationDataForPage)
    {

    }

    /**
     * @param array $commandData
     */
    protected function checkCommandData(array $commandData)
    {

    }

    /**
     * @param Request $request
     */
    protected function handleFormElements(Request $request)
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
     * @param Request $request
     * @param array $generationDataForPage
     * @return array
     */
    protected function generatePageData(Request $request, array $generationDataForPage)
    {
        return array(
            'serverUrl' => 'http://localhost:8000/',
            'currentUser' => $this->userDataGenerator->getCurrentUser(),
            'userLogin' => $this->userDataGenerator->userAuthorized(),
        );
    }

    /**
     * @return array
     */
    protected function getCommandDataFromUrl()
    {
        return array();
    }


    /**
     * @param array $commandData
     */
    protected function commandProcessing(array  $commandData)
    {
    }

    /**
     * @param string $errorMessage
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
     * @param string $argumentName
     * @return string
     */
    protected function getMessageAboutLackArgument($argumentName)
    {
        return 'Не передан аргумент ' . $argumentName;
    }

    /**
     * @param string $argName
     * @return string|null
     */
    protected function getParamFromGetRequest($argName)
    {
        if (isset($_GET[$argName])) {
            return $_GET[$argName];
        }
        return null;
    }

}