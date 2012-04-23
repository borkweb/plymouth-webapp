<?php

$person = new PSUPerson($_REQUEST['pidm']);
$action = $_REQUEST['action'];
$value = $_REQUEST['value'];
if(!$person->pidm) $person = new PSUPerson($_REQUEST['username']);

$logs = $GLOBALS['BannerIDM']->getLogs($person->pidm);
$attribute_log = current(current($logs[$action]));
if($attribute_log['source'] == 'ape')
{
	$log = $GLOBALS['BannerIDM']->getLog($attribute_log['origin_id']);
	if(IDMObject::authZ('admin', $log['attribute']) || IDMObject::authZ('permission', 'ape_attribute_admin'))
		$GLOBALS['BannerIDM']->setAttribute($person->pidm, $action, $value, 'ape', false, 'parent_id='.$attribute_log['parent_id'].'&origin_id='.$attribute_log['origin_id']);
}//end if

// bail here if request was javascript
if( isset($_GET['method']) && $_GET['method'] == 'js' )
{
	header('Content-type: text/javascript');

	$response['pidm'] = $person->pidm;
	$response['type'] = $type;
	$response['attribute'] = $log_attribute;

	die( $value );
}

PSUHTML::redirect($GLOBALS['BASE_URL'] . '/user/' . $person->pidm);
