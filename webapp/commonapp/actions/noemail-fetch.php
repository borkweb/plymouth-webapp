<?php

// role: commonapp required via common.php

$pidms = $_POST['pidms'];

if( empty($pidms) ) {
	die();
}

$pidms = explode(',', $pidms);
$pidms = array_slice( $pidms, 0, 10 );

$users = array();

foreach( $pidms as $pidm ) {
	$pidm = (int)$pidm;
	$user = ugApplicants::getApplicant( $pidm );

	$users[$pidm] = $user;
}

die( json_encode( $users ) );
