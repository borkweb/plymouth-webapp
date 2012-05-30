<?php

$from = $_GET['from'];
$to = $_GET['to'];
$rf_row = (int)$_GET['rf_row'];

$path = $_GET['path'];
if(substr($path, -1) != '/')
{
	$path .= '/';
}

$log_data = array(
	'action' => 'upload',
	'path' => $path,
	'result' => null
);

$json = array(
	'status' => 'success',
	'rf_row' => $rf_row
);

try
{
	$log_data['path'] = $path;

	if(strpos($from, '/') !== false)
	{
		throw new Exception('Old file name has illegal characters');
	}

	if(strpos($to, '/') !== false)
	{
		throw new Exception('New file name has illegal characters');
	}

	if($to === '')
	{
		throw new Exception('New name cannot be blank');
	}

	if($from === '')
	{
		throw new Exception('Old name cannot be blank');
	}

	if(strlen($to) > $GLOBALS['MAX_RENAME_LENGTH'])
	{
		throw new Exception("New name cannot be longer than {$GLOBALS['MAX_RENAME_LENGTH']} characters");
	}

	if($to === $from)
	{
		throw new Exception('Old name cannot be the same as new name');
	}

	if(!$GLOBALS['RFP']->canWrite($_SESSION['pidm'], $path))
	{
		throw new Exception('You do not have write access to ' . htmlentities($path));
	}

	try
	{
		$GLOBALS['SCP']->rename($path, $from, $to);
	}
	catch(SCPException $e)
	{
		$log_data['result'] = 'failure';
		throw new Exception('There was an error renaming your file: ' . $e->getMessage() . ' (' . $e->getCode() . ')');
	}

	$log_data['result'] = 'success';

	$json['name'] = $to;
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
