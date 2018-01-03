<?php

namespace AppBundle\DomainModel\Rules;

use AppBundle\DatabaseManagement\DatabaseManager;
use AppBundle\Entity\Book;
use AppBundle\Entity\User;
use AppBundle\Entity\UserListBook;
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
        $this->checkUserCatalogName($catalog);
        $this->checkExistUser($userId);

        return true;
    }

    public function checkUserCatalogName($catalog)
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

        return true;
    }

    public function canDeleteBookFormCatalog($deleteBookId, $catalog, $ownerId, $currentUserId)
    {
        $this->checkExistBook($deleteBookId);
        $this->checkUserCatalogName($catalog);
        $this->checkExistUser($ownerId);
        $this->checkExistUser($currentUserId);

        if ($currentUserId != $ownerId) {
            throw new Exception('Команда удаления книги из каталога доступна только владельцу книги');
        }

        return true;
    }

    public function checkUserCatalog($ownerId, $bookListName)
    {
        $this->checkUserCatalogName($bookListName);
        $this->checkExistUser($ownerId);

        return true;
    }


}