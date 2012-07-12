<?php

respond( '[*]', function( $request, $response, $app ) {
	PSU::dbug( $request->params() );
});
