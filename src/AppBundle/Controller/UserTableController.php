<?php
// src/AppBundle/Controller/UserTableController.php
namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\DatabaseManagement\DatabaseManager;
use AppBundle\Controller\MyController;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


class UserTableController extends MyController
{
    /**
    * @Route("/Tables/users")
    */
    public function showPage(Request $request)
    {
        return $this->generatePage($request);
    }

    protected function generatePageData($request, $generationDataForPage)
    {
        $repository = $this->getDoctrine()->getRepository(User::class);
        // TODO : замени findAll на get(<range>)

        $users = $repository->findAll();

        $form = $this->createFormBuilder()->getForm();

        $this->renderTemplate = 'Tables/UserTable.html.twig';

        return array_merge(
            MyController::generatePageData($request, $generationDataForPage),
            array(
                'form' => $form->createView(),
                'users' => $users
            )
        );
    }
}