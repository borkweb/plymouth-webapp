<?php

require_once 'autoload.php';
PSU::session_start(); // force ssl + start a session

$GLOBALS['BASE_URL'] = '/webapp/cts-new';
$GLOBALS['BASE_DIR'] = dirname(__FILE__);

$GLOBALS['TITLE'] = 'Classroom Technology Services Toolkit';
$GLOBALS['TEMPLATES'] = $GLOBALS['BASE_DIR'] . '/templates';

require_once $GLOBALS['BASE_DIR'] . '/includes/CTSAPI.class.php';

require_once 'klein/klein.php';

if( file_exists( $GLOBALS['BASE_DIR'] . '/debug.php' ) ) {
	include $GLOBALS['BASE_DIR'] . '/debug.php';
}

IDMObject::authN();

/*
if( ! IDMObject::authZ('permission', 'mis') ) {
	die('You do not have access to this application.');
}
*/

/**
 * Routing provided by klein.php (https://github.com/chriso/klein.php)
 * Make some objects available elsewhere.
 */
respond( function( $request, $response, $app ) {
	// initialize the template
	$app->tpl = new PSUTemplate;

	// get the logged in user
	$app->user = PSUPerson::get( $_SESSION['wp_id'] ); 

	// assign user to template
	$app->tpl->assign( 'user', $app->user );
});

/*
// klein catch-all
respond( '[*]', function( $request, $response, $app ) {
	$app->tpl->display('index.tpl');
});
*/
respond( '/?', function( $request, $response, $app ) {
	$app->tpl->display('index.tpl');
});

respond( '/monkey-fart', function( $request, $response, $app ) {
	die('Boo');
});


$app_routes = array(
	'bork',
);

foreach( $app_routes as $base ) {
	with( "/{$base}", $GLOBALS['BASE_DIR'] . "/routes/{$base}.php" );
}//end foreach

dispatch( $_SERVER['PATH_INFO'] );
