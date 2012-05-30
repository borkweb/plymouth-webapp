<?php

$path = stripslashes($_GET['path']);
$parent = dirname($path) . '/';
$redirect = (int)$_GET['redirect'];
$confirmed = (int)$_GET['confirmed'];

$file = basename($path);

$json = array(); // result array

$log_data = array(
	'action' => 'unlink',
	'path' => $path
);

if($confirmed !== 1)
{
	$tpl = new RFSmarty();

	$path = $_GET['path'];
	$tpl->assign('path', $path);
	$tpl->assign('title', 'Unlink ' . $path);
	$tpl->assign('parent', $parent);

	$tpl->assign('content', 'unlink');

	$tpl->display('_wrapper.tpl');

	exit();
}

// check for a directory argument
if(substr($path, -1) == '/')
{
	$json['error'] = true;
	$json['message'] = 'Script refuses to delete a directory.';

	// log this action
	$log_data['result'] = 'failure';
	rf_log($log_data);

	if($redirect)
	{
		$_SESSION['errors'][] = $json['message'];
		PSUHTML::redirect($GLOBALS['BASE_URL'] . '/' . $GLOBALS['SSH_HOST'] . ':browse' . $parent);
	}
	else
	{
		jsonAndExit($json);
	}
}

// check if the user is allowed to delete this file
try
{
	if(!$GLOBALS['RFP']->canDelete($_SESSION['pidm'], $parent))
	{
		$json['error'] = true;
		$json['message'] = 'You do not have permission to delete the specified file.';

		// log this action
		$log_data['result'] = 'denied';
		rf_log($log_data);

		if($redirect)
		{
			$_SESSION['errors'][] = $json['message'];
			PSUHTML::redirect($GLOBALS['BASE_URL'] . '/' . $GLOBALS['SSH_HOST'] . ':browse' . $parent);
		}
		else
		{
			jsonAndExit($json);
		}
	}
}
catch(RFException $e)
{
	$json['error'] = true;
	$json['message'] = sprintf('%s (%d)', $e->GetMessage(), $e->GetCode());

	// log this action
	$log_data['result'] = 'failure';
	rf_log($log_data);

	if($redirect)
	{
		$_SESSION['errors'][] = $json['message'];
		PSUHTML::redirect($GLOBALS['BASE_URL'] . '/' . $GLOBALS['SSH_HOST'] . ':browse' . $parent);
	}
	else
	{
		jsonAndExit($json);
	}
}

$result = $GLOBALS['SCP']->unlink($path);

if($result === null)
{
	$log_data['result'] = 'failure';
	$json['error'] = true;
	$json['message'] = 'File did not exist.';
}
elseif($result === false)
{
	$log_data['result'] = 'failure';
	$json['error'] = true;
	$json['message'] = 'File could not be deleted.';
}
else
{
	$log_data['result'] = 'success';
	$json['success'] = true;
	$json['row_id'] = $_GET['row_id'];
}

rf_log($log_data);

if($redirect)
{
	if($json['success'])
	{
		$_SESSION['messages'][] = 'File ' . htmlentities(basename($path)) . ' was deleted.';
	}
	else
	{
		$_SESSION['errors'][] = $json['message'];
	}

	PSUHTML::redirect($GLOBALS['BASE_URL'] . '/' . $GLOBALS['SSH_HOST'] . ':browse' . $parent);
}
else
{
	jsonAndExit($json);
}

// vim:ts=2:sw=2:noet:
