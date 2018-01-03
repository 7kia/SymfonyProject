<?php

namespace AppBundle\DomainModel\Actions;

use AppBundle\DomainModel\Rules\RulesForUserBookCatalog;
use AppBundle\DomainModel\Strategies\StrategiesForUserBookCatalog;
use AppBundle\Entity\Book;
use AppBundle\Entity\UserListBook;

class ActionsForUserBookCatalog
{
    private $rulesForBookToUserCatalog;
    private $strategiesForUserBookCatalog;

    public function __construct($doctrine)
    {
        $this->rulesForBookToUserCatalog = new RulesForUserBookCatalog($doctrine);
        $this->strategiesForUserBookCatalog = new StrategiesForUserBookCatalog($doctrine);
    }

    /**
     * @param $bookId
     * @param $catalog
     * @param $userId
     * @return bool
     * @internal param Book $addBook
     */
    public function addBookToUserCatalog($bookId, $catalog, $userId)
    {
        if ($this->rulesForBookToUserCatalog->canAddBookToUserCatalog(
            $bookId,
            $catalog,
            $userId
        )) {
            return $this->strategiesForUserBookCatalog->addBookToUserCatalog($bookId, $catalog, $userId);
        }
        return false;
    }

    public function deleteBookFormCatalog($deleteBookId, $catalog, $ownerId, $currentUserId)
    {
        if ($this->rulesForBookToUserCatalog->canDeleteBookFormCatalog(
            $deleteBookId,
            $catalog,
            $ownerId,
            $currentUserId
        )) {
            return $this->strategiesForUserBookCatalog->deleteBookFormCatalog($deleteBookId, $catalog, $ownerId);
        }
        return false;
    }

}