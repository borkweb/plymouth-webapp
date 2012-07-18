<?php

/**
 * Common Application administration. An interfacing for loading data into Common App, and
 * viewing reports of related information.
 */

require_once 'autoload.php';
PSU::session_start();

/*******************[Site Constants]*****************/
$GLOBALS['BASE_DIR'] = dirname(__FILE__);

$GLOBALS['HTTP_HOST'] = 'https://'.$_SERVER['HTTP_HOST'];
PSU::get()->base_url = $GLOBALS['BASE_URL'] = $GLOBALS['HTTP_HOST'] . '/webapp/commonapp';

// Common Includes

require_once $GLOBALS['BASE_DIR'] . '/includes/CAController.class.php';
require_once $GLOBALS['BASE_DIR'] . '/includes/CommonAppCountries.class.php';

$GLOBALS['COMMON_INCLUDES']='/web/pscpages/includes';

$GLOBALS['LOCAL_INCLUDES'] = $GLOBALS['BASE_DIR'].'/includes';

$GLOBALS['TEMPLATES']=$GLOBALS['BASE_DIR'].'/templates';

// Javascript
$GLOBALS['JS'] = $GLOBALS['BASE_URL'].'/js';

$GLOBALS['TMP'] = '/web/temp/commonapp';

// template page title
$GLOBALS['TITLE'] = 'Common Application';

/*******************[End Site Constants]*****************/

/*******************[Authentication Stuff]*****************/
IDMObject::authN();
/*******************[End Authentication Stuff]*****************/

/*******************[Database Constants]*****************/
$GLOBALS['BANNER'] = PSU::db('banner');
/*******************[End Database Constants]*****************/

/*******************[Authorization Stuff]*****************/
if(!IDMObject::authZ('role','commonapp'))
{
	echo 'You ('.$_SESSION['username'].') do not have access to use this application.  If '.$_SESSION['username'].' is not your username, please log in to <a href="http://my.plymouth.edu">myPlymouth</a> and try again.';
	exit;
}//end if
/*******************[End Authorization Stuff]*****************/

function adodb_firephp( $msg, $newline ) {
	PSU::get('firephp')->log( html_entity_decode( strip_tags($msg) ) );
}
//define('ADODB_OUTP', 'adodb_firephp');
