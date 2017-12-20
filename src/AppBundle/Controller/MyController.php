<?php
/**
 * Created by PhpStorm.
 * User: Илья
 * Date: 06.12.2017
 * Time: 17:26
 */

namespace AppBundle\Controller;

use AppBundle\DatabaseManagement\DatabaseManager;
use AppBundle\Entity\ApplicationForBook;
use AppBundle\Entity\Book;
use AppBundle\Entity\User;
use AppBundle\Entity\TakenBook;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MyController extends Controller
{
    const SERVER_URL = 'http://localhost:8000/';
    const TEMPLATE_PATH = 'template.html.twig';
    protected $databaseManager;

    /**
     * @return bool
     */
    protected function userAuthorized()
    {
        return ($this->getUser() != null);
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

    /**
     * @param $userLogin
     * @return string
     */
    // TODO : посмотри точно ли нужна
    protected function getCurrentUserName($userLogin)
    {
        if ($userLogin != false) {
            return $this->getUser()->getUsername();
        } else {
            return '7kia';
        }
    }

    /**
     * @return mixed|object
     */
    protected function getCurrentUser()
    {
        if ($this->userAuthorized()) {
            return $this->getUser();
        } else {
            return $this->databaseManager->getOneThingByCriteria('7kia', 'username', User::class);
        }
    }

}