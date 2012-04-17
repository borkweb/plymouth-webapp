<?php

require_once PSU_EXTERNAL_DIR . '/klein/klein.php';

with( '/festivals', __DIR__ . '/festivals.php' );

respond( '404', function() {
	header( 'Content-Type: text/plain' );
	echo '404 Not Found';
	error_log( sprintf( "404 %s%s", $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI'] ), E_USER_NOTICE );
});
