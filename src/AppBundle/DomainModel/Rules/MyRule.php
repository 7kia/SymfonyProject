<?php
/**
 * Created by PhpStorm.
 * User: Илья
 * Date: 02.01.2018
 * Time: 20:00
 */

namespace AppBundle\DomainModel\Rules;


use AppBundle\DatabaseManagement\DatabaseManager;
use AppBundle\Entity\Book;
use AppBundle\Entity\User;
use Symfony\Component\Config\Definition\Exception\Exception;

class MyRule
{
    /** @var  DatabaseManager */
    protected $databaseManager;

    /**
     * @param int $bookId
     * @param string $entityName
     * @return string
     */
    protected function getMessageNotExist($bookId, $entityName)
    {
        return $entityName . ' с id=' . $bookId . ' не существует';
    }

    /**
     * @param int $bookId
     * @return bool
     */
    protected function checkExistBook($bookId)
    {
        $addBook = $this->databaseManager->getOneThingByCriterion(
            $bookId,
            "id",
            Book::class
        );

        if ($addBook == null) {
            throw new Exception($this->getMessageNotExist($bookId, 'Книги'));
        }

        return true;
    }

    /**
     * @param int $userId
     * @return bool
     */
    public function checkExistUser($userId)
    {
        /** @var User $user */
        $user = $this->databaseManager->getOneThingByCriterion(
            $userId,
            "id",
            User::class
        );

        if ($user == null) {
            throw new Exception($this->getMessageNotExist($user, 'Пользователя'));
        }

        return true;
    }
}