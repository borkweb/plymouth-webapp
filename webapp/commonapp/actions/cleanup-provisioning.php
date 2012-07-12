<?php

global $unsent;

$missing = ugApplicants::appsMissingEmail();
$unsent = ugApplicants::getPopulation('unsent_myp_invite');

function _just_pidms( $row ) {
	return $row['pidm'];
}

function _remove_unsent( $row ) {
	global $unsent;
	return in_array($row['pidm'], $GLOBALS['unsent']) == false;
}

// $unsent should be an array of pidms
$unsent = array_map( '_just_pidms', $unsent );

// trim users from $missing who no longer need an invite
$missing = array_filter( $missing, '_remove_unsent' );

// remove everyone left in $missing
foreach( $missing as $row ) {
	ugApplicants::app_missing_email_resolved($row['pidm']);
}

PSU::redirect( $GLOBALS['BASE_URL'] . '/provisioning.html' );
