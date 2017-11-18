<?php
// src/AppBundle/Entity/Message.php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="Message")
 */
class Message implements \Serializable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
	
	
	/**
     * @ORM\Column(name="bookId",type="integer")
     */
    private $bookId;
	
	/**
     * @ORM\Column(name="firstUserId",type="integer")
     */
    private $firstUserId;
	
	/**
     * @ORM\Column(name="secondUserId",type="integer")
     */
    private $secondUserId;
	
	/**
     * @ORM\Column(name="message", type="string")
     */
    private $message;
	
	/**
     * @ORM\Column(name="date",type="date")
     */
    private $date;
	
	// get/set bookId
    public function getBookId()
    {
        return $this->bookId;
    }

    public function setBookId($bookId)
    {
        $this->bookId = $bookId;
    }
	
	// get/set firstUserId
    public function getFirstUserId()
    {
        return $this->firstUserId;
    }

    public function setFirstUserId($firstUserId)
    {
        $this->firstUserId = $firstUserId;
    }
	
	// get/set secondUserId
    public function getSecondUserId()
    {
        return $this->secondUserId;
    }

    public function setSecondUserId($secondUserId)
    {
        $this->secondUserId = $secondUserId;
    }
	
	// get/set message
    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }
	
	// get/set date
    public function getDate()
    {
        return $this->date;
    }

    public function setDate($date)
    {
        $this->date = $date;
    }
	
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
            $this->firstUserId,
			$this->secondUserId,
			$this->message,
            // see section on salt below
            // $this->salt,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->firstUserId,
			$this->secondUserId,
			$this->message,
            // see section on salt below
            // $this->salt
        ) = unserialize($serialized);
    }


}