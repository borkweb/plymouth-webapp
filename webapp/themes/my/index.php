<?php

session_start();

header("Cache-Control: no-cache, must-revalidate");

require_once 'PSUDatabase.class.php';
require_once 'includes/holidays.php';
require_once 'PSUTheme.class.php';
require_once 'IDMObject.class.php';
require_once 'includes/crews.php';

$my = PSUDatabase::connect('mysql/myplymouth');
if($_SESSION['pidm']) IDMObject::loadAuthZ($_SESSION['pidm']);

/************[ BEGIN: Main Vars ]***********/
$theme = new PSUTheme($my, dirname(__FILE__));
$theme->loadUserTheme($_SESSION['wp_id']);

$day = date('j');
$month = date('n');
$month_name = strtolower(date('F'));
$year = date('Y');
/************[ END: Main Vars ]***********/

/****************************************
 * GENERAL SETTINGS
 ****************************************/
@include_once('general.php');

/****************************************
 * MONTH SETTINGS
 ****************************************/
@include_once('months/'.$month_name.'.php');

/****************************************
 * EXTREME SETTINGS
 ****************************************/
@include_once('extreme.php');

/****************************************
 * set header and output
 ****************************************/
if( $_GET['is_event'] ) {
	if( ! $theme->event ) die;

	$keys = array_keys( $theme->theme );
	die( array_pop( $keys ) );

} else {
	if( $_GET['js'] ) {
		$theme->out_js();
	} else {
		$theme->out();
	}//end else
}//end else
