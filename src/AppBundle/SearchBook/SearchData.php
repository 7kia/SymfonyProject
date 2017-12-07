<?php
/**
 * Created by PhpStorm.
 * User: Илья
 * Date: 07.12.2017
 * Time: 15:22
 */

namespace AppBundle\SearchBook;


class SearchData
{
    private $searchTextField;
    private $searchCategory;

    // $searchTextField
    public function setSearchTextField($searchTextField)
    {
        $this->searchTextField = $searchTextField;
    }

    public function getSearchTextField()
    {
        return $this->searchTextField;
    }

    // $searchCategory
    public function setSearchCategory($searchCategory)
    {
        $this->searchCategory = $searchCategory;
    }

    public function getSearchCategory()
    {
        return $this->searchCategory;
    }
}