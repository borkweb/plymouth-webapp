<?php
include_once('../common.php');

setlocale(LC_MONETARY, 'en_US');

$GLOBALS['p_administrators']=array(
	'ambackstrom',
	'zbtirrell',
	'mtbatchelder',
	'nrporter'
);
/*******************[End Site Constants]*****************/

/*******************[Authentication Stuff]*****************/
if(!$_GET['hash'])
{
	$_SESSION['username']=IDMObject::authN();
	
	
	if(!in_array($_SESSION['username'],$GLOBALS['p_administrators']))
	{
		echo 'You do not have access to use this application';
		exit;
	}//end if
}//end if
/*******************[End Authentication Stuff]*****************/

?>
