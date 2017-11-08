<?php
// src/AppBundle/Controller/UserTableController.php
namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Console\Helper\Table;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;

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