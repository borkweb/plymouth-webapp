<?php

$tpl = new RFSmarty();

$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$order = isset($_GET['order']) ? $_GET['order'] : 'asc';

$dirpath = $path = $_GET['path'];

if($GLOBALS['RFP']->type($path) == 'dir' && substr($path, -1) !== '/')
{
	PSUHTML::redirect($GLOBALS['BASE_URL'] . '/' . $GLOBALS['SSH_HOST'] . ':browse' . $path . '/');
}

if(substr($path, -1) != '/')
{
	$dirpath = dirname($path) . '/';
}

$filter = ($dirpath == $path) ? '' : substr($path, strlen($dirpath));

try
{
	if($GLOBALS['RFP']->canWrite($_SESSION['pidm'], $path))
	{
		$tpl->assign('can_write', true);
	}
}
catch(RFException $e) {}

// dummy class for our exceptions
class ListingException extends Exception{}

$response = array('exit_code' => 0);

$log_data = array(
	'action' => 'browse',
	'path' => $path,
	'result' => null
);

try
{
	if(!$GLOBALS['RFP']->canRead($_SESSION['pidm'], $path))
	{
		$log_data['result'] = 'denied';
		throw new ListingException('You are not allowed to view this directory.', 1);
	}

	$parent = dirname($dirpath) . '/';
	try
	{
		if($GLOBALS['RFP']->canRead($_SESSION['pidm'], $parent))
		{
			$tpl->assign('parent', $parent);
		}
	}
	catch(RFException $e){}

	$listing = $GLOBALS['SCP']->ls($path, $sort, $order);

	if(is_numeric($listing))
	{
		if($listing == RFUtilException::DIR_MISSING_SLASH)
		{
			PSUHTML::redirect($GLOBALS['BASE_URL'] . '/' . $GLOBALS['SSH_HOST'] . ':browse' . $path . '/');
		}
		else
		{
			throw new RFUtilException($listing);
		}
	}

	// massage file sizes into something human-readable
	foreach($listing as $k => $f)
	{
		if(!array_key_exists('size', $f))
		{
			continue;
		}

		$units = array('B', 'KB', 'MB', 'GB');
		$s = $f['size'];
		$unit = array_shift($units);
		while($s > 1023)
		{
			$s /= 1023.0;
			$unit = array_shift($units);
		}

		$listing[$k]['size'] = sprintf('%d', $s);
		$listing[$k]['size_unit'] = $unit;
	}
	$tpl->assign('listing', $listing);

	if($GLOBALS['RFP']->canDelete($_SESSION['pidm'], $path))
	{
		$tpl->assign('can_delete', true);
	}

	$log_data['result'] = 'success';
}
catch(Exception $e)
{
	$response['exit_code'] = $e->getCode();
	$response['error'] = $e->getMessage();

	if($log_data['result'] == null)
	{
		$log_data['result'] == 'failure';
	}
}

rf_log($log_data);

$tpl->assign('path', $path);
$tpl->assign('content', 'browse');
$tpl->assign('filter', $filter);
$tpl->assign('title', $GLOBALS['SSH_HOST'] . ':' . htmlentities($path));
$tpl->assign('sort', $sort);
$tpl->assign('order', $order);

$tpl->assign('response', $response);
$tpl->assign('dirpath', $dirpath);
$tpl->display('_wrapper.tpl');

// vim:ts=2:sw=2:noet:
