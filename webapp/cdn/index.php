<?php

require dirname( dirname( __DIR__ ) ) . '/legacy/git-bootstrap.php';

require_once 'autoload.php';

PSU::session_start();

$GLOBALS['BASE_DIR'] = dirname(__FILE__);
$GLOBALS['BASE_URL'] = '/webapp/cdn';
$GLOBALS['TEMPLATES'] = $GLOBALS['BASE_DIR'] . '/templates';
$GLOBALS['TITLE'] = 'CDN Manager';

require_once 'includes/CDNController.class.php';
require_once 'includes/CDNAPI.class.php';

IDMObject::authN();

if( !IDMObject::authZ('permission', 'web_developer') ) {
	die('You don\'t have access to ski on the moon.');
}

CDNController::delegate();
