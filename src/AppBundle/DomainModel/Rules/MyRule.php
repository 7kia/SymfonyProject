<?php
/**
 * Created by PhpStorm.
 * User: Илья
 * Date: 02.01.2018
 * Time: 20:00
 */

namespace AppBundle\DomainModel\Rules;


use AppBundle\Entity\Book;
use AppBundle\Entity\User;

class MyRule
{
    protected $databaseManager;

    /**
     * @param $bookId
     * @return string
     * @internal param $argumentName
     */
    protected function getMessageNotExist($bookId, $entityName)
    {
        return $entityName . ' с id=' . $bookId . ' не существует';
    }

    /**
     * @param $bookId
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
     * @param $userId
     * @return bool
     */
    public function checkExistUser($userId)
    {
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