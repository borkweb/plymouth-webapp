<?php

/**
 * student_update_summer.php
 *
 * Accepts data submission from the student, and provides feedback.
 */

$student =& $_SESSION['student'];

$student['confirmed'] = ($_POST['confirmed'] == '1') ? 1 : 0;

// if this is a 'reject' condition, don't bother with any other checks

// pull data from the form
$student['confirmed_cert'] = ($_POST['confirmed_cert'] == '1') ? 1 : 0;
$student['guest_count'] = (int)$_POST['guest_count'];
$student['addr1'] = substr($_POST['addr1'], 0, 30);
$student['addr2'] = substr($_POST['addr2'], 0, 30);
$student['city'] = substr($_POST['city'], 0, 30);
$student['state'] = substr($_POST['state'], 0, 2);
$student['zip'] = substr($_POST['zip'], 0, 10);
$student['ceremony_needs'] = isset($_POST['ceremony_needs']) ? $_POST['ceremony_needs'] : null;

$required_fields = array(
	'addr1' => 'Address Line 1',
	'city' => 'City',
	'state' => 'State',
	'zip' => 'Zip'
);

// clean up inputted data
foreach($student as $key => $value)
{
	// only test strings in this way
	if(!is_string($value))
	{
		continue;
	}

	$value = trim($value);
	$value = stripslashes($value);
	$value = strip_tags($value);

	// can't think of any reason double quotes would be needed, and they
	// just cause problems for csv later on. remove them.
	$value = str_replace('"', '', $value);

	$student[$key] = $value;
}

if( $student['ceremony_needs'] == $GLOBALS['SPECIAL_NEEDS_DEFAULT'] ) {
	$student['ceremony_needs'] = null;
}

// check for missing fields
$bad_fields = array();
foreach($required_fields as $field => $long_desc)
{
	if($student[$field] == '')
	{
		array_push($bad_fields, $long_desc);
	}
}

// were there missing fields? (only check when they want the cert, otherwise we don't care)
if(count($bad_fields) > 0 && $student['confirmed_cert'] == 1)
{
	$fields = implode(', ', $bad_fields);
	$error = sprintf('The following fields are required: %s.', $fields);
	$_SESSION['errors'][] = $error;

	// send them back to the input screen
	header('Location: '.$GLOBALS['BASE_URL'].'/');
	exit;
}

// update the table if there were no errors
$result = AEStudent::saveConfirmation($_SESSION['pidm'], $GLOBALS['TERM'], $student);

if($result === false)
{
	$_SESSION['errors'][] = 'Sorry, there was an error processing your input.';
	header('Location: ' . $GLOBALS['BASE_URL'] .'?error');
}
else
{
	$_SESSION['editing'] = false;
	header('Location: ' . $GLOBALS['BASE_URL']);
}
