<?php

// Generic response (don't force the trailing slash: this should catch any accidental laziness)
respond( '/?', function( $request, $response, $app ){
	// Display the template
	$app->tpl->assign( 'show_page', 'upgrade' );
	$app->tpl->display( '_wrapper.tpl' );
});
