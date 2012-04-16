<?php

require_once PSU_EXTERNAL_DIR . '/klein/klein.php';

respond( function( $request, $response, $app ){
	echo "Document Root: ";
	var_dump( $_SERVER['DOCUMENT_ROOT'] );
	echo " (#" . $_SERVER['VARIATION'] . ")<br>";

	echo "Request URI: ";
	var_dump( $_SERVER['REQUEST_URI'] );
	echo "<br>";

	var_dump( $request );
});

with( '/festivals', __DIR__ . '/festivals.php' );
