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
     * @param $doctrine
     */
    public function __construct($doctrine)
    {
        $this->databaseManager = new DatabaseManager($doctrine);
    }

    /**
     * @param int $bookId
     * @param int $catalog
     * @param int $userId
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

    /**
     * @param string $catalog
     * @return bool
     */
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


    /**
     * @param int $deleteBookId
     * @param string $catalog
     * @param int $ownerId
     * @param int $currentUserId
     * @return bool
     */
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

    /**
     * @param int $ownerId
     * @param int $bookListName
     * @return bool
     */
    public function checkUserCatalog($ownerId, $bookListName)
    {
        $this->checkUserCatalogName($bookListName);
        $this->checkExistUser($ownerId);

        return true;
    }
}