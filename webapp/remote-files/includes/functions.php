<?php

/**
 * General functions for Remote Files.
 */

function jsonAndExit($output, $status=0)
{
	header('Content-Type: text/javascript');
	$json = json_encode($output);
	echo $json;
	exit($status);
}

/**
 * Log an action.
 */
function rf_log($data)
{
	$db = new PSUDatabase();

	$data['username'] = $_SESSION['username'];
	$data['pidm'] = $_SESSION['pidm'];

	$db->insert($GLOBALS['RemoteFiles'], 'remote_files_log', $data);
}


// vim:ts=2:sw=2:noet:
?>
