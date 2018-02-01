<?php

namespace AppBundle\Controller;

use AppBundle\DomainModel\PageDataGenerators\UserDataGenerator;
use AppBundle\Controller\MyController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends MyController
{
    /** @var AuthenticationUtils */
    private $authUtils;

    private function initComponents()
    {
        $this->userDataGenerator = new UserDataGenerator($this);
    }

    /**
     * @Route("/login", name="login")
     * @param Request $request
     * @param AuthenticationUtils $authUtils
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showPage(Request $request, AuthenticationUtils $authUtils)
    {
        $this->initComponents();

        $this->authUtils = $authUtils;
        return $this->generatePage($request);
    }

    /**
     * @param Request $request
     * @param array $generationDataForPage
     * @return array
     */
    protected function generatePageData(Request $request, array $generationDataForPage)
    {
        // get the login error if there is one
        $error = $this->authUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername =  $this->authUtils->getLastUsername();

        $this->renderTemplate = 'authorization\\login.html.twig';

        return array_merge(
            MyController::generatePageData($request, $generationDataForPage),
            array(
                'last_username' => $lastUsername,
                'error'         => $error,
            )
        );
    }
}
