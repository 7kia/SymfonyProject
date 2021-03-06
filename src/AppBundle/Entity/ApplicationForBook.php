<?php
// src/AppBundle/Entity/ApplicationForBook.php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="ApplicationForBook")
 */
class ApplicationForBook implements \Serializable
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
            // see section on salt below
            // $this->salt
        ) = unserialize($serialized);
    }


}