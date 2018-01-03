<?php

namespace AppBundle\DomainModel\Strategies;

use AppBundle\DatabaseManagement\DatabaseManager;
use AppBundle\Entity\UserListBook;

class StrategiesForUserBookCatalog
{
    /**
     * StrategiesForUserBookCatalog constructor.
     * @param $doctrine
     */
    public function __construct($doctrine)
    {
        $this->databaseManager = new DatabaseManager($doctrine);
    }

    public function addBookToUserCatalog($bookId, $catalog, $userId)
    {
        $sameBook = $this->databaseManager->findThingByCriteria(
            'AppBundle\Entity\UserListBook',
            array(
                'bookId' => $bookId,
                'listName' => $catalog,
                'userId' => $userId
            )
        );
        if ($sameBook != null) {
            return false;
        }

        $bookToCatalog = new UserListBook();
        $bookToCatalog->setBookId($bookId);
        $bookToCatalog->setListName($catalog);
        $bookToCatalog->setUserId($userId);

        $this->databaseManager->add($bookToCatalog);
        return true;
    }
}