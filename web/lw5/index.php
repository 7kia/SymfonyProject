<?php
require_once 'f:\Study\7_Semester\Web-BackEnd\WebProject\vendor\autoload.php';
require_once 'src\Book.php';

use Entity\Book;

$loader = new Twig_Loader_Filesystem('f:\Study\7_Semester\Web-BackEnd\WebProject\app\Resources\views\htmlTemplates');

$twig = new Twig_Environment($loader, array('debug' => true));

$book1 = new Book();

$bookList = array(
	Book::generateWithData("First", 2017, 100, 4, "lw5\\img\\1.png"),
	Book::generateWithData("Firstddddddddddddddddddddddddddd", 2017, 100, 3, "lw5\\img\\1.png"),
	Book::generateWithData("Second", 2000, 1000, 2, "lw5\\img\\2.png")
);

echo $twig->render(
	'template5Lab.html.twig',
	array(
		'bookList' => $bookList
	)
);