<?php

require_once 'autoload.php';
PSU::session_start();

$GLOBALS['BASE_URL'] = '/webapp/music-career-day';
$GLOBALS['BASE_DIR'] = dirname(__FILE__);

$GLOBALS['TITLE'] = '2011 Music Technology and Education Career Day';
$GLOBALS['TEMPLATES'] = $GLOBALS['BASE_DIR'] . '/templates';

require $GLOBALS['BASE_DIR'] . '/includes/MTEAPI.class.php';
require $GLOBALS['BASE_DIR'] . '/includes/MTEModels.class.php';
require $GLOBALS['BASE_DIR'] . '/includes/MTEController.class.php';

if( !isset($_SESSION['mtecd']) ) {
	$_SESSION['mtecd'] = array();
}

MTEController::delegate();
