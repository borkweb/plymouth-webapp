<?php
require dirname( dirname( __DIR__ ) ) . '/legacy/git-bootstrap.php';
require_once 'autoload.php';
PSU::session_start();

/*******************[Site Constants]*****************/
// Base directory of application
$GLOBALS['BASE_DIR']=dirname(__FILE__);

// Base URL
$GLOBALS['BASE_URL']='https://'.$_SERVER['HTTP_HOST'].'/webapp/raintix';

// Local Includes
$GLOBALS['LOCAL_INCLUDES']=$GLOBALS['BASE_DIR'].'/includes';

// Templates
$GLOBALS['TEMPLATES']=$GLOBALS['BASE_DIR'].'/templates';

$GLOBALS['TITLE'] = 'Inclement Weather Tickets';

/*******************[End Site Constants]*****************/

/*******************[Authentication Stuff]*****************/
if(!strstr($_SERVER['SCRIPT_FILENAME'], 'search.html'))
$username = IDMObject::authN();
/*******************[End Authentication Stuff]*****************/

$valid_users=array('mtbatchelder', 'blyndes');
