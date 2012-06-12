<?php

/**
 * Academic Excellence.
 *
 * \li http://www.plymouth.edu/webapp/academic-excellence/
 * \li http://go.plymouth.edu/academicexcellence
 *
 * @author Adam Backstrom <ambackstrom@plymouth.edu>
 * @defgroup acadexcel Academic Excellence
 */

/**
 * @file
 * @brief <a href="group__acadexcel.html">Academic Excellence</a>
 */

require_once dirname( dirname( __DIR__ ) ) . '/legacy/git-bootstrap.php';
require_once 'autoload.php';
PSU::session_start();

/*******************[Site Constants]***********************/

$GLOBALS['BASE_DIR'] = __DIR__;

// Base URL
$GLOBALS['BASE_URL'] = 'https://'.$_SERVER['HTTP_HOST'].'/webapp/academic-excellence';

// Local Includes
$GLOBALS['LOCAL_INCLUDES'] = $GLOBALS['BASE_DIR'].'/includes';

/*******************[End Site Constants]*******************/

/*******************[Common Includes]**********************/
require_once('IDMObject.class.php');
require_once('smarty/Smarty.class.php');
require_once('BannerStudent.class.php');
require_once('PSUDatabase.class.php');
/*****************[End Common Includes]********************/

/*******************[Local Includes]**********************/
require_once($GLOBALS['LOCAL_INCLUDES'].'/AETemplate.class.php');
require_once($GLOBALS['LOCAL_INCLUDES'].'/AEStudent.class.php');
require_once($GLOBALS['LOCAL_INCLUDES'].'/utility.php');
/*****************[End Local Includes]********************/

/*******************[Database Connections]*****************/
$GLOBALS['BANNER'] = PSUDatabase::connect('oracle/psc1_psu/fixcase');
/*******************[End Database Connections]*****************/

/*******************[Authentication Stuff]*****************/
IDMObject::authN();

$GLOBALS['BannerStudent'] = new BannerStudent($GLOBALS['BANNER']);
$GLOBALS['DBUTIL'] = new PSUDatabase();
/*******************[End Authentication Stuff]*****************/

/*******************[Application Configuration]*****************/
$GLOBALS['TITLE'] = 'Academic Excellence';
$GLOBALS['TEMPLATES'] = $GLOBALS['BASE_DIR'] . '/templates';

$GLOBALS['TERM'] = AEAPI::option('term')->value;
$GLOBALS['ACCEPTING_DATA'] = AEAPI::option('accepting')->value;
$GLOBALS['IS_SUMMER'] = substr($GLOBALS['TERM'], 4, 2) == '30' ? true : false;
$GLOBALS['DINNER_DATE'] = strtotime( AEAPI::option('dinner')->value );

$GLOBALS['SPECIAL_NEEDS_DEFAULT'] = 'Please use this space to let us know of any special needs you or your guests may have, ie. if wheelchair access is required.';

/*******************[End Application Configuration]*****************/
if(!isset($_SESSION['ae_init']))
{
	// must run after we have a term set
	initializeSession();
}
