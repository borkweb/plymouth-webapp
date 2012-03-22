<?php
//this page will deal with all of the routing for the admin pages
//
//admin/equipment
//admin/reservation
require_once $GLOBALS['BASE_DIR'] . '/includes/CTSdatabaseAPI.class.php';
require_once $GLOBALS['BASE_DIR'] . '/includes/reserveDatabaseAPI.class.php';


respond('/equipment', function( $request, $response, $app) {
	$app->tpl->assign( 'manufacturers', CTSdatabaseAPI::manufacturers() );
	
	PSU::dbug(CTSdatabaseAPI::manufacturer());
	$app->tpl->assign( 'types', CTSdatabaseAPI::types() );
	$app->tpl->assign( 'models', array_keys(CTSdatabaseAPI::models()) );
	$app->tpl->display('admincps.tpl');

});//end equipment

respond('/reservation' , function( $request, $response, $app){
	$app->tpl->assign( 'locations' , reserveDatabaseAPI::locations());
	$start_date=date('Y-m-d');
	$app->tpl->assign( 'reservation' , reserveDatabaseAPI::by_date($start_date));

	$app->tpl->display( 'admincp.tpl' );
	PSU::db('cts')->debug=true;

});//end reservation

respond('/reservation/search/id/[i:id]' , function( $request, $response, $app){
	$reservation_idx=$request->id;
	$app->tpl->assign( 'messages', reserveDatabaseAPI::getMessages($reservation_idx));
	$app->tpl->assign( 'equipment', reserveDatabaseAPI::getEquipment($reservation_idx));
	$app->tpl->assign( 'locations' , reserveDatabaseAPI::locations());
	$app->tpl->assign( 'reservation_idx', $reservation_idx);
	PSU::dbug($reservation_idx);
	PSU::db('cts')->debug=true;
	$app->tpl->assign( 'reservation' , reserveDatabaseAPI::by_id($reservation_idx));
	PSU::dbug(reserveDatabaseAPI::by_id($reservation_idx));
	$app->tpl->display( 'singlereservation.tpl' );

});//end reservation/searach/id


respond('/reservation/addmessage/[i:id]', function( $request, $response, $app){
	PSU::dbug($request->message);
	$username=$_SESSION['username'];
	$message=$request->message;
	$reservation_idx=$request->id;
	reserveDatabaseAPI::addMessage($reservation_idx,$message, $username);
	PSU::db('cts')->debug=true;
	$response->redirect($GLOBALS['BASE_URL'].'/admin/reservation/search/id/'.$reservation_idx);


});//add message to reservation

respond('/reservation/search/[a:action]' , function( $request, $response, $app){
	$app->tpl->assign( 'locations' , reserveDatabaseAPI::locations());
	if($request->action=="nextweek"){
		$start_date=date('Y-m-d', strtotime("+1 week"));
		$end_date=date('Y-m-d', strtotime("+2 week"));
		$dates=array($start_date, $end_date);
		$app->tpl->assign( 'reservation' , reserveDatabaseAPI::by_date_range($dates));

	}elseif($request->action=="thisweek"){
		$start_date=date('Y-m-d');
		$end_date=date('Y-m-d', strtotime("+1 week"));
		$dates=array($start_date, $end_date);
		$app->tpl->assign( 'reservation' , reserveDatabaseAPI::by_date_range($dates));
	}elseif($request->action=="lastweek"){
		$start_date=date('Y-m-d', strtotime("-1 week"));
		$end_date=date('Y-m-d');
		$dates=array($start_date, $end_date);
		$app->tpl->assign( 'reservation' , reserveDatabaseAPI::by_date_range($dates));
	}elseif($request->action=="today")
	{
		$start_date=date('Y-m-d');
		$app->tpl->assign( 'reservation' , reserveDatabaseAPI::by_date($start_date));

	}elseif($request->action=="yesterday")
	{
		$start_date=date('Y-m-d', strtotime("-1 day"));
		$app->tpl->assign( 'reservation' , reserveDatabaseAPI::by_date($start_date));

	}elseif($request->action=="tommorrow")
	{
		$start_date=date('Y-m-d', strtotime("+1 day"));
		$app->tpl->assign( 'reservation' , reserveDatabaseAPI::by_date($start_date));

	}elseif($request->action=="pending"){
		$query="pending";
		$app->tpl->assign( 'reservation' , reserveDatabaseAPI::by_status($query));
	}

	$app->tpl->assign('start_date', $start_date);
	$app->tpl->assign('end_date',$end_date);
	$app->tpl->display( 'reservation.tpl' );
	PSU::db('cts')->debug=true;

});//end reservation/search/action

