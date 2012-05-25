<?php

$path = $_GET['path'];
$view = (int)$_GET['view'] == 1 ? true : false;

$log_data = array(
	'action' => $view ? 'view' : 'download',
	'path' => $path
);

if(!$GLOBALS['RFP']->canRead($_SESSION['pidm'], $path))
{
	$log_data['result'] = 'denied';
	rf_log($log_data);

	header('HTTP/1.1 403');
	die('403 Forbidden. You do not have Remote Files access to this directory.');
}

$exit_code = $GLOBALS['SCP']->stream($path, $view);

if($exit_code === 0)
{
	$log_data['result'] = 'success';
	rf_log($log_data);
}
else
{
	$log_data['result'] = 'failure';
	rf_log($log_data);

	if($exit_code === RFUtilException::FILE_NOT_FOUND)
	{
		header('HTTP/1.1 404');
		die('404 Not Found. The supplied path does not exist on the remote server.');
	}
	elseif($exit_code === RFUtilException::UNREADABLE_PATH) // !is_readable()
	{
		header('HTTP/1.1 403');
		die('403 Forbidden. You do not have read access to this file.');
	}
	elseif($exit_code === RFUtilException::BAD_FILE_TYPE) // !is_file()
	{
		header('HTTP/1.1 409');
		die('409 Conflict. Resource is not a regular file.');
	}
	elseif($exit_code === RFUtilException::READFILE_ERROR) // readfile() === false
	{
		header('HTTP/1.1 500');
		die('500 Internal Server Error. Unknown readfile() error on the remote server.');
	}
}

// vim:ts=2:sw=2:noet:
