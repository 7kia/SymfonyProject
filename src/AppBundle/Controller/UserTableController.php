<?php
// src/AppBundle/Controller/UserTableController.php
namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class UserTableController extends Controller
{
    /**
    * @Route("/Tables/users")
    */
    public function newAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(User::class);
        // TODO : replace findAll to get(<range>)
        $users = $repository->findAll();

        $form = $this->createFormBuilder()->getForm();
        
        return $this->render(
            'Tables/UserTable.html.twig',
            array(
                'form' => $form->createView(),
                'users' => $users
            )
        );

    }
}