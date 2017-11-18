<?php
// src/AppBundle/Entity/TakenBook.php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

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
     * @ORM\Column(name="deadline",type="date")
     */
    private $deadline;
	
    // get/set bookId
    public function getBookId()
    {
        return $this->bookId;
    }

    public function setBookId($bookId)
    {
        $this->bookId = $bookId;
    }
	// get/set applicantId
    public function getApplicantId()
    {
        return $this->applicantId;
    }

    public function setApplicantId($applicantId)
    {
        $this->applicantId = $applicantId;
    }
	// get/set ownerId
    public function getOwnerId()
    {
        return $this->ownerId;
    }
	// get/set deadline
    public function getDeadline()
    {
        return $this->deadline;
    }

    public function setDeadline($deadline)
    {
        $this->deadline = $deadline;
    }
	
    public function setOwnerId($ownerId)
    {
        $this->ownerId = $ownerId;
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
            $this->bookId,
            $this->applicantId,
            $this->ownerId,
			$this->deadline,
            // see section on salt below
            // $this->salt,
        ));
    }

    /** @see \Serializable::unserialize() */
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