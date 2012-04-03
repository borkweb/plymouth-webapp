<?php
//this page will deal with all of the routing for the admin pages
//
//admin/equipment
//admin/reservation
require_once $GLOBALS['BASE_DIR'] . '/includes/CTSdatabaseAPI.class.php';
require_once $GLOBALS['BASE_DIR'] . '/includes/reserveDatabaseAPI.class.php';

/*
respond('[*]', function( $request, $response, $app){

});
 */

respond('/admincp', function( $request, $response, $app){

	$app->tpl->assign( 'categories', reserveDatabaseAPI::getFormOptions());
	$app->tpl->display( 'admincp.tpl' );
});//admin cp

respond('/admincp/equipment', function( $request, $response, $app){
	$app->tpl->assign( 'categories', reserveDatabaseAPI::getFormOptions());
	$app->tpl->display( 'adminequipment.tpl' );

});//admincp equipment page

respond('POST', '/admincp/equipment/add', function( $request, $response, $app){
	$category = $request->param('new_equipment');
	$category=filter_var($category, FILTER_SANITIZE_STRING);
	$description=$request->param('description');
	$description=filter_var($description, FILTER_SANITIZE_STRING);
	reserveDatabaseAPI::insertFormOptions($category,$description);
	$response->redirect($GLOBALS['BASE_URL'] . '/admin/admincp/equipment' );
});//admin equipment add

respond('/admincp/equipment/[i:id]/remove', function( $request, $response, $app){
	$equipment_id=$request->id;
	reserveDatabaseAPI::deleteEquipment($equipment_id);
	$response->redirect($GLOBALS['BASE_URL'] . '/admin/admincp/equipment');
	

});//admin equipment 

respond('/equipment', function( $request, $response, $app) {
	$app->tpl->assign( 'manufacturers', CTSdatabaseAPI::manufacturers() );
	
	$app->tpl->assign( 'types', CTSdatabaseAPI::types() );
	$app->tpl->assign( 'models', array_keys(CTSdatabaseAPI::models()) );
	//$app->tpl->display('admincp.tpl');

});//end equipment

respond('/reservation' , function( $request, $response, $app){
	$app->tpl->assign( 'locations' , reserveDatabaseAPI::locations());
	$start_date=date('Y-m-d');
	$app->tpl->assign( 'reservation' , reserveDatabaseAPI::by_date($start_date));

	$app->tpl->display( 'reservation.tpl' );

});//end reservation

respond('/reservation/search/id/[i:id]' , function( $request, $response, $app){
	$reservation_idx=$request->id;
	$query=new \PSU\Population\Query\IDMAttribute('mis','permission');
	$factory = new \PSU_Population_UserFactory_PSUPerson;
	$population= new \PSU_Population( $query, $factory );	
	$cts_technicians=$population->query();
	PSU::dbug($population);
	PSU::dbug($cts_technicians);
	$app->tpl->assign( 'cts_technicians',$cts_technicians );
	//$app->tpl->assign( 'cts_technicians',array(000256614=>"David Allen",000256615 => "Technician Dave"));//list of CTS technicians
	$app->tpl->assign( 'messages', reserveDatabaseAPI::getMessages($reservation_idx));
	$app->tpl->assign( 'equipment', reserveDatabaseAPI::getEquipment($reservation_idx));
	$app->tpl->assign( 'locations' , reserveDatabaseAPI::locations());
	$app->tpl->assign( 'reservation_idx', $reservation_idx);
	$app->tpl->assign( 'reservation' , reserveDatabaseAPI::by_id($reservation_idx));
	$app->tpl->display( 'singlereservation.tpl' );

});//end reservation/search/i

respond('/reservation/search/id/[i:id]/[a:action]' , function( $request, $response, $app){
	if($request->action=="edit"){//if the action is to edit the current reservation
		$editable=true;
		$app->tpl->assign( 'editable', $editable);
		$reservation_idx=$request->id;
		$app->tpl->assign( 'messages', reserveDatabaseAPI::getMessages($reservation_idx));
		$app->tpl->assign( 'equipment', reserveDatabaseAPI::getEquipment($reservation_idx));
		$app->tpl->assign( 'locations' , reserveDatabaseAPI::locations());
		$app->tpl->assign( 'reservation_idx', $reservation_idx);
		$app->tpl->assign( 'reservation' , reserveDatabaseAPI::by_id($reservation_idx));
		PSU::dbug(reserveDatabaseAPI::by_id($reservation_idx));
		$app->tpl->display( 'singlereservation.tpl' );

	}//edit

	if($request->action=="delete"){
		$reservation_idx=$request->id;
		reserveDatabaseAPI::deleteReservation($reservation_idx);
		reserveDatabaseAPI::deleteMessages($reservation_idx);
		$response->redirect($GLOBALS['BASE_URL'].'/admin/reservation');
	}//delete
	
});//end reservation/searach/id


respond('/reservation/addmessage/[i:id]', function( $request, $response, $app){
	PSU::dbug($request->message);
	$username=$_SESSION['username'];
	$message=$request->message;
	$reservation_idx=$request->id;
	reserveDatabaseAPI::addMessage($reservation_idx,$message, $username);
	$response->redirect($GLOBALS['BASE_URL'].'/admin/reservation/search/id/'.$reservation_idx);


});//add message to reservation

respond('/reservation/search/[a:action]' , function( $request, $response, $app){
	$app->tpl->assign( 'locations' , reserveDatabaseAPI::locations());
	define('ONE_DAY', 60*60*24);//defining what one day is
	$week=date('w');//define the current week
	if($request->action=="nextweek"){
		//$start_date=date('Y-m-d', strtotime("+1 week"));
		//$end_date=date('Y-m-d', strtotime("+2 week"));
		$start_date=date('Y-m-d',time()- ($week - 7) * ONE_DAY);
		$end_date=date('Y-m-d',time()- ($week - 13) * ONE_DAY);

		$dates=array($start_date, $end_date, $start_date, $end_date);
		$app->tpl->assign( 'reservation' , reserveDatabaseAPI::by_date_range($dates));

	}elseif($request->action=="thisweek"){
		$start_date=date('Y-m-d',time()- ($week) * ONE_DAY);
		$end_date=date('Y-m-d',time()- ($week - 6) * ONE_DAY);
		$dates=array($start_date, $end_date, $start_date, $end_date);
		$app->tpl->assign( 'reservation' , reserveDatabaseAPI::by_date_range($dates));
	}elseif($request->action=="lastweek"){
		$start_date=date('Y-m-d',time()- ($week + 7) * ONE_DAY);
		$end_date=date('Y-m-d',time()- ($week + 1) * ONE_DAY);
		$dates=array($start_date, $end_date, $start_date, $end_date);
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

});//end reservation/search/action

