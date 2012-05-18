<?php

respond( '/person-data', function( $request, $response, $app ) {
	if( ! $app->permissions->can_search() ) {
		$response->code(401);
		die( '401 Not Authorized' );
	}

	$ids = $request->param( 'id' );
	$response->psu_lazyload( $ids );
});
