<?php
// src/AppBundle/Controller/RegistrationController.php
namespace AppBundle\Controller;

use AppBundle\Form\UserType;
use AppBundle\Entity\User;
use AppBundle\DatabaseManagement\DatabaseManager;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class RegistrationController extends MyController
{
    // TODO : когда админ сможет редактировать список пользователей поменяй:
    // return $this->redirectToRoute('catalog');// 40-41 строка
    /**
     * @Route("/register", name="user_registration")
     */
    public function registerAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->databaseManager = new DatabaseManager($this->getDoctrine());

        // 1) build the form
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);

        //print_r($form->isSubmitted());
        print_r($form->isSubmitted());
        if ($form->isSubmitted() && $form->isValid()) {
            print_r($form->isSubmitted());
            // 3) Encode the password (you could also do this via Doctrine listener)
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $user->setAvatar('');
            $user->setIsAdmin(false);

            // 4) save the User!
            //$this->databaseManager->add($user);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            // ... do any other work - like sending them an email, etc
            // maybe set a "flash" success message for the user
            return $this->redirectToRoute(
                'login'
            );

        }

        return $this->render(
            'authorization/register.html.twig',
            array('form' => $form->createView())
        );


    }
}