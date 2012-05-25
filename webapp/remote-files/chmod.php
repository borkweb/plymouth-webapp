<?php

$path = $_GET['path'];

$log_data = array(
	'action' => 'chmod',
	'path' => $path,
	'result' => null
);

$json = array(
	'status' => 'success',
	'filename' => basename($path)
);

try
{
	$log_data['path'] = $path;

	if(!$GLOBALS['RFP']->canWrite($_SESSION['pidm'], $path))
	{
		throw new Exception('You do not have write access to ' . htmlentities($path));
	}

	try
	{
		// hard code perms for now: global rw
		$GLOBALS['SCP']->chmod($path, 0666);
	}
	catch(SCPException $e)
	{
		$log_data['result'] = 'failure';
		throw new Exception('There was an error modifying file permissions: ' . $e->getMessage() . ' (' . $e->getCode() . ')');
	}

	$log_data['result'] = 'success';
}
catch(Exception $e)
{
	// default result
	if($log_data['result'] === null)
	{
		$log_data['result'] = 'denied';
	}
	
	$json['status'] = 'error';
	$json['message'] = $e->getMessage();
}

rf_log($log_data);

PSUTools::jsonAndExit($json);

// vim:ts=2:sw=2:noet:
