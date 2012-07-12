<?php

require_once PSU_EXTERNAL_DIR . '/klein/klein.php';

respond( function( $request, $response, $app ) {
	$app->config = PSU\Config\Factory::get_config();

	if( false == $app->config->get( 'cdn', 'enabled', true ) ) {
		define( 'PSU_CDN', false );
	}

	$response->psu_lazyload = function( $ids ) use ( $request, $response, $app ) {
		if( ! is_array($ids) ) {
			$ids = array( $ids );
		}

		$qs = http_build_query( array( 'id' => $ids ) );

		try {
			$json = \PSU::api('backend')->get( "user/?$qs" );
			$people = $json;
		} catch( Exception $e ) {
			// nothing...?
			throw $e;
		}

		$response->header( 'Content-Type', 'application/json' );
		$response->json( $people );
	};
});

// Load routes for this hostname
$webapp->host()->routes();

respond( '404', function( $request, $response, $app ) {
	$response->code( 404 );
	$response->header( 'Content-Type', 'text/plain' );
	echo '404 Not Found';
	error_log( sprintf( "404 %s%s", $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI'] ), E_USER_NOTICE );
});
