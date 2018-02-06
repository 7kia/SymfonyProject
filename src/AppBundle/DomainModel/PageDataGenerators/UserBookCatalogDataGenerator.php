<?php

namespace AppBundle\DomainModel\PageDataGenerators;

use AppBundle\Controller\MyController;
use AppBundle\DatabaseManagement\DatabaseManager;
use AppBundle\DomainModel\Rules\RulesForUser;
use AppBundle\DomainModel\Rules\RulesForUserBookCatalog;

class UserBookCatalogDataGenerator
{
    /** @var MyController */
    private $controller;
    /** @var DatabaseManager  */
    private $databaseManager;
    /** @var RulesForUserBookCatalog  */
    private $rulesForUserBookCatalog;
    /** @var RulesForUser  */
    private $rulesForUser;

    /**
     * UserBookCatalogDataGenerator constructor.
     * @param MyController $controller
     */
    public function __construct(MyController $controller)
    {
        $this->controller = $controller;
        $this->databaseManager = new DatabaseManager($this->controller->getDoctrine());
        $this->rulesForUserBookCatalog = new RulesForUserBookCatalog($this->controller->getDoctrine());
        $this->rulesForUser = new RulesForUser($this->controller->getDoctrine());
    }

    /**
     * @param int $ownerId
     * @param string $bookListName
     * @return array|null
     */
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
     * @param string $bookListName
     * @param string $userName
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
     * @param string $bookListName
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