<?php

require_once 'autoload.php';

PSU::session_start();

$GLOBALS['BASE_URL'] = '/webapp/festivals/woodwind';
$GLOBALS['BASE_DIR'] = dirname(__FILE__);

$GLOBALS['TITLE'] = 'PSU Woodwind Day';
$GLOBALS['TEMPLATES'] = $GLOBALS['BASE_DIR'] . '/templates';

require $GLOBALS['BASE_DIR'] . '/includes/WoodwindAPI.class.php';
require $GLOBALS['BASE_DIR'] . '/includes/WoodwindModels.class.php';
require $GLOBALS['BASE_DIR'] . '/includes/WoodwindController.class.php';

if( !isset($_SESSION['woodwind-day']) ) {
	$_SESSION['woodwind-day'] = array();
}

WoodwindController::delegate();
