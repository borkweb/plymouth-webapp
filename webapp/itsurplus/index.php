<?php

require_once 'autoload.php';
PSU::session_start(); // force ssl + start a session

$GLOBALS['BASE_URL'] = '/webapp/itsurplus';
$GLOBALS['BASE_DIR'] = __DIR__;

$GLOBALS['TITLE'] = 'ITSurplus';
$GLOBALS['TEMPLATES'] = $GLOBALS['BASE_DIR'] . '/templates';

require_once 'klein/klein.php';

require_once $GLOBALS['BASE_DIR'] . '/includes/ITSurplusAPI.class.php';
define('PSU_CDN', false);

// Icons
$GLOBALS['ICONS']='https://'.$_SERVER['HTTP_HOST'].'/images/icons';

//Image filepath base
$GLOBALS['GLPI_IMAGE_BASE'] = 'http://puppis.plymouth.edu/inventory/files/';

if( file_exists( $GLOBALS['BASE_DIR'] . '/debug.php' ) ) {
	include $GLOBALS['BASE_DIR'] . '/debug.php';
}

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

	// setup navigation links, and assign to the template
	$nav_links = array(
		'nav-home' => array(
			'title' => 'Home',
			'url' => $GLOBALS['BASE_URL'].'/',
			'icon' => 'home',
			'class' => 'nav-icon',

		),
		'nav-pickup' => array(
			'title' => 'Request Pickup',
			'url' => 'http://go.plymouth.edu/supportticket/surplus',
			'icon' => 'truck',
			'class' => 'nav-icon',
		),
		'nav-help' => array(
			'title' => 'Help',
			'url' => 'http://go.plymouth.edu/surplus-help/',
			'icon' => 'umbrella',
			'class' => 'nav-icon',
		),
	);
	$app->tpl->assign( 'nav_links', $nav_links );

	// assign any notifications about surplus that we have...
	foreach( ITSurplusAPI::notifications() as $message ) {
		$_SESSION['messages'][] = $message['text'];
	}//end foreach

	// assign a default item description accross the site
	$app->tpl->assign( 'default_description', 'No details about this item are available at this time.' );

	// assign the various constants to display the filter form
	$app->tpl->assign( 'manufacturers', ITSurplusAPI::manufacturers() );
	$app->tpl->assign( 'types', ITSurplusAPI::types() );
	$app->tpl->assign( 'models', array_keys(ITSurplusAPI::models()) );
	$app->tpl->assign( 'price_range', ITSurplusAPI::price_range() );
});

//
// Searching for something specific
//
respond( '/', function( $request, $response, $app ) {
	if( $_GET['search_term'] ) {
		$app->tpl->assign( 'search_term', $_GET['search_term'] );
	}	
	$app->tpl->assign( 'by_model', ITSurplusAPI::by_model( $_GET ) );
	$app->tpl->assign( 'manufacturer', ITSurplusAPI::manufacturers( $_GET ) );
	$app->tpl->assign( 'model', array_keys(ITSurplusAPI::models( $_GET )) );
	$app->tpl->assign( 'selected_price_range', ITSurplusAPI::price_range( $_GET ) );
	$app->tpl->assign( 'type', ITSurplusAPI::types( $_GET ) );
	$app->tpl->display('index.tpl');
});

//
// Bring up a detail page for a particular model
//
respond( '/item/model/[:model]/?', function( $request, $response, $app ) {
	$models = ITSurplusAPI::by_model( array(
		'model' => array( $request->model ),
	) );
	$app->tpl->assign( 'model_info', $models[ $request->model ] );
	$app->tpl->display('model.tpl');
});

//
// Bring up a detail page for each computer of a certain model
//
respond( '/item/model/[:model]/list/?', function( $request, $response, $app ) {
	$items = ITSurplusAPI::by_model( array(
		'model' => array( $request->model ),
	) );
	$app->tpl->assign( 'items', $items[$request->model]['machines'] );
	$app->tpl->display('item-list.tpl');
});

respond( '/item/price/[:price]/?', function( $request, $response, $app ) {
	if( strpos($request->price, 'to') ) {
		$price = str_replace('to', '-', $request->price);
	} else {
		$price = $request->price.' - '.$request->price;	
	}
	unset( $_SESSION['messages'] );
	$response->redirect($GLOBALS['BASE_URL']."/?price=".$price);
});

//
// Bring up a detail page for a particular model
//
respond( '/item/[:item]/?', function( $request, $response, $app ) {
	$items = ITSurplusAPI::items( array(
		'search_term' => $request->item,
	) );
	$app->tpl->assign( 'item', $items[0] );
	$app->tpl->display('item.tpl');
});

//
// Bring up a detail page for a particular model
//
respond( '/howto/?', function( $request, $response, $app ) {
	$app->tpl->display('howto.tpl');
});

dispatch( $_SERVER['PATH_INFO'] );
