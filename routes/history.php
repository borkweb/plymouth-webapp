<?php
//this file will contain all of the routes for the history pages
//
//history/past
//history/pending
//
require_once $GLOBALS['BASE_DIR'] . '/includes/reserveDatabaseAPI.class.php';


respond('/', function( $request, $response, $app){
	$reservations=reserveDatabaseAPI::by_wp_id($_SESSION['wp_id']);
	$app->tpl->assign( 'locations' , reserveDatabaseAPI::locations());
	$app->tpl->assign('reservations', $reservations);
$app->tpl->display('history.tpl');
});//end /

respond('/pending', function( $request, $response, $app){
	$reservations=reserveDatabaseAPI::by_wp_id_pending($_SESSION['wp_id']);
	$app->tpl->assign( 'locations' , reserveDatabaseAPI::locations());

	$app->tpl->assign('reservations', $reservations);
$app->tpl->display('historypending.tpl');
});//end pending

respond('/search/id/[i:id]' , function( $request, $response, $app){
	$reservation_idx=$request->id;
	$app->tpl->assign( 'locations' , reserveDatabaseAPI::locations());
	$app->tpl->assign( 'reservation_idx', $reservation_idx);
	$app->tpl->assign( 'reservation' , reserveDatabaseAPI::by_id($reservation_idx));
	$app->tpl->display( 'historyreservation.tpl' );

});//end reservation/search/
