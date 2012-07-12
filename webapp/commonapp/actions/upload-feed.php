<?php

if( ! IDMObject::authZ('permission', 'commonapp_upload') ) {
	$_SESSION['errors'][] = 'You do not have permission to upload Common App feeds.';
	PSUHTML::redirect( $GLOBALS['BASE_URL'] );
}

$tmp_name = $_FILES['feed']['tmp_name'];
$new_name = $GLOBALS['TMP'] . '/' . $_FILES['feed']['name'];

if( !is_uploaded_file( $tmp_name ) ) {
	$_SESSION['errors'][] = "Uploaded file not found.";
	PSUHTML::redirect( $GLOBALS['BASE_URL'] . '/upload.html' );
}

if( !is_dir( $GLOBALS['TMP'] ) ) {
	mkdir( $GLOBALS['TMP'] );
}

move_uploaded_file( $tmp_name, $new_name );

if( !is_file( $new_name ) ) {
	$_SESSION['errors'][] = 'Could not find renamed file at ' . $new_name;
	PSUHTML::redirect( $GLOBALS['BASE_URL'] . '/upload.html' );
}

$result = chmod( $new_name, 0600 );

if( $result == false ) {
	$_SESSION['errors'][] = 'Could not chmod ' . $new_name;
	PSUHTML::redirect( $GLOBALS['BASE_URL'] . '/upload.html' );
}

$ca = new CommonApp( $new_name );

$ca->import();

if( count($ca->errors) ) {
	// there were errors
	$_SESSION['errors'][] = 'Some records file failed to import.';
	$_SESSION['errors'] = array_merge($_SESSION['errors'], $ca->errors);
} else {
	$_SESSION['messages'][] = 'Feed file imported successfully.';
}

unlink( $new_name );

PSUHTML::redirect( $GLOBALS['BASE_URL'] . '/upload.html' );
