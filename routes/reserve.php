<?php
//This file will route all of the traffic for the user side of the reservation system.
//
//reserve/contact
//reserve/event
//reseve/equipment
//reserve/confirm
//reserve/success
require_once $GLOBALS['BASE_DIR'] . '/includes/reserveDatabaseAPI.class.php';
require_once $GLOBALS['BASE_DIR'] . '/includes/CTSdatabaseAPI.class.php';

respond( '/', function( $request, $response, $app){
	$app->tpl->assign( 'locations' , reserveDatabaseAPI::locations());
	$app->tpl->display( 'event.tpl' );

});//end /



respond ( '/equipment', function( $request, $response, $app){
	$equipment_id=$request->param('equipment_id');
	if($equipment_id){
		$_SESSION['cts']['equipment'][]=$equipment_id;
	}

	$app->tpl->assign( 'categories', reserveDatabaseAPI::categories());
	$app->tpl->assign( 'equipment', $_SESSION['cts']['equipment']); 
	PSU::dbug($_SESSION['cts']);
	PSU::dbug(reserveDatabaseAPI::categories());
	$app->tpl->display( 'equipment.tpl' );
	

});//end equipment

respond( '/equipment/[:id]/?', function( $request, $response, $app){
	$app->tpl->assign( 'categories', reserveDatabaseAPI::categories());

	$app->tpl->assign( 'equipment_id' ,$request->id );
	$app->tpl->display( 'equipment.tpl' );
});//equipment with id

respond( 'POST', '/event',function( $request, $response, $app){

	//required parameters
	$curr_page="/";
	$first_name=$request->param('first_name');
	$last_name=$request->param('last_name');
	$phone=$request->param('phone');
	$secondary_phone=$request->param('secondary_phone');
	$email=$request->param('email');

	$first_name=filter_var($first_name, FILTER_SANITIZE_STRING);
	$last_name=filter_var($last_name, FILTER_SANITIZE_STRING);
	$phone=filter_var($phone, FILTER_SANITIZE_STRING);
	$secondary_phone=filter_var($secondary_phone, FILTER_SANITIZE_STRING);
	$email=filter_var($email, FILTER_SANITIZE_STRING);

	$reserve_type=$request->param('radio');
	$start_date=$request->param('start_date');//request a parameter for start_date
	$end_date=$request->param('end_date');//request a parameter for enddate
	$title=$request->param('title');//request a parameter for title
	$location=$request->param('location');//request a parameter for location
	$room=$request->param('room');
	
	$comments=$request->param('comments');
	$comments=filter_var($comments,FILTER_SANITIZE_STRING);

	$starthour=$request->param('starthour');
	$startminute=$request->param('startminute');
	$startampm=$request->param('startampm');
	$start_time=$starthour . ':' . $startminute . ':' . $startampm;

	$endhour=$request->param('endhour');
	$endminute=$request->param('endminute');
	$endampm=$request->param('endampm');
	$end_time=$endhour . ':' . $endminute . ':' .$endampm;


	if( ! $first_name ){ //if there is no first name
		$_SESSION['errors'][]='First name not found'; //throw error
	}elseif( ! $last_name ){ //if there is no last name
		$_SESSION['errors'][]='Last name not found'; //throw error
	}elseif( ! $phone ){ //if there is no phone number
		$_SESSION['errors'][]='Phone number not found'; //throw error
	}elseif( ! $email ){
		$_SESSION['errors'][]='Email not found';
	}elseif( ! $title ){
		$_SESSION['errors'][]='Event Title not found';
	}elseif( ! $location){
		$_SESSION['errors'][]='Location not found';
	}elseif( $location == "Please select a location" ) {
		$_SESSION['errors'][]='Location not found';
	}elseif( ! $room ){
		$_SESSION['errors'][]='Room not found';
	}elseif( ! $start_date ){//if there is no start date
		$_SESSION['errors'][]='Start Date not found';
	}elseif( ! $end_date ){ //if there is no end date
		$_SESSION['errors'][]='End Date not found';
	}//end elseif

	if( count($_SESSION['errors'])>0 ){//if the number of errors is > 0
		$response->redirect( $GLOBALS['BASE_URL'] . '/reserve/' );
	}else{
		$_SESSION['cts']['first_name']=$first_name;
		$_SESSION['cts']['last_name']=$last_name;
		$_SESSION['cts']['phone']=$phone;
		
		if( $secondary_phone ){
			$_SESSION['cts']['secondary_phone']=$secondary_phone;
		}
		$_SESSION['cts']['email']=$email;
		$_SESSION['cts']['title']=$title;
		$_SESSION['cts']['location']=$location;
		$_SESSION['cts']['room']=$room;
		
		if( $comments ) {
			$_SESSION['cts']['comments']=$comments;
		}

		$_SESSION['cts']['start_date']=$start_date;
		$_SESSION['cts']['end_date']=$end_date;
		$_SESSION['cts']['start_time']=$start_time;
		$_SESSION['cts']['end_time']=$end_time;
		$_SESSION['cts']['reserve_type']=$reserve_type;

		$response->redirect($GLOBALS['BASE_URL'] . '/reserve/equipment');
		$app->tpl->display( 'equipment.tpl' );
	}//end else
});//end event respond
