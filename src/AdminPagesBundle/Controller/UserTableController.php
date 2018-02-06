<?php
namespace AdminPagesBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Controller\MyController;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class UserTableController extends MyController
{
    /**
     * @Route("/Tables/users")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showPage(Request $request)
    {
        return $this->generatePage($request);
    }

    /**
     * @param Request $request
     * @param array $generationDataForPage
     * @return array
     */
    protected function generatePageData(Request $request, array $generationDataForPage)
    {
        $repository = $this->getDoctrine()->getRepository(User::class);
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