<?php

require dirname( dirname( __DIR__ ) ) . '/legacy/git-bootstrap.php';

require_once 'autoload.php';
PSU::session_start();

$config = new PSU\Config;
$config->load();

define( 'PSU_API_APPID', $config->get('ape', 'api_appid') );
define( 'PSU_API_APPKEY', $config->get('ape', 'api_key') );

/*******************[Site Constants]*****************/
// Base directory of application
$GLOBALS['BASE_DIR']=dirname(__FILE__);

// Base URL
PSU::get()->base_url = $GLOBALS['BASE_URL'] = $config->get('ape', 'base_url');

if( file_exists('debug.php') ) {
	// 1. debug.php should override $GLOBALS['BASE_URL'] and PSU::get()->base_url
	// 2. also modify auto_prepend_file and RewriteBase in .htaccess, but make sure you don't commit those changes
	include 'debug.php';
}

if(isset($_GET['go']))
{
	PSU::redirect( $config->get('ape', 'base_url') . '/user/'.$_GET['go'] );
}

// Local Includes
$GLOBALS['LOCAL_INCLUDES']=$GLOBALS['BASE_DIR'].'/includes';

// Temp
$GLOBALS['TMP'] = '/web/temp';

// Templates
$GLOBALS['TEMPLATES']=$GLOBALS['BASE_DIR'].'/templates';

// Icons
$GLOBALS['ICONS']= $config->get('app_url').'/core/images/my/icons';

// Javascript
$GLOBALS['COMMON_JS'] = $config->get('app_url').'/core/js';

// Javascript
$GLOBALS['JS'] = $config->get('ape', 'base_url').'/js';

$GLOBALS['IDCARD_URL'] = $config->get('idcard', 'base_url');

// IDM "Source" name
$GLOBALS['IDM_SOURCE'] = 'ape';
$GLOBALS['TITLE'] = 'Analysis and Provisioning Engine | APE';
/*******************[End Site Constants]*****************/

/*******************[Common Includes]**********************/
require_once 'PSUWordPress.php';
require_once 'portal.class.php';    //portal functions
require_once 'workflow.class.php';
require_once 'adldap/adLDAP.php';
require_once 'zimbraAdmin.class.php';
/*******************[End Common Includes]**********************/

/*******************[Authentication Stuff]*****************/
IDMObject::authN();
/*******************[End Authentication Stuff]*****************/

/*******************[Local Includes]**********************/
require_once $GLOBALS['LOCAL_INCLUDES'].'/functions.php';    //application functions
require_once $GLOBALS['LOCAL_INCLUDES'].'/APE.class.php';    //APE class
require_once $GLOBALS['LOCAL_INCLUDES'].'/APEAuthZ.class.php';    //APE class
require_once $GLOBALS['LOCAL_INCLUDES'].'/APESmarty.class.php';    //APE class
/*******************[End Local Includes]**********************/

/*******************[Database Connections]*****************/
$GLOBALS['BANNER'] = PSUDatabase::connect('oracle/psc1_psu/fixcase');
$GLOBALS['ITS4'] = PSUDatabase::connect('mysql/data_mart-admin');
$GLOBALS['CALLLOG'] = PSUDatabase::connect('mysql/calllog');
$GLOBALS['USER_DB'] = PSUDatabase::connect('mysql/user_info-admin');
//$GLOBALS['EPO'] = PSUDatabase::connect('mssql/epo_mercury');
$GLOBALS['ASTER'] = PSUDatabase::connect('mysql/aster-misuser');
$GLOBALS['MYPLYMOUTH'] = PSUDatabase::connect('mysql/myplymouth');
/*******************[End Database Connections]*****************/

// which portal we are working in, for now there is only one, and we hardcode it!
$GLOBALS['Workflow'] = new Workflow();

$GLOBALS['BannerGeneral'] = new BannerGeneral($GLOBALS['BANNER']);
$GLOBALS['BannerStudent'] = new BannerStudent($GLOBALS['BANNER']);

$GLOBALS['PWMAN'] = new PasswordManager($GLOBALS['MYPLYMOUTH'], $_ = false, $GLOBALS['USER_DB']);

$GLOBALS['LOG'] = new PSULog('ape',$_SESSION['username']); 

$GLOBALS['ZimbraAdmin'] = new zimbraAdmin();

/*******************[Authorization Stuff]*****************/
$GLOBALS['portal'] = new Portal();
$GLOBALS['user_roles'] = $GLOBALS['portal']->getRoles($_SESSION['username']);

$path_parts = pathinfo( $_SERVER['SCRIPT_FILENAME'] );

if( $path_parts['basename'] != 'portalv5.php' ) {
	if(!IDMObject::authZ('role', 'staff') && !IDMObject::authZ('role','ape') && !APEAuthZ::infodesk() && !APEAuthZ::family() && !APEAuthZ::student() && !APEAuthZ::advancement() && !$_SESSION['impersonate'])
	{
		echo 'You ('.$_SESSION['username'].') do not have access to use this application.  If '.$_SESSION['username'].' is not your username, please log in to <a href="http://go.plymouth.edu/logout">myPlymouth</a> and try again.';
		exit;
	}//end if
}//end if
/*******************[End Authorization Stuff]*****************/

if( $_GET['mobile'] ) {
	$_SESSION['psu_mobile'] = true;	
} elseif( $_GET['nomobile'] ) {
	$_SESSION['psu_mobile'] = false;	
}//end else

$GLOBALS['myuser'] = new PSUPerson($_SESSION['username']);

// first-time init. of error and message vars
if(!isset($_SESSION['errors']))
{
	$_SESSION['errors'] = $_SESSION['messages'] = array();
}

$GLOBALS['ape'] = new APE($GLOBALS['BANNER'],$GLOBALS['BannerIDM'],$GLOBALS['BannerGeneral'],$GLOBALS['MYPLYMOUTH']);
