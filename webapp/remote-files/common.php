<?php

require_once __DIR__ . '/../../legacy/git-bootstrap.php';
require_once 'autoload.php';

PSU::session_start();

$GLOBALS['BASE_DIR'] = dirname(__FILE__);
$GLOBALS['BASE_URL'] = 'https://' . $_SERVER['HTTP_HOST'] . '/webapp/remote-files';

$GLOBALS['COMMON_JS'] = 'https://www.plymouth.edu/includes/js'; // DEBUG: change to better path once we go live?

$GLOBALS['LOCAL_INCLUDES'] = $GLOBALS['BASE_DIR'].'/includes';

$GLOBALS['MAX_RENAME_LENGTH'] = 100;
$GLOBALS['DEFAULT_HOST'] = 'titan';

// ************ Internal Includes ************* //
require_once($GLOBALS['LOCAL_INCLUDES'].'/SCPlib.class.php');
require_once($GLOBALS['LOCAL_INCLUDES'].'/RFPermissions.class.php');
require_once($GLOBALS['LOCAL_INCLUDES'].'/RFSmarty.class.php');
require_once($GLOBALS['LOCAL_INCLUDES'].'/functions.php');
require_once($GLOBALS['BASE_DIR'].'/rfutil/rfutil.inc.php');

IDMObject::authN();

$GLOBALS['BANNER'] = PSUDatabase::connect('oracle/psc1_psu/fixcase');
$GLOBALS['RemoteFiles'] = PSUDatabase::connect('mysql/myplymouth');
$GLOBALS['BannerIDM'] = new IDMObject($GLOBALS['BANNER']);
$GLOBALS['PHPSESSID'] = $_COOKIE['PHPSESSID'];

// make sure our session variables are set up
if( ! isset( $_SESSION['javascript'] ) )
{
	$_SESSION['javascript'] = true;
}

if(isset($_GET['go']))
{
	$go = $_GET['go'];

	if(empty($go))
	{
		$go = $GLOBALS['DEFAULT_HOST'];
	}
	elseif(!ctype_lower($go))
	{
		$go = $GLOBALS['DEFAULT_HOST'];
		$_SESSION['errors'][] = 'An invalid server name was provided via go.plymouth.edu.';
	}
	PSUHTML::redirect($GLOBALS['BASE_URL'] . "/" . $go . ":");
}


$GLOBALS['SSH_HOST'] = isset($_REQUEST['server']) ? $_REQUEST['server'] : $GLOBALS['DEFAULT_HOST'];
$GLOBALS['SCP'] = new SCPlib($GLOBALS['SSH_HOST']);
$GLOBALS['RFP'] = new RFPermissions($GLOBALS['BannerIDM'], $GLOBALS['RemoteFiles'], $GLOBALS['SSH_HOST']);

// vim:ts=2:sw=2:noet:
