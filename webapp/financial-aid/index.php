<?php

require dirname( dirname( __DIR__ ) ) . '/legacy/git-bootstrap.php';

require_once 'autoload.php';
PSU::session_start(); // force ssl + start a session

$GLOBALS['BASE_URL'] = '/webapp/financial-aid';
$GLOBALS['BASE_DIR'] = dirname(__FILE__);

$GLOBALS['TITLE'] = 'Financial Aid';
$GLOBALS['TEMPLATES'] = $GLOBALS['BASE_DIR'] . '/templates';

require_once $GLOBALS['BASE_DIR'] . '/includes/FinaidController.class.php';
require_once $GLOBALS['BASE_DIR'] . '/includes/FinaidAPI.class.php';
require_once $GLOBALS['BASE_DIR'] . '/includes/FinaidParams.class.php';
require_once $GLOBALS['BASE_DIR'] . '/includes/FinaidTesting.php';

if( file_exists( $GLOBALS['BASE_DIR'] . '/debug.php' ) ) {
	require_once $GLOBALS['BASE_DIR'] . '/debug.php';
}

IDMObject::authN();

FinaidController::delegate();
