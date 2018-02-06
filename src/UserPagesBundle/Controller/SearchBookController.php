<?php

namespace UserPagesBundle\Controller;

use AppBundle\DomainModel\Actions\ActionsForBook;
use AppBundle\DomainModel\PageDataGenerators\BookDataGenerator;
use AppBundle\DomainModel\PageDataGenerators\UserDataGenerator;
use AppBundle\Controller\MyController;
use AppBundle\DatabaseManagement\SearchData;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class SearchBookController extends MyController
{
    /** @var  Form */
    private $searchForm;

    /** @var  ActionsForBook */
    private $actionsForBook;
    /** @var  BookDataGenerator */
    private $bookDataGenerator;

    private function initComponents()
    {
        $this->actionsForBook = new ActionsForBook($this->getDoctrine());
        $this->bookDataGenerator = new BookDataGenerator($this);

        $this->userDataGenerator = new UserDataGenerator($this);
    }

    /**
     * @Route("/search_book", name="search_book" )
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showPage(Request $request)
    {
        $this->initComponents();
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
     * @param array $generationDataForPage
     */
    protected function checkGenerationDataForPage(array $generationDataForPage)
    {
        $searchTextDefined = ($generationDataForPage['search_text'] != null);
        $searchCategoryDefined = ($generationDataForPage['search_category'] != null);

        if (!$searchTextDefined and $searchCategoryDefined) {
            throw new Exception($this->getMessageAboutLackArgument('search_text'));
        } elseif ($searchTextDefined and !$searchCategoryDefined) {
            throw new Exception($this->getMessageAboutLackArgument('search_category'));
        }
    }


    /**
     * @param Form $form
     * @return bool
     */
    private function handleFormEvents(Form $form)
    {
        $clickedBtn = $form->getClickedButton();
        if ($clickedBtn != null) {// TODO : WARNING может не заработать(не то имя)
            return ($clickedBtn->getName() == 'searchBtn');
        }
        return false;
    }

    /**
     * @param Request $request
     * @param array $generationDataForPage
     * @return array
     */
    protected function generatePageData(Request $request, array $generationDataForPage)
    {
        $bookCards = array();
        if ($generationDataForPage['search_category'] != null) {

            $bookCards = $this->actionsForBook->findBooksByCategory(
                $generationDataForPage['search_text'],
                $generationDataForPage['search_category']
            );
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

    /**
     * @param Request $request
     */
    protected function handleFormElements(Request $request)
    {
        $searchData = new SearchData();
        $this->searchForm = $this->getSearchForm($searchData);
        $this->searchForm->handleRequest($request);


        $text = $searchData->getSearchTextField();
        $category = $searchData->getSearchCategory();

        $this->handleSearchFormElements(
            $this->searchForm,
            $text,
            $category
        );
    }

    /**
     * @param Form $form
     * @param string $text
     * @param string $category
     */
    protected function handleSearchFormElements($form, $text, $category)
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
     * @param SearchData $searchData
     * @return \Symfony\Component\Form\FormInterface
     */
    private function getSearchForm(SearchData $searchData)
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
