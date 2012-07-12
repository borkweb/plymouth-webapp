<?php
require dirname( dirname( __DIR__ ) ) . '/legacy/git-bootstrap.php';

require_once 'autoload.php';
PSU::session_start();

require_once 'PSUWordPress.php';

$GLOBALS['BASE_URL'] = $GLOBALS['RELATIVE_URL'] = '/webapp/els';
$GLOBALS['BASE_DIR'] = __DIR__;
$GLOBALS['UPLOAD_DIR'] = PSU::UPLOAD_DIR . $GLOBALS['BASE_URL'];

$GLOBALS['TITLE'] = 'ELS Administration';

$GLOBALS['META_WEBAPP'] = 'webapp_els';

require_once $GLOBALS['BASE_DIR'] . '/includes/ELS.class.php';

IDMObject::authN();

if( !IDMObject::authZ('permission', 'els_admin') ) {
	die('You do not have access to this application.');
}
