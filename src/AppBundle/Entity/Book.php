<?php
// src/AppBundle/Entity/Book.php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\DateTime;

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
     * @var string
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
     * @ORM\Column(name="publishingYear",type="datetime")
     */
    private $publishingYear;

	/**
     * @ORM\Column(name="bookImage", type="string")
     */
    private $bookImage;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return mixed
     */
    public function getPageCount()
    {
        return $this->pageCount;
    }

    /**
     * @param $pageCount
     */
    public function setPageCount($pageCount)
    {
        $this->pageCount = $pageCount;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getIsbn()
    {
        return $this->isbn;
    }

    /**
     * @param $isbn
     */
    public function setIsbn($isbn)
    {
        $this->isbn = $isbn;
    }


    /**
     * @return mixed
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * @param $rating
     */
    public function setRating($rating)
    {
        $this->rating = $rating;
    }

    /**
     * @return mixed
     */
    public function getDeadline()
    {
        return $this->publishingYear;
    }

    /**
     * @param DateTime $publishingYear
     */
    public function setDeadline(DateTime $publishingYear)
    {
        $this->publishingYear = $publishingYear;
    }

    /**
     * @return mixed
     */
    public function getBookImage()
    {
        return $this->bookImage;
    }

    /**
     * @param $bookImage
     */
    public function setBookImage($bookImage)
    {
        $this->bookImage = $bookImage;
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

    // TODO : некорректное считывание
    /**
     * @param string $name
     * @param $publishingYear
     * @param int $pageCount
     * @param int $rating
     * @param string $bookImage
     * @return Book
     */
    public static function generateWithData(
		$name, 
		$publishingYear,
		$pageCount,
		$rating,
		$bookImage
	) {
        $instance = new self();
		$instance->fill(
			$name, 
			$publishingYear,
			$pageCount,
			$rating,
			$bookImage
		);
        return $instance;
    }

    /**
     * @param $name
     * @param $publishingYear
     * @param $pageCount
     * @param $rating
     * @param $bookImage
     */
    public function fill(
		$name, 
		$publishingYear,
		$pageCount,
		$rating,
		$bookImage
	) {
        $this->name = $name;
        $this->author = $name;
		$this->publishingYear = $publishingYear;
		$this->pageCount = $pageCount;
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

    /** @see \Serializable::unserialize()
     * @param string $serialized
     */
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