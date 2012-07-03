<?php
require_once 'autoload.php';

PSU::session_start();

$GLOBALS['BASE_DIR'] = dirname(__FILE__);
$GLOBALS['BASE_URL'] = '/webapp/faculty';
$GLOBALS['TEMPLATES'] = $GLOBALS['BASE_DIR'] . '/templates';
$GLOBALS['TITLE'] = 'Faculty Database';

require_once 'faculty.class.php';

IDMObject::authN();

if(!IDMObject::authZ('permission', 'faculty_admin')) {
	exit('You do not have access to this service.');
}
