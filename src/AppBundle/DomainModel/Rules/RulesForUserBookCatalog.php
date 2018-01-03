<?php

namespace AppBundle\DomainModel\Rules;

use AppBundle\DatabaseManagement\DatabaseManager;
use AppBundle\Entity\Book;
use AppBundle\Entity\User;
use Symfony\Component\Config\Definition\Exception\Exception;

class RulesForUserBookCatalog extends MyRule
{


    /**
     * RulesForUserBookCatalog constructor.
     */
    public function __construct($doctrine)
    {
        $this->databaseManager = new DatabaseManager($doctrine);
    }

    /**
     * @param $bookId
     * @param $catalog
     * @param $userId
     * @return bool
     */
    public function canAddBookToUserCatalog(
        $bookId,
        $catalog,
        $userId
    )
    {
        $this->checkExistBook($bookId);
        $this->checkUserCatalog($catalog);
        $this->checkExistUser($userId);

        return true;
    }

    private function checkUserCatalog($catalog)
    {
        $catalogs = array(
            'favorite_books',
            'read_later',
            'personal_books'
        );

        if (!in_array($catalog, $catalogs)) {
            throw new Exception(
                'Каталога с именем \''
                . $catalog
                . '\' не существует'
            );
        }
    }



}