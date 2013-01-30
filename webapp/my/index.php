<?php

require dirname( dirname( __DIR__ ) ) . '/legacy/git-bootstrap.php';
require_once 'autoload.php';

PSU::session_start();

/*******************[Site Constants]*****************/
// Base directory of application
$GLOBALS['BASE_DIR']=dirname(__FILE__);

// Base URL
$GLOBALS['WEBAPP_URL']='https://'.$_SERVER['HTTP_HOST'].'/webapp';

// Base URL
$GLOBALS['BASE_URL']='https://'.$_SERVER['HTTP_HOST'].'/webapp/my';
$GLOBALS['WEBAPP_URL']='https://'.$_SERVER['HTTP_HOST'].'/webapp';
$GLOBALS['OLD_WEBAPP_URL']='https://www' . (PSU::isdev()?'.dev':'') . '.plymouth.edu/webapp';
$GLOBALS['HOST_URL']='https://'.$_SERVER['HTTP_HOST'];

// Local Includes
$GLOBALS['LOCAL_INCLUDES']=$GLOBALS['BASE_DIR'].'/includes';

// Templates
$GLOBALS['TEMPLATES']=$GLOBALS['BASE_DIR'].'/templates';

// Application Title
$GLOBALS['TITLE'] = 'myPlymouth';

$GLOBALS['NO_FANCY_TPL'] = true;

/*******************[End Site Constants]*****************/

if( is_file($GLOBALS['BASE_DIR'] . '/debug.php') ) {
	include($GLOBALS['BASE_DIR'] . '/debug.php');
}

/*******************[Common Includes]**********************/
require_once $GLOBALS['BASE_DIR'].'/includes/MyController.class.php';
require_once $GLOBALS['BASE_DIR'].'/includes/MyController_tab.class.php';
require_once $GLOBALS['BASE_DIR'].'/includes/MyController_channel.class.php';
require_once $GLOBALS['BASE_DIR'].'/includes/MyController_admin.class.php';
require_once $GLOBALS['BASE_DIR'].'/includes/MyPortal.class.php';
require_once 'PSUModels/Model.class.php';
require_once $GLOBALS['BASE_DIR'].'/includes/MyValues.class.php';
require_once $GLOBALS['BASE_DIR'].'/includes/ChannelForm.class.php';
require_once $GLOBALS['BASE_DIR'].'/includes/TabForm.class.php';
require_once 'MyRelationships.class.php';
/*******************[End Common Includes]**********************/

/*******************[Authentication Stuff]*****************/

IDMObject::authN();

// get rid of cas cruft in url
if( isset($_GET['ticket']) ) {
	PSU::redirect( $GLOBALS['BASE_URL'] . '/' );
}

/*******************[End Authentication Stuff]*****************/
$GLOBALS['identifier'] = PSU::is_wpid($_SESSION['username']) ? $_SESSION['username'] : $_SESSION['wp_id'];

// session namespace for portal variables
if( !isset($_SESSION['portal']) ) {
	$_SESSION['portal'] = array();
}

//session alert if logged in as portalord
if( $_SESSION['generic_user_type'] == 'portalord' ) {
	$_SESSION['messages'][] = 'You are currently logged in as PortaLord. <a href="' . $GLOBALS['BASE_URL'] . '/admin/unset-type">Resume your session</a>';
}

if( ! $GLOBALS['identifier'] ) {
	$_SESSION['errors'][] = 'You are logged in as the default user! ANYTHING you do will be done for EVERY DEFAULT LAYOUT!';
}//end if

MyController::delegate();
