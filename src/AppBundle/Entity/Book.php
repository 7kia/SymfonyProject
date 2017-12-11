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
     * @ORM\Column(name="author", type="string")
     */
    private $author;

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

    /**
     * @ORM\Column(name="deadline",type="date")
     */
    private $deadline;

	/**
     * @ORM\Column(name="bookImage", type="string")
     */
    private $bookImage;

    // get/set id
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

	// get/set name
    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    // get/set name
    public function getAuthor()
    {
        return $this->author;
    }

    public function setAuthor($author)
    {
        $this->author = $author;
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

    // get/set deadline
    public function getDeadline()
    {
        return $this->deadline;
    }

    public function setDeadline($deadline)
    {
        $this->deadline = $deadline;
    }

    // get/set bookImage
    public function getBookImage()
    {
        return $this->bookImage;
    }

    public function setBookImage($bookImage)
    {
        $this->bookImage = $bookImage;
    }

    public function getSalt()
    {
        // The bcrypt algorithm doesn't require a separate salt.
        // You *may* need a real salt if you choose a different encoder.
        return null;
    }

	
	function __construct() {
	}
	
	public static function generateWithData(
		$name, 
		$publishingYear,
		$pageAmount,
		$rating,
		$bookImage
	) {
        $instance = new self();
		$instance->fill(
			$name, 
			$publishingYear,
			$pageAmount,
			$rating,
			$bookImage
		);
        return $instance;
    }
	
	public function fill( 
		$name, 
		$publishingYear,
		$pageAmount,
		$rating,
		$bookImage
	) {
        $this->name = $name;
        $this->author = $name;
		$this->publishingYear = $publishingYear;
		$this->pageAmount = $pageAmount;
		$this->rating = $rating;
		$this->bookImage = $bookImage;
    }
	
    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->name,
            $this->author,
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
            $this->author,
            $this->pageCount,
			$this->description,
			$this->isbn,
			$this->rating,
            // see section on salt below
            // $this->salt
        ) = unserialize($serialized);
    }


}