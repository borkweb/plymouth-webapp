<?php
require_once 'autoload.php';
require_once('ecommerce/ETransFlexCash.class.php');

$link = new ETransFlexCash();
if($url = $link->url($_GET['user'])) {
	header('Location: '.$url);
	die;
} else {
	die("The user specified could not be found. Please contact ITS at x2929.");
}//end else
