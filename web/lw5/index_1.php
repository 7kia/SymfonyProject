<?php
require_once 'f:\Study\7_Semester\Web-BackEnd\WebProject\vendor\autoload.php';

$loader = new Twig_Loader_String();
$twig = new Twig_Environment($loader);

echo $twig->render('Hello {{ name }}!', array('name' => 'Fabien'));