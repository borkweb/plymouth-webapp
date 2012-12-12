<?php
/**
 * common.php
 *
 * PHP Repair Shop Workorder System: Configuration and Authentication file
 *
 * @version		1.0
 * @author		Alan Baker <a_bake@plymouth.edu>
 * @copyright 2008, Plymouth State University, ITS
 */ 
require dirname( dirname( dirname( __DIR__ ) ) ) . '/legacy/git-bootstrap.php';

require_once 'autoload.php';
PSU::session_start();

$config = \PSU\Config\Factory::get_config();

/*******************[Site Constants]*****************/
// Base directory of application
$GLOBALS['BASE_DIR']=dirname(__FILE__);

// Base URL: whatever the URL is 
$GLOBALS['BASE_URL']= $config->get('systems-workorder', 'base_url');

// Local Includes
$GLOBALS['INCLUDES']=$GLOBALS['BASE_DIR'].'/includes';

// Templates
$GLOBALS['TEMPLATES']=$GLOBALS['BASE_DIR'].'/templates';

/*******************[End Site Constants]*****************/
$GLOBALS['NUM_ITEMS']=7;
if(PSU::isDev()) {
	$GLOBALS['ORDER_USERNAME'] = "a_bake";
} else {
	$GLOBALS['ORDER_USERNAME'] = "tom";
}//end else

$GLOBALS['PARTS_MARKUP'] = $config->get( 'systems-workorder', 'parts_markup' );
$GLOBALS['PARTS_MARKUP_MAX'] = $config->get( 'systems-workorder', 'parts_markup_max' );
$GLOBALS['SHOP_EMAIL']="computer-service@plymouth.edu";
$GLOBALS['IP']=explode(".",$_SERVER['REMOTE_ADDR']);

$sql = "SELECT ip FROM shop_authorized_ips";
$GLOBALS['HD_IPS'] = \PSU::db('systems')->GetCol( $sql );

$GLOBALS['IS_HD'] = in_array($_SERVER['REMOTE_ADDR'],$GLOBALS['HD_IPS']);

/*******************[Includes]**********************/
require_once('xtemplate.php');  //XTemplates
require_once("adldap/adLDAP.php"); // AD integration
require_once("functions.php"); // local functions
/*******************[End Includes]**********************/
if(!isset($GLOBALS['AD'])) {
	$conf = PSUDatabase::connect('ldap/password','return');
	$conf['password']= PSUSecurity::password_decode($conf['password']);
	$options['account_suffix']="@plymouth.edu";
	$options['base_dn']=$conf['dn'];
	$options['domain_controllers']=array($conf['hostname'],$conf['hostname2']);
	$options['ad_username']=$conf['username'];
	$options['ad_password']=$conf['password'];
	$options['real_primarygroup']=true;
	$options['use_ssl']=true;
	$options['recursive_groups']=true;

	$GLOBALS['AD'] = new adLDAP($options);
}

$GLOBALS['SYSTEMS_DB'] = PSU::db('systems');

// do whatever you do to authenticate the user....set the 
// username into a session variable.
// at PSU we use phpCAS:
if( $GLOBALS['IS_HD'] || $GLOBALS['IP'][2]==112 || $GLOBALS['IP'][2]==114 || $GLOBALS['IP'][2]==33 || $GLOBALS['IP'][2]==32 || $GLOBALS['IP'][2]==115) {
	// make sure we're either on an acceptable helpdesk computer, or on the 112 or 114 networks, otherwise deny access 
	IDMObject::authN();
	if( ! (
       IDMObject::authZ('banner', 'student_active')
    	|| IDMObject::authZ('banner', 'employee')
    	|| IDMObject::authZ('banner', 'alumni')
    	|| IDMObject::authZ('banner', 'alumni_campus')
    	|| IDMObject::authZ('banner', 'alumni_emeritus')
    	|| IDMObject::authZ('banner', 'psu_friend')
	)) {
		echo "You must be a current student, employee, alumni, or retiree to use this service";
		exit;
	}
} else {
	echo "You do not have access to use this service from this location";
	exit;
}

/*******************[End Authentication]********************/

/*******************[Authorization]********************/

/*$auth_query = "select id from authorized_users where uid='".$_SESSION['username']."'";
$result = $GLOBALS['SYSTEMS_DB']->Execute($auth_query);
if($result->RecordCount()<1)
{
	echo 'You do not have access to use this service';
	exit;
}//end if */

/*******************[End Authorization]********************/
