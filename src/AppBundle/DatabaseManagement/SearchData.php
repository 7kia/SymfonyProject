<?php
/**
 * Created by PhpStorm.
 * User: Илья
 * Date: 07.12.2017
 * Time: 15:22
 */

namespace AppBundle\DatabaseManagement;


class SearchData
{
    /** @var  string */
    private $searchTextField;
    /** @var  string */

    private $searchCategory;

    /**
     * @param $searchTextField
     */
    public function setSearchTextField($searchTextField)
    {
        $this->searchTextField = $searchTextField;
    }

    /**
     * @return string
     */
    public function getSearchTextField()
    {
        return $this->searchTextField;
    }

    /**
     * @param $searchCategory
     */
    public function setSearchCategory($searchCategory)
    {
        $this->searchCategory = $searchCategory;
    }

    /**
     * @return string
     */
    public function getSearchCategory()
    {
        return $this->searchCategory;
    }
}