<?php

require_once PSU_WEBAPP_BASE . '/external/klein/klein.php';

respond( function( $request, $response, $app ){
	echo "Hello, World.";
});

with( '/festivals', __DIR__ . '/festivals.php' );
