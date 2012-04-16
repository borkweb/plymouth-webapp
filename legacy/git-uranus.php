<?php

function _psuwebapp_uranus() {
	$path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
	$base = $default_base = dirname( __DIR__ );

	if( isset( $_COOKIE['psudevhost'] ) ) {
		$host = $_COOKIE['psudevhost'];
		$tld = substr( $host, strrpos( $host, '.' ) + 1 );
		$base = "/web/dev/{$tld}/{$host}";
	}

	if( file_exists( $resource_file = $base . $path ) && is_file( $resource_file ) ) {
		$extension = substr( $resource_file, strrpos( $resource_file, '.' ) + 1 );
		switch( $extension ) {
			case 'css': header( 'Content-Type: text/css' ); break;
			case 'png': header( 'Content-Type: image/png' ); break;
			case 'gif': header( 'Content-Type: image/gif' ); break;
			case 'jpg': header( 'Content-Type: image/jpg' ); break;
			case 'js': header( 'Content-Type: text/javascript' ); break;
		}
		readfile( $resource_file );
		die;
	}

	if( $base !== $default_base ) {
		include "{$base}/app/index.php";
		die;
	}
}

_psuwebapp_uranus();
