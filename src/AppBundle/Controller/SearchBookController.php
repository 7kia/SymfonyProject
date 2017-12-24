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
    private $searchForm;

    /**
     * @Route("/search_book", name="search_book" )
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showPage(Request $request)
    {
        return $this->generatePage($request);
    }

    /**
     * @return array
     */
    protected function getGenerationDataFromUrl()
    {
        $searchText = $this->getParamFromGetRequest('search_text');
        $searchCategory = $this->getParamFromGetRequest('search_category');

        return array(
            'search_text' => $searchText,
            'search_category' => $searchCategory
        );
    }

    /**
     * @param $generationDataForPage
     */
    protected function checkGenerationDataForPage($generationDataForPage)
    {
        if ($generationDataForPage['search_text'] != null) {

            $categories = array(
                'name',
                'author'
            );

            if (!in_array($generationDataForPage['search_category'], $categories)) {
                throw new Exception(
                    'Категория поиска должна иметь одно из следующих значений '
                    . implode(",", $categories)
                );
            }
        }
    }


    /**
     * @param $form
     * @return bool
     */
    private function handleFormEvents($form)
    {
        $clickedBtn = $form->getClickedButton();
        if ($clickedBtn != null) {// TODO : WARNING может не заработать(не то имя)
            return ($clickedBtn->getName() == 'searchBtn');
        }
    }

    /**
     * @param $request
     * @param $generationDataForPage
     * @return array
     */
    protected function generatePageData($request, $generationDataForPage)
    {
        $bookCards = array();
        if ($generationDataForPage['search_category'] != null) {
            $book = $this->databaseManager->getOneThingByCriteria($generationDataForPage['search_text'], $generationDataForPage['search_category'], Book::class);
            if ($book != null) {
                array_push($bookCards, $book);
            }
        }

        return array_merge(
            MyController::generatePageData($request, $generationDataForPage),
            array(
                'pageName' => 'search_book',
                'bookCards' => $bookCards,
                'searchForm' => $this->searchForm->createView()
            )
        );
    }

    protected function handleFormElements($request)
    {
        $searchData = new SearchData();
        $this->searchForm = $this->getSearchForm($searchData);
        $this->searchForm->handleRequest($request);


        $text = $searchData->getSearchTextField();
        $category = $searchData->getSearchCategory();

        $this->handleFormElement(
            $this->searchForm,
            $text,
            $category
        );
    }

    /**
     * @param $form
     * @param $text
     * @param $category
     */
    protected function handleFormElement($form, $text, $category)
    {
        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->handleFormEvents($form)) {

                $this->redirectData = array(
                    'route' =>'search_book',
                    'arguments' => array(
                        'search_text' => $text,
                        'search_category' => $category
                    )
                );
            }
        }
    }

    /**
     * @param $searchData
     * @return \Symfony\Component\Form\FormInterface
     */
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


}
