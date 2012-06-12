<?php

/**
 * Commits configuration changes made by administrators.
 */

// security check
if($_SESSION['user_type'] != 'admin')
{
	die('That is not allowed.');
}

// get new values
$term = trim($_REQUEST['term']);
$accepting = ($_REQUEST['accepting'] == '1') ? 1 : 0;
$dinner = strtotime($_REQUEST['dinner']);

$semester = substr($term, 4, 2);
$error = false;

// check $term contents
if(strlen($term) != 6)
{
	$error = true;
	$_SESSION['errors'][] = 'Term must be a six-digit number';
}
elseif(!is_numeric($term))
{
	$error = true;
	$_SESSION['errors'][] = 'Term must be a number.';
}
elseif((int)substr($term, 0, 4) < 2008)
{
	$error = true;
	$_SESSION['errors'][] = 'Term cannot be less than 2008.';
}
elseif($semester != "10" && $semester != "30")
{
	$error = true;
	$_SESSION['errors'][] = 'Please specify a fall or spring semester.';
}

// check dinner date format
if($dinner === false)
{
	$error = true;
	$_SESSION['errors'][] = 'Supplied dinner date cannot be parsed as a date string, please try again.';
}

if($error)
{
	header('Location: ' . $GLOBALS['BASE_URL'] . '/config.html');
	exit;
}

// passed the tests. save the data and update the config file
AEAPI::option('term')->value($term)->save();
AEAPI::option('accepting')->value($accepting)->save();
AEAPI::option('dinner')->value( strftime('%Y-%m-%d', $dinner) )->save();

$_SESSION['messages'][] = 'Your configuration changes were saved.';

header('Location: ' . $GLOBALS['BASE_URL'] . '/config.html');
