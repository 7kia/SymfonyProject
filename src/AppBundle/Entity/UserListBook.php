<?php
// src/AppBundle/Entity/UserListBook.php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="UserListBook")
 */
class UserListBook implements \Serializable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
	
	/**
     * @ORM\Column(name="listName", type="string")
     */
    private $listName;
	
	/**
     * @ORM\Column(name="bookId",type="integer")
     */
    private $bookId;
	
	/**
     * @ORM\Column(name="userId",type="integer")
     */
    private $userId;

    /**
     * @return mixed
     */
    public function getBookId()
    {
        return $this->bookId;
    }

    /**
     * @param $bookId
     */
    public function setBookId($bookId)
    {
        $this->bookId = $bookId;
    }

    /**
     * @return mixed
     */
    public function getListName()
    {
        return $this->listName;
    }

    /**
     * @param $listName
     */
    public function setListName($listName)
    {
        $this->listName = $listName;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }


    /**
     * @return null
     */
    public function getSalt()
    {
        // The bcrypt algorithm doesn't require a separate salt.
        // You *may* need a real salt if you choose a different encoder.
        return null;
    }


    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->listName,
            $this->bookId,
            $this->userId,
            // see section on salt below
            // $this->salt,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->listName,
            $this->userId,
            $this->ownerId,
            // see section on salt below
            // $this->salt
        ) = unserialize($serialized);
    }
}