<?php
//This file will route all of the traffic for the user side of the reservation system.
//
//reserve/contact.php
//reserve/event.php
//reseve/confirm.php
//reserve/success.php
//reserve/equipment.php
	
$curr_page='/';
respond( 'POST', '/contact',function( $request, $respond, $app){
	//required parameters
	$first_name=$request->param('first_name');
	$last_name=$request->param('last_name');
	$phone=$request->param('phone');
	if( ! $first_name ){ //if there is no first name
		$_SESSION['errors'][]='First name not found'; //throw error
		$response->redirect( $GLOBALS['BASE_URL'] . $curr_page ); //redirect them back to the same page
	}
	if( ! $last_name ){ //if there is no first name
		$_SESSION['errors'][]='Last name not found'; //throw error
		$response->redirect( $GLOBALS['BASE_URL'] . $curr_page ); //redirect them back to the same page
	}

	if( ! $phone ){ //if there is no first name
		$_SESSION['errors'][]='Phone number not found'; //throw error
		$response->redirect( $GLOBALS['BASE_URL'] . $curr_page ); //redirect them back to the same page
	}
});

respond( 'POST','/event',function( $request, $response, $app){
	$start_date=$request->param('start_date');
	$end_date=$request->param('end_date');

	if( ! $start_date ){
		$_SESSION['errors'][]='Start Date not found';
	}elseif( ! $end_date ){
		$_SESSION['errors'][]='End Date not found';
	}

		if( count($_SESSION['errors'])>0){
		
		$response->redirect( $GLOBALS['BASE_URL'] );
	}
	PSU::dbug($_SESSION['errors']);
	PSU::dbug($_POST);

});
