<?php
namespace AppBundle\Controller;

use AppBundle\DomainModel\Actions\ActionsForRegistration;
use AppBundle\DomainModel\PageDataGenerators\UserDataGenerator;
use AppBundle\Form\UserType;
use AppBundle\Entity\User;
use AppBundle\Controller\MyController;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class RegistrationController extends MyController
{
    /** @var  UserPasswordEncoderInterface */
    private $passwordEncoder;
    /** @var  Form */
    private $registerForm;
    /** @var  ActionsForRegistration */
    private $actionsForRegistration;

    private function initComponents()
    {
        $this->actionsForRegistration = new ActionsForRegistration($this->getDoctrine());
        $this->userDataGenerator = new UserDataGenerator($this);
    }

    /**
     * @Route("/register", name="user_registration")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function registerAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->initComponents();
        $this->passwordEncoder = $passwordEncoder;

        return $this->generatePage($request);
    }

    /**
     * @param Request $request
     */
    protected function handleFormElements(Request $request)
    {
        $user = new User();
        $this->registerForm = $this->createForm(UserType::class, $user);
        $this->registerForm->handleRequest($request);

        if ($this->registerForm->isSubmitted() && $this->registerForm->isValid()) {
            $this->actionsForRegistration->registerUser($user, $this->passwordEncoder);

            $this->redirectData = array(
                'route' =>'login',
                'arguments' => array(
                )
            );
        }
    }

    /**
     * @param Request $request
     * @param array $generationDataForPage
     * @return array
     */
    protected function generatePageData(Request $request, array $generationDataForPage)
    {
        $this->renderTemplate = 'authorization\\register.html.twig';

        return array_merge(
            MyController::generatePageData($request, $generationDataForPage),
            array(
                'form' => $this->registerForm->createView()
            )
        );
    }
}