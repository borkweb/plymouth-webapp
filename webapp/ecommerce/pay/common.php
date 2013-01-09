<?php
include_once('../common.php');

$GLOBALS['p_administrators']=array(
	'nrporter',
	'lrwilcox',
);
/*******************[End Site Constants]*****************/

/*******************[Authentication Stuff]*****************/
if(!$_GET['hash'])
{
	$_SESSION['username'] = IDMObject::authN();
	
	if(!in_array($_SESSION['username'],$GLOBALS['p_administrators']))
	{
		echo 'You do not have access to use this application';
		exit;
	}//end if
}//end if
/*******************[End Authentication Stuff]*****************/

?>
