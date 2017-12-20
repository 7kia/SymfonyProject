<?php
// src/AppBundle/Controller/UserTableController.php
namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\DatabaseManagement\DatabaseManager;

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
        $this->databaseManager = new DatabaseManager($this->getDoctrine());

        $repository = $this->getDoctrine()->getRepository(User::class);
        // TODO : замени findAll на get(<range>)
        // TODO : отредактируй когда будешь профиль добавлять
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