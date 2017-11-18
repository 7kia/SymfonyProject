<?php
// src/AppBundle/Entity/Book.php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="Book")
 */
class Book implements \Serializable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
	
	/**
     * @ORM\Column(name="name", type="string")
     */
    private $name;
	
	/**
     * @ORM\Column(name="pageCount",type="integer")
     */
    private $pageCount;
	
	/**
     * @ORM\Column(name="description", type="string")
     */
    private $description;
	
	/**
     * @ORM\Column(name="isbn", type="string")
     */
    private $isbn;
	
	/**
     * @ORM\Column(name="rating",type="integer")
     */
    private $rating;
	
	// get/set name
    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
	
	// get/set pageCount
    public function getPageCount()
    {
        return $this->pageCount;
    }

    public function setPageCount($pageCount)
    {
        $this->pageCount = $pageCount;
    }
	
	// get/set description
    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }
	
	// get/set isbn
    public function getIsbn()
    {
        return $this->isbn;
    }

    public function setIsbn($isbn)
    {
        $this->isbn = $isbn;
    }

	// get/set rating
    public function getRating()
    {
        return $this->rating;
    }

    public function setRating($rating)
    {
        $this->rating = $rating;
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
            $this->name,
            $this->pageCount,
			$this->description,
			$this->isbn,
			$this->rating,
            // see section on salt below
            // $this->salt,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->name,
            $this->pageCount,
			$this->description,
			$this->isbn,
			$this->rating,
            // see section on salt below
            // $this->salt
        ) = unserialize($serialized);
    }


}