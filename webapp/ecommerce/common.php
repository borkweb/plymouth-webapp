<?php
require dirname( dirname( __DIR__ ) ) . '/legacy/git-bootstrap.php';
require_once 'autoload.php';

PSU::session_start();

/*******************[Site Constants]*****************/
// Base directory of application
$GLOBALS['BASE_DIR']=dirname(__FILE__);

// Base URL
$GLOBALS['BASE_URL']='https://'.$_SERVER['HTTP_HOST'].'/webapp/ecommerce';

PSUTools::https();

// if logout is passed through the GET, log them out
if(isset($_GET['logout']))
{
	$_SESSION = array();
	session_destroy();
	header('Location: '.$GLOBALS['BASE_URL']);
	exit;
}

// Local Includes
$GLOBALS['LOCAL_INCLUDES']=$GLOBALS['BASE_DIR'].'/includes';

// Templates
$GLOBALS['TEMPLATES'] = $GLOBALS['BASE_DIR'].'/templates';
$GLOBALS['CSS'] = 'https://'.$_SERVER['HTTP_HOST'].'/app/core/css/style.css';

// Icons
$GLOBALS['ICONS']='https://'.$_SERVER['HTTP_HOST'].'/images/icons';

$GLOBALS['NEW_STYLE'] = true;

// Javascript
$GLOBALS['JS']=$GLOBALS['BASE_URL'].'/js';
/*******************[End Site Constants]*****************/

/*******************[Common Includes]**********************/
require_once('PSUTemplate.class.php');
require_once('PSUDatabase.class.php');    //database functions
require_once('IDMObject.class.php');    //idm class
require_once('BannerGeneral.class.php');    //general class
require_once('BannerStudent.class.php');    //student class
require_once('PSUECommerce.class.php');
require_once('PSUECommerceInterface.class.php');
require_once('PSUECommerceTransaction.class.php');
require_once('ecommerce/ETrans.class.php');
require_once('channel.class.php');
/*******************[End Common Includes]**********************/

/*******************[Local Includes]**********************/
require_once($GLOBALS['LOCAL_INCLUDES'].'/ECommerceSmarty.class.php');
/*******************[End Local Includes]**********************/

/*******************[Database Connections]*****************/
$which = 'test';

if($_GET['which'] == 'psc1')
{
	$which = 'psc1';
}//end if
elseif(preg_match('/https?\:\/\/www\./',$GLOBALS['BASE_URL']) && $_GET['which'] != 'test')
{
	$which = 'psc1';
}//end else

$GLOBALS['BANNER'] = PSUDatabase::connect('oracle/'.$which.'_psu/fixcase');
/*******************[End Database Connections]*****************/

$GLOBALS['BannerStudent']=new BannerStudent($GLOBALS['BANNER']);
$GLOBALS['BannerIDM']=new IDMObject();

if(strchr($_SERVER['SCRIPT_NAME'],'/admin/'))
{
	$_SESSION['username'] = IDMObject::authN();
	
	if( !IDMObject::authZ('permission','mis') )
	{
		exit("You do not have sufficient permissions to view this page.");
	}//end if
}//end if
