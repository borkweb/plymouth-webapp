<?php

///
/// Accept a file upload, deleting the old file.
///

if( ! is_uploaded_file( $_FILES['roster']['tmp_name'] ) ) {
	$_SESSION['errors'][] = 'That didn\'t look like an uploaded file.';
	PSU::redirect( $GLOBALS['BASE_URL'] );
}

$dh = opendir( $GLOBALS['UPLOAD_DIR'] );

// delete old file
while( ($file = readdir($dh)) !== false ) {
	$path = $GLOBALS['UPLOAD_DIR'] . '/' . $file;
	if( is_file( $path ) ) {
		unlink( $path );
		break;
	}
}

PSUMeta::set('webapp_els', 'roster_uploader', $_SESSION['pidm']);
PSUMeta::set('webapp_els', 'roster_filename', $_FILES['roster']['name']);
PSUMeta::set('webapp_els', 'roster_uploaded', time());

$roster_path = $GLOBALS['UPLOAD_DIR'] . '/' . $_FILES['roster']['name'];
move_uploaded_file( $_FILES['roster']['tmp_name'], $roster_path );

PSU::redirect( $GLOBALS['BASE_URL'] );
