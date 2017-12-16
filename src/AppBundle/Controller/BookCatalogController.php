<?php

// src/AppBundle/Controller/SecurityController.php
namespace AppBundle\Controller;

use AppBundle\Entity\Book;
use AppBundle\Entity\User;
use AppBundle\Entity\UserListBook;
use AppBundle\Controller\MyController;
use AppBundle\SearchBook\SearchData;

use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class BookCatalogController extends MyController
{
    private function getSearchForm($searchData)
    {
        // TODO : поправь текст кнопки
        $form = $this->createFormBuilder($searchData)
            ->add(
                'searchBtn',
                SubmitType::class,
                array(
                    'attr' => array('class' => 'searchBtn'),
                    'label' => 'Поиск'
                )
            )
            ->add(
                'searchTextField',
                null,
                array(
                    'label' => false,
                    'attr' => array('class' => 'searchTextField'),
                    'data' => 'Здесь текст',
                )
            )
            ->add(
                'searchCategory',
                ChoiceType::class,
                array(
                    'choices'  => array(
                        'Название' => 'name',
                        'Автор' => 'author'
                    ),
                    'label' => false,
                    'attr' => array('class' => 'searchCategory'),
                )
            )
            ->getForm();

        return $form;
    }

    protected function handleFormEvents($form)
    {
        $clickedBtn = $form->getClickedButton();
        if ($clickedBtn != null) {
            return ($clickedBtn->getName() == 'searchBtn');
        }
    }

    /**
     * @param $searchText
     * @param $searchCategory
     * @return \Symfony\Component\HttpFoundation\Response
     */
    function createPage(Request $request, $searchText, $searchCategory)
    {
        $bookCards = array();
        if ($searchCategory != null) {
            $book = $this->getOneThingByCriteria($searchText, $searchCategory, Book::class);
            if ($book != null) {
                array_push($bookCards, $book);
            }
        }

        $searchData = new SearchData();
        $searchForm = $this->getSearchForm($searchData);
        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            if ($this->handleFormEvents($searchForm)) {
                $text = $searchData->getSearchTextField();
                $category = $searchData->getSearchCategory();

                return $this->redirectToRoute(
                    'bookCatalogs',
                    array(
                        'searchText' => $text,
                        'searchCategory' => $category
                    )
                );
            }
        }

        return $this->render(
            MyController::TEMPLATE_PATH,
            array(
                'serverUrl' => MyController::SERVER_URL,
                'currentUserName' => $this->getCurrentUserName($this->userAuthorized()),
                'pageName' => 'bookCatalog',
                'userLogin' => $this->userAuthorized(),
                'bookCards' => $bookCards,
                'searchForm' => $searchForm->createView()
            )
        );
    }


    /**
     * @Route("/bookCatalog", name="bookCatalogs" )
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showPage(Request $request)
    {

        $searchText = $this->getParamFromGetRequest('searchText');
        $searchCategory = null;
        if ($searchText != null) {
            $searchCategory = $this->getParamFromGetRequest('searchCategory');

            $categories = array(
                'name',
                'author'
            );

            if (!in_array($searchCategory, $categories)) {
                return $this->createErrorPage(
                    'Категория поиска должна иметь одно из следующих значений '
                    . implode(",", $categories)
                );
            }
        }


        return $this->createPage($request, $searchText, $searchCategory);
    }


}
