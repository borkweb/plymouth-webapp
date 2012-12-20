<?php
require_once 'autoload.php';
PSU::session_start();

$sql = "SELECT class 
          FROM ecommerce_processor 
         WHERE code = '" . preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['processor']) . "'";

if($processor = PSU::db('banner')->GetOne($sql))
{
	require_once('ecommerce/'.$processor.'.class.php');
	try{
		
		$link = new $processor();
		if($url = $link->url($_GET['user']))
		{
			header('Location: '.$url);
			exit;
		}//end if
		else
		{
			exit("The user specified could not be found. Please contact ITS at 603-535-2929.");
		}//end else
	}//end try
	catch(Exception $e)
	{
		exit('Your request could not be processed at this time. Please contact ITS at 603-535-2929.');
	}//end catch
}//end if
