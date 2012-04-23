<?php

$response = array();
$response['messages'] = array();
$response['errors'] = array();

$is_ajax = isset($_REQUEST['ajax']) ? true : false;

$pidm = (int)$_GET['pidm'];

// check for invalid pidm
if(!$GLOBALS['BannerGeneral']->isValidPidm($pidm))
{
	$response['errors'] = sprintf('PIDM "%d" is invalid.', $pidm);
}

// where should redirects go?
$redirect_url = $GLOBALS['BASE_URL'] . '/user/' . $pidm;

if($_GET['synchronize_ldi'] == 1)
{
	// TODO: replace LDI sync code with REST API call to POST user/sync/ldi/[:id]/[:source] Where ":source" is the username of the synchronizer
	$person = new PSUPerson( $pidm );

	if( $person->sync_ldi( $_SESSION['username'] ?: 'ape' ) ) {
		$response['messages'][] = 'Synchronization has been queued.';
	} else {
		$response['errors'][] = 'LDISync() failed, contact MIS.';
	}

	action_cleanup($redirect_url, $response, $is_ajax);
}
elseif($_GET['synchronize_ad'])
{
	$GLOBALS['LOG']->write('Active Directory Sync', PSU::get('idmobject')->getIdentifier($pidm,'pid','username'));
	require_once('PSUadLDAP.class.php');
	$GLOBALS['AD'] = new PSUadLDAP();
	$GLOBALS['AD']->syncGroups($pidm);
	$response['messages'][] = 'Synchronization has been queued.';
	action_cleanup($redirect_url, $response, $is_ajax);
}//end elseif
