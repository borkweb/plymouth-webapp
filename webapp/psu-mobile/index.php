<?php

require_once 'autoload.php';
PSU::session_start(); // force ssl + start a session

// Let's send UTF-8 (thanks abackstrom)
header( 'Content-Type: text/html; charset=UTF-8' );

$GLOBALS['BASE_URL'] = '/webapp/psu-mobile';
$GLOBALS['BASE_DIR'] = __DIR__;

$GLOBALS['TITLE'] = 'PSU Mobile';
$GLOBALS['TEMPLATES'] = $GLOBALS['BASE_DIR'] . '/templates/';

$GLOBALS['APP_VERSION'] = '0.8.0';
$GLOBALS['APP_BUILD_NAME'] = 'jqm-html5';
$GLOBALS['APP_BUILD_TYPE'] = 'beta';

// Set some globals about our server for easy use later
$GLOBALS['APP_PROTOCOL'] = ($_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
$GLOBALS['APP_URL'] = $GLOBALS['APP_PROTOCOL'] . $_SERVER['HTTP_HOST'] . $GLOBALS['BASE_URL'];

// If the app is currently running on the development server
if (PSU::isdev()) {
	// Have the APP_BUILD_TYPE global reflect the current server/code status
	$GLOBALS['APP_BUILD_TYPE'] .= '-dev';

	// Set a global for easier access from templates
	$GLOBALS['IS_DEV'] = true;

	// Turn off the CDN
	define('PSU_CDN', false);
}
else {
	// Have the APP_BUILD_TYPE global reflect the current server/code status
	$GLOBALS['APP_BUILD_TYPE'] .= '-prod';

	// Set a global for easier access from templates
	$GLOBALS['IS_DEV'] = false;
}

// Include my custom mobile smarty class
require_once $GLOBALS['BASE_DIR'] . '/includes/MobileTemplate.class.php';

// Include my custom mobile smarty class
require_once $GLOBALS['BASE_DIR'] . '/includes/MobileParams.class.php';

// Include the Klein PHP routing engine
require_once 'klein/klein.php';

if( file_exists( $GLOBALS['BASE_DIR'] . '/debug.php' ) ) {
	include $GLOBALS['BASE_DIR'] . '/debug.php';
}

// Register my directory into the autoloader
includes_psu_register( 'Mobile', $GLOBALS['BASE_DIR'] . '/includes' );


/**
 * Routing provided by klein.php (https://github.com/chriso/klein.php)
 */

// Make some objects available elsewhere
respond( function( $request, $response, $app ) {
	// Initialize the custom parameters
	$app->params = new MobileParams;

	// Initialize the PSU smarty templating
	$app->tpl = new MobileTemplate;

	// Add the parameters object to the template
	$app->tpl->assign( 'params', $app->params );
});

// Generic request 
respond( '/', function( $request, $response, $app ) {
	// Grab a couple of the request parameters
	$app->params['phonegap'] = $request->param('phonegap');
	$app->params['cordova'] = $request->param('cordova');
	$app->params['client_app'] = $request->param('client-app');

	// Remove the variables if they're null
	if (is_null($app->params['phonegap'])) {
		unset($app->params['phonegap']);
	}
	if (is_null($app->params['cordova'])) {
		unset($app->params['cordova']);
	}
	if (is_null($app->params['client_app'])) {
		unset($app->params['client_app']);
	}

	// Show the index on a generic request
	$app->tpl->display( '_wrapper.tpl' );
});

$app_routes = array(
	'newsfeed',
	'campusmap',
	'feedback',
	'clusters',
	'directory',
	'events',
	'login',
	'logout',
	'schedule',
	'upgrade',
);

foreach( $app_routes as $base ) {
	with( "/{$base}", $GLOBALS['BASE_DIR'] . "/routes/{$base}.php" );
}//end foreach

// Let's do some cleanup
respond( function( $request, $response, $app ) {
	// Remove our "back button url" session var. It was only needed for a reload.
	unset( $app->params['back_button_url'] );
});

dispatch( $_SERVER['PATH_INFO'] );
