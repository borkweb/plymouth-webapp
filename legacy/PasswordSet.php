<?php

/**
 * Run on the CLI to set a user's password on some number of systems.
 */

require_once('PasswordManager.class.php');

// initialize a list of target systems to set
$targets = array(
	'oracle' => array(),
	'mysql' => array()
);

$username = null;
$password = null;

foreach($argv as $arg) {
	list($option, $value) = explode('=', $arg);
	$option = substr($option, 2);

	if( $option === 'oracle' || $option === 'mysql' ) {
		$targets[$option] = explode( ',', strtolower($value) );
	} elseif( $option === 'username' || $option === 'password' ) {
		$$option = $value;
	} elseif( $option === 'username-base64' || $option === 'password-base64' ) {
		$option = substr( $option, 0, strpos($option, '-') );
		$$option = base64_decode($value);
	}
}

if( empty($username) ) {
	die( "username may not be left blank\n" );
}

if( empty($password) ) {
	die( "password may not be left blank\n" );
}

//
// Update oracle passwords
//

if( ! PasswordManager::validateOracleUsername($username) ) {
	echo "Oracle: username is invalid, skipping\n";
} elseif( ! PasswordManager::validateOraclePassword($password) ) {
	echo "Oracle: password is invalid, skipping\n";
} else {
	foreach( $targets['oracle'] as $server ) {
		echo "Oracle: Setting password for $username on $server... ";
		if( PasswordManager::setOraclePassword( PSU::db($server), $username, $password ) ) {
			echo "success\n";
		} else {
			echo "failure\n";
		}
	}
}
