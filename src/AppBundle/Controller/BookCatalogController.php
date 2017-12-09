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

    private function handleClickedButtons(SearchData $searchData, $clickedBtn)
    {
        $runSearch = ($clickedBtn->getName() == 'searchBtn');
        if ($runSearch) {

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
            $clickedBtn = $searchForm->getClickedButton();
            if ($clickedBtn != null) {
                // TODO : вынос это куска кода в отдельную функцию отключает redirectToRoute
                // Возможно на момент запуска был глюк в Symfony
                $runSearch = ($clickedBtn->getName() == 'searchBtn');
                if ($runSearch) {

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
        }

        return $this->render(
            $this->getTemplatePath(),
            array(
                'serverUrl' => $this->getServerUrl(),
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

            // TODO : посмотри позже как можно обработать ошибку
            if (!in_array($searchCategory, $categories)) {

                header('HTTP/1.0 500');
                // TODO : поправить вывод
                return $this->createErrorPage(
                    'Ошибка, категория поиска выставлена не корректно.'
                );
            }
        }


        return $this->createPage($request, $searchText, $searchCategory);
    }


}
