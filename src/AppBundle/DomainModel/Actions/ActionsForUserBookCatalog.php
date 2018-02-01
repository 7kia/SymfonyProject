<?php

namespace AppBundle\DomainModel\Actions;

use AppBundle\DomainModel\Rules\RulesForUserBookCatalog;
use AppBundle\DomainModel\Strategies\StrategiesForUserBookCatalog;
use AppBundle\Entity\Book;
use AppBundle\Entity\UserListBook;

class ActionsForUserBookCatalog
{
    /** @var  StrategiesForRegistration */
    private $rulesForBookToUserCatalog;
    /** @var  StrategiesForRegistration */
    private $strategiesForUserBookCatalog;

    /**
     * ActionsForUserBookCatalog constructor.
     * @param $doctrine
     */
    public function __construct($doctrine)
    {
        $this->rulesForBookToUserCatalog = new RulesForUserBookCatalog($doctrine);
        $this->strategiesForUserBookCatalog = new StrategiesForUserBookCatalog($doctrine);
    }

    /**
     * @param int $bookId
     * @param string $catalog
     * @param int $userId
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

    /**
     * @param int $deleteBookId
     * @param string $catalog
     * @param int $ownerId
     * @param int $currentUserId
     * @return bool
     */
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