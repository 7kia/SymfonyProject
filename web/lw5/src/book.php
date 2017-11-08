<?php
namespace Entity;

class Book {
	public $name;
	public $publishingYear;
	public $pageAmount;
	public $rating;
	public $cover;
	
	function __construct() {
	}
	
	public static function generateWithData(
		$name, 
		$publishingYear,
		$pageAmount,
		$rating,
		$cover
	) {
        $instance = new self();
		$instance->fill(
			$name, 
			$publishingYear,
			$pageAmount,
			$rating,
			$cover
		);
        return $instance;
    }
	
	public function fill( 
		$name, 
		$publishingYear,
		$pageAmount,
		$rating,
		$cover
	) {
        $this->name = $name;
		$this->publishingYear = $publishingYear;
		$this->pageAmount = $pageAmount;
		$this->rating = $rating;
		$this->cover = $cover;
    }
	
}