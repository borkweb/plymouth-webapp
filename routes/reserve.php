<?php
//This file will route all of the traffic for the user side of the reservation system.
//
//reserve/contact
//reserve/event
//reseve/equipment
//reserve/confirm
//reserve/success
	
respond( 'POST', '/contact',function( $request, $response, $app){
	//required parameters
	$curr_page="/";
	$first_name=$request->param('first_name');
	$last_name=$request->param('last_name');
	$phone=$request->param('phone');

	if( ! $first_name ){ //if there is no first name
		$_SESSION['errors'][]='First name not found'; //throw error
	}elseif( ! $last_name ){ //if there is no last name
		$_SESSION['errors'][]='Last name not found'; //throw error
	}elseif( ! $phone ){ //if there is no phone number
		$_SESSION['errors'][]='Phone number not found'; //throw error
	}


	if( count($_SESSION['errors'])>0){
		$response->redirect( $GLOBALS['BASE_URL'] );//redirect to the current page
	}else{
	
		$app->tpl->display( $GLOBALS['BASE_URL'] . '/templates/equipment.tpl');
		//$response->redirect( $GLOBALS['BASE_URL'] . "/");//redirect to the next page
	}

});

respond( 'POST','/event',function( $request, $response, $app){
	$start_date=$request->param('start_date');//request a parameter for start_date
	$end_date=$request->param('end_date');//request a parameter for enddate

	if( ! $start_date ){//if there is no start date
		$_SESSION['errors'][]='Start Date not found';
	}elseif( ! $end_date ){ //if there is no end date
		$_SESSION['errors'][]='End Date not found';
	}

	if( count($_SESSION['errors'])>0){//if the number of errors is > 0
		$response->redirect( $GLOBALS['BASE_URL'] );
	}else{
		//if there are no errors then redirect to the next step
		$response->redirect( $GLOBALS['BASE_URL'] . "/reserve/equipment" );
	}
	PSU::dbug($_SESSION['errors']);
	PSU::dbug($_POST);

});
