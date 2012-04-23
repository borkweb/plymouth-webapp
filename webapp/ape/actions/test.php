<?php

// setup response arrays
$response = array();
$response['messages'] = array();
$response['errors'] = array();

// determine if this is an ajax request
$is_ajax = false;
if(isset($_REQUEST['is_ajax']))
{
	$is_ajax = true;
}

// get pidm before we build $url
$pidm = $_REQUEST['pidm'];

// build the url that we'll push back to during a redirect
$url = $GLOBALS['BASE_URL'] . '/user/' . $pidm;

//
// real code here. just add messages in this test.
//
$response['messages'][] = "My message.";
$response['errors'][] = "My cool error.";

// output and exit
action_cleanup($url, $response, $is_ajax);

?>
