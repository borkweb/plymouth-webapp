<?php

require dirname( dirname( __DIR__ ) ) . '/legacy/git-bootstrap.php';
require_once 'autoload.php';
PSU::session_start();

/*******************[Site Constants]*****************/
// Base directory of application
$GLOBALS['BASE_DIR']=dirname(__FILE__);

// Base URL
$GLOBALS['BASE_URL']='https://'.$_SERVER['HTTP_HOST'].'/webapp/themes';

// Base URL
$GLOBALS['WEBAPP_URL']='https://'.$_SERVER['HTTP_HOST'].'/webapp';

// Local Includes
$GLOBALS['LOCAL_INCLUDES']=$GLOBALS['BASE_DIR'].'/includes';

// Templates
$GLOBALS['TEMPLATES']=$GLOBALS['BASE_DIR'].'/templates';

/*******************[End Site Constants]*****************/

/*******************[Common Includes]**********************/
require_once '/web/pscpages/webapp/my/includes/MyPortal.class.php';
/*******************[End Common Includes]**********************/

/*******************[Authentication Stuff]*****************/
IDMObject::authN();
/*******************[End Authentication Stuff]*****************/

/*******************[Database Connections]*****************/
$GLOBALS['MY'] = PSU::db('myplymouth');
/*******************[End Database Connections]*****************/

$theme = new PSUTheme($GLOBALS['MY']);

function isAdmin()
{
	return IDMObject::authZ('permission', 'theme_admin');
}//end isAdmin

$theme_level = array(
	'basic',
	'holiday',
	'event'
);

if(isAdmin())
{
	$theme_level[] = 'admin';
}//end if

if($_SESSION['username']=='mtbatchelder')
{
	$theme_level[] = 'extreme';
	$theme_level[] = 'dev';
}//end if

$valid_themes = $theme->getThemes($theme_level);

$portal = new MyPortal( $_SESSION['wp_id'] );

$GLOBALS['fluid'] = $portal->is_fluid();
$GLOBALS['disabled_chat'] = $portal->is_chat_disabled();
