<?php 

require dirname( __DIR__ ) . '/legacy/git-bootstrap.php';

/*
 * Stream a binary file if logged in. Requires rewrite rule for each filetype.  
 */

ob_end_clean();

require_once 'autoload.php';
PSU::session_start();

/*
 * Determine directories in which we'll look for this file.
 */
$path = $_SERVER['REQUEST_URI'];

$valid_roots = array(
	'/web/pscpages', // legacy web root
);

$parts = explode( '/', $path );

// app and webapp can be pulled from this repository
if( in_array( $parts[1], array( 'app', 'webapp' ), true ) ) {
	$valid_roots[] = dirname( __DIR__ );
}

// Iterate all valid document roots, looking for this file
foreach( $valid_roots as $root ) {
	if( $fullpath = realpath( $root . '/' . $path ) ) {
		if( substr( $fullpath, 0, strlen( $root ) + 1 ) === $root . '/' ) {
			break;
		}
	}

	$fullpath = false;
}

// Production currently specifies "RewriteCond %{REQUEST_FILENAME} -f", so 
// in theory we won't reach this line of code.
if( false == $fullpath || ! is_file( $fullpath ) ) {
	header('HTTP/1.1 404 Not Found');
	exit('File not found error (' . $path . ')');
}

$user = IDMObject::authN();

$fullpath_dir = dirname( $fullpath );

// Find path to our "secure" directory
if( '/secure' === substr( $fullpath_dir, -7 ) ) {
	$secure_dir = $fullpath_dir;
} else {
	$parts = explode( '/', $fullpath_dir );
	while( 'secure' !== array_pop( $parts ) ) {
		if( 0 === count( $parts ) ) {
			break;
		}
	}

	$secure_dir = implode( '/', $parts ) . '/secure';
}

// Examine optional .htrole
if( file_exists( $htrole = $secure_dir . '/.htrole' ) ) {
	$roles = file( $htrole );

	foreach( $roles as $role ) {
		if( ! in_array( trim($role), $_SESSION['AUTHZ']['banner'] ) ) {
			header('HTTP/1.1 403 Forbidden');
			exit('You do not have access to the requested file.');
		}
	}
}

switch( substr( $fullpath, strrpos( $fullpath, '.' ) + 1 ) ) {
	case 'png':
		$content_type = 'image/png'; break;
	case 'jpg':
	case 'jpeg':
		$content_type = 'image/jpeg'; break;
	case 'gif':
		$content_type = 'image/gif'; break;
	case 'pdf':
		$content_type = 'application/pdf'; break;
	default: $content_type = 'application/octet-stream';
}

header( 'Pragma: no-cache' );
header( 'Cache-Control: no-cache, must-revalidate' );
header( 'Content-Type: ' . $content_type );
header( 'Content-Length: ' . filesize( $fullpath ) );
header( 'Content-Transfer-Encoding: binary' );

readfile( $fullpath );
