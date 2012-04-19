<?php

require_once 'autoload.php';

PSU::session_start();

$GLOBALS['BASE_URL'] = '/webapp/festivals/anejf';
$GLOBALS['BASE_DIR'] = __DIR__;

$GLOBALS['ANEJF'] = array(
	'YEAR' => 2012,
);

$GLOBALS['TITLE'] = $GLOBALS['ANEJF']['YEAR'] . ' All New England Jazz Festival';
$GLOBALS['TEMPLATES'] = $GLOBALS['BASE_DIR'] . '/templates';

require 'PSUTemplate.class.php';

require $GLOBALS['BASE_DIR'] . '/includes/AnejfAPI.class.php';
require $GLOBALS['BASE_DIR'] . '/includes/AnejfModels.class.php';
require $GLOBALS['BASE_DIR'] . '/includes/AnejfController.class.php';

if( !isset($_SESSION['anejf']) ) {
	$_SESSION['anejf'] = array();
}

AnejfController::delegate();
