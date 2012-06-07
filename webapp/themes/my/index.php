<?php

require dirname( dirname( dirname( __DIR__ ) ) ) . '/legacy/git-bootstrap.php';
require_once 'autoload.php';

PSU::session_start();

header("Cache-Control: no-cache, must-revalidate");

require_once __DIR__ . '/includes/holidays.php';
require_once __DIR__ . '/includes/crews.php';

/************[ BEGIN: Main Vars ]***********/
$theme = new PSUTheme( PSU::db('myplymouth'), dirname(__FILE__) );
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
