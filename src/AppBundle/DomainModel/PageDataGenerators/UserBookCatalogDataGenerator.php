<?php

namespace AppBundle\DomainModel\PageDataGenerators;

use AppBundle\Controller\MyController;
use AppBundle\DatabaseManagement\DatabaseManager;
use AppBundle\DomainModel\Rules\RulesForUser;
use AppBundle\DomainModel\Rules\RulesForUserBookCatalog;
use AppBundle\Entity\User;

class UserBookCatalogDataGenerator
{
    private $controller;
    private $databaseManager;
    private $rulesForUserBookCatalog;
    private $rulesForUser;

    public function __construct(MyController $controller)
    {
        $this->controller = $controller;
        $this->databaseManager = new DatabaseManager($this->controller->getDoctrine());
        $this->rulesForUserBookCatalog = new RulesForUserBookCatalog($this->controller->getDoctrine());
        $this->rulesForUser = new RulesForUser($this->controller->getDoctrine());
    }

    public function getUserCatalog($ownerId, $bookListName)
    {
        $existCatalog = $this->rulesForUserBookCatalog->checkUserCatalog($ownerId, $bookListName);

        if ($existCatalog) {
            $catalog = $this->databaseManager->findUserCatalog($ownerId, $bookListName);
            $catalogBooks = $this->databaseManager->extractBooks($catalog);

            return $catalogBooks;
        }

        return null;
    }

    /**
     * @param $bookListName
     * @param $userName
     * @return string|null
     */
    public function getCatalogTitle($bookListName, $userName)
    {
        if ($this->rulesForUserBookCatalog->checkUserCatalogName($bookListName)) {
            return $this->getCatalogName($bookListName) . ' пользователя ' . $userName;
        }
        return null;
    }

    /**
     * @param $bookListName
     * @return string
     */
    private function getCatalogName($bookListName)
    {
        switch ($bookListName)
        {
            case 'favorite_books':
                return 'Любимые книги';
            case 'read_later':
                return 'Прочитать позже';
            case 'personal_books':
                return 'Личные книги';
        }
        return '';
    }



}