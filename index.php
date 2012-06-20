<?php

require_once 'autoload.php';

PSU::session_start(); // force ssl + start a session

$GLOBALS['BASE_URL'] = '/webapp/cts-new';
$GLOBALS['BASE_DIR'] = dirname(__FILE__);

$GLOBALS['TITLE'] = 'Classroom Technology Services Toolkit';
$GLOBALS['TEMPLATES'] = $GLOBALS['BASE_DIR'] . '/templates';

require_once $GLOBALS['BASE_DIR'] . '/includes/CTSAPI.class.php';
require_once $GLOBALS['BASE_DIR'] . '/includes/CTSEmailAPI.class.php';
require_once $GLOBALS['BASE_DIR'] . '/includes/ReserveDatabaseAPI.class.php';
require_once $GLOBALS['BASE_DIR'] . '/includes/CTSDatabaseAPI.class.php';
require_once 'klein/klein.php';

if( file_exists( $GLOBALS['BASE_DIR'] . '/debug.php' ) ) {
	include $GLOBALS['BASE_DIR'] . '/debug.php';
}

IDMObject::authN();
 
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

	$hours=array();
		//generate numbers 1 through 12 for the hours
		for($i = 1; $i <=12; $i++){

			$hours[$i]=$i;
		}
		$minutes=array();
		//generate numbers 0 through 55 every 5 numbers (0,5,10,15 etc.)
		for($x = 0;$x <=55; $x+= 5){
			$minutes[$x]=$x;

		}
	
		$app->tpl->assign( 'hours', $hours );
		$app->tpl->assign( 'minutes', $minutes );	
		$app->tpl->assign( 'ampm' , array("AM"=>"AM","PM"=>"PM"));

		$app->tpl->assign( 'user', $app->user );

		//assign vars that are used throughout the whole system
		$app->tpl->assign('date_format','%m-%d-%Y');
		$app->tpl->assign('time_format','%l:%M %p');
		$app->tpl->assign('locations',ReserveDatabaseAPI::locations(false)); 
		$app->tpl->assign('user_level',ReserveDatabaseAPI::user_level());//this assigns the user to a manager (0) cts staff (1) or helpdesk (2)
		$status=array(
			"approved"=>"approved",
			"pending"=>"pending",
			"loaned out"=>"loaned out",
			"returned"=> "returned", 
			"cancelled"=>"cancelled",
		);
		$app->tpl->assign('status', $status);
		$app->tpl->assign('priority', array("normal", "high"));
		$app->tpl->assign( 'subitemlist', ReserveDatabaseAPI::get_subitems());
});

respond( '/?', function( $request, $response, $app ) {
	$app->tpl->display('index.tpl');
});


$app_routes = array(
	'reserve',//user page for reservations
	'history',//pending and past reservations
	'admin',//admin page with all current reservations and equipment
	'user',
);

foreach( $app_routes as $base ) {
	with( "/{$base}", $GLOBALS['BASE_DIR'] . "/routes/{$base}.php" );
}//end foreach

dispatch( $_SERVER['PATH_INFO'] );
