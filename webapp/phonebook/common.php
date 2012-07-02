<?php

require_once 'autoload.php';

PSU::session_start();

if( $_REQUEST['authme'] && !$_SESSION['pidm'] ) {
	IDMObject::authN();
}

$GLOBALS['suppress_theme'] = true;

header("Cache-Control: no-cache, must-revalidate");
ini_set("display_errors","0");

/*******************[Site Constants]***********************/

// Base directory of application
$GLOBALS['BASE_DIR'] = dirname(__FILE__);
	
// Base URL
$GLOBALS['BASE_URL'] = 'https://'.$_SERVER['HTTP_HOST'].'/webapp/phonebook';
	
if($_SERVER['HTTPS'] !== "on") {
	header("Location: ".$GLOBALS['BASE_URL'].'/');
	exit;
}
// Local Includes
$GLOBALS['LOCAL_INCLUDES'] = $GLOBALS['BASE_DIR'].'/includes';

$GLOBALS['TEMPORARY_FILES'] = '/web/temp';

// Directory to hold Smarty's compiled templates
$GLOBALS['SMARTY_COMPILE'] = $GLOBALS['TEMPORARY_FILES'] . '/phonebook';
if( ! is_writable($GLOBALS['SMARTY_COMPILE']) ) {
	mkdir($GLOBALS['SMARTY_COMPILE'], 0700);
}
/*******************[End Site Constants]*******************/

$GLOBALS['TITLE'] = 'Public Directory';
	
/*******************[Authorization]*****************/
$can_see_images=false;

if($_SESSION['pidm']) {
	/**** TODO: make this based off of APE *****/
	$GLOBALS['BANNER'] = PSUDatabase::connect('oracle/psc1_psu/fixcase');
	
	if( IDMObject::authZ('department', 'University Police') ) {
		$can_see_images = true;
	}//end if
	/**** END TODO: make this based off of APE *****/
	
	IDMObject::loadAuthZ($_SESSION['pidm']);
	
	if(IDMObject::authZ('permission','view_idcard_images')) {
		$can_see_images=true;
	}//end if
}//end if
/*******************[End Authorization]*****************/

$tpl = new PSUTemplate();
