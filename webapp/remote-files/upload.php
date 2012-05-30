<?php

$rf_file = $_FILES['rf_file'];
$path = $_GET['path'];
$fullpath = $_POST['fullpath'];
$swfupload = (bool)$_REQUEST['swfupload'];

file_put_contents('/tmp/logginit', $_SERVER['QUERY_STRING'] . "\n", FILE_APPEND);

$log_data = array(
	'action' => 'upload',
	'path' => $path
);

$json = array(
	'status' => 'success'
);

try
{
	if(!is_uploaded_file($rf_file['tmp_name']))
	{
		// if the file size is greater than $_POST['MAX_FILE_SIZE'], $name will have a value.
		// if it exceeds the ini value UPLOAD_MAX_FILESIZE, $name will be empty.

		$name = htmlentities($rf_file['name']);
		$name = empty($name) ? $name : "of $name";

		if(!empty($name))
		{
			$log_data['path'] = $path . '/' . $name;
		}

		$log_data['result'] = 'failure';

		throw new Exception('Upload ' . $name . ' failed. File too large?');
	}

	$log_data['path'] = $path . $rf_file['name'];

	if(!$GLOBALS['RFP']->canWrite($_SESSION['pidm'], $path))
	{
		$log_data['result'] = 'denied';

		throw new Exception('You do not have write access to ' . htmlentities($path . $rf_file['name']));
	}

	try
	{
		$GLOBALS['SCP']->put($rf_file['tmp_name'], $path . $rf_file['name']);
	}
	catch(SCPException $e)
	{
		$log_data['result'] = 'failure';
		throw new Exception('There was an error uploading your file: ' . $e->getMessage() . ' (' . $e->getCode() . ')');
	}

	$log_data['result'] = 'success';

	$msg = 'File "' . htmlentities($rf_file['name']) . '" was uploaded successfully.';

	if($swfupload)
	{
		$json['message'] = $rf_file['name'];
		$json['html'] = $msg;
	}
	else
	{
		$_SESSION['messages'][] = $msg;
	}
}
catch(Exception $e)
{
	if($swfupload)
	{
		$json['status'] = 'error';
		$json['message'] = $e->getMessage();
	}
	else
	{
		$_SESSION['errors'][] = $e->getMessage();
	}
}

rf_log($log_data);

if($swfupload)
{
	PSUTools::jsonAndExit($json);
}
{
	PSUHTML::redirect($GLOBALS['BASE_URL'] . '/' . $GLOBALS['SSH_HOST'] . ':browse' . $fullpath);
}

// vim:ts=2:sw=2:noet:
