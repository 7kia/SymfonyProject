<?php
// src/AppBundle/Entity/TakenBook.php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="TakenBook")
 */
class TakenBook implements \Serializable
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
     * @ORM\Column(name="applicantId",type="integer")
     */
    private $applicantId;
	
	/**
     * @ORM\Column(name="ownerId",type="integer")
     */
    private $ownerId;

	/**
     * @ORM\Column(name="deadline", type="datetime")
     */
    private $deadline;

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
    public function getApplicantId()
    {
        return $this->applicantId;
    }

    /**
     * @param $applicantId
     */
    public function setApplicantId($applicantId)
    {
        $this->applicantId = $applicantId;
    }

    /**
     * @return mixed
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * @param $ownerId
     */
    public function setOwnerId($ownerId)
    {
        $this->ownerId = $ownerId;
    }

    /**
     * @return mixed
     */
    public function getDeadline()
    {
        return $this->deadline;
    }

    /**
     * @param $deadline
     */
    public function setDeadline($deadline)
    {
        $this->deadline = $deadline;
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
            $this->bookId,
            $this->applicantId,
            $this->ownerId,
			$this->deadline,
            // see section on salt below
            // $this->salt,
        ));
    }

    /** @see \Serializable::unserialize()
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->bookId,
            $this->applicantId,
            $this->ownerId,
			$this->deadline,
            // see section on salt below
            // $this->salt
        ) = unserialize($serialized);
    }
}