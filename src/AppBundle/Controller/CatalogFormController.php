<?php
// src/AppBundle/Controller/Form1Controller.php
namespace AppBundle\Controller;

use AppBundle\Entity\Task;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CatalogFormController extends Controller
{
    /**
    * @Route("/catalog", name="catalog")
    */
    public function newAction(Request $request)
    {
        
        return $this->render(
                'catalog.html.twig',
                array(
                )
        );
    }
}