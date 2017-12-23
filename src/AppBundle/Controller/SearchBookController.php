<?php

// src/AppBundle/Controller/SecurityController.php
namespace AppBundle\Controller;

use AppBundle\Entity\Book;
use AppBundle\Entity\User;
use AppBundle\Entity\UserListBook;
use AppBundle\Controller\MyController;
use AppBundle\DatabaseManagement\SearchData;
use AppBundle\DatabaseManagement\DatabaseManager;

use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SearchBookController extends MyController
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
                    'attr' => array('class' => 'search-text-field'),

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
                    'attr' => array('class' => 'search-category'),
                )
            )
            ->getForm();

        return $form;
    }

    protected function handleFormEvents($form)
    {
        $clickedBtn = $form->getClickedButton();
        if ($clickedBtn != null) {// TODO : WARNING может не заработать(не то имя)
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
        try {
            $pageData = $this->generateDataForPage($request, $searchText, $searchCategory);
            return $this->render(
                MyController::TEMPLATE_PATH,
                $pageData
            );
        } catch (Exception $exception) {
            return $this->createErrorPage($exception->getMessage());
        }
    }


    /**
     * @Route("/search_book", name="search_book" )
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showPage(Request $request)
    {
        $this->databaseManager = new DatabaseManager($this->getDoctrine());

        $searchText = $this->getParamFromGetRequest('search_text');
        $searchCategory = null;
        if ($searchText != null) {
            $searchCategory = $this->getParamFromGetRequest('search_category');

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
        print_r($searchText);
        print_r($searchCategory);

        return $this->createPage($request, $searchText, $searchCategory);
    }

    private function generateDataForPage($request, $searchText, $searchCategory)
    {
        $bookCards = array();
        if ($searchCategory != null) {
            $book = $this->databaseManager->getOneThingByCriteria($searchText, $searchCategory, Book::class);
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



                $this->redirectToRoute(
                    'search_book',
                    array(
                        'search_text' => $text,
                        'search_category' => $category
                    )
                );
            }
        }

        return array(
            'serverUrl' => MyController::SERVER_URL,
            'currentUser' => $this->getCurrentUser(),
            'pageName' => 'search_book',
            'userLogin' => $this->userAuthorized(),
            'bookCards' => $bookCards,
            'searchForm' => $searchForm->createView()
        );
    }


}
