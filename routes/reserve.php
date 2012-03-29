<?php
//This file will route all of the traffic for the user side of the reservation system.
//
//reserve/event
//reseve/equipment
//reserve/confirm
//reserve/success
require_once $GLOBALS['BASE_DIR'] . '/includes/CTSemailAPI.class.php';
require_once $GLOBALS['BASE_DIR'] . '/includes/reserveDatabaseAPI.class.php';
require_once $GLOBALS['BASE_DIR'] . '/includes/CTSdatabaseAPI.class.php';

respond( '/', function( $request, $response, $app){
	$app->tpl->assign( 'hours' , array(1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,11=>11,12=>12));
	$app->tpl->assign( 'minutes', array(00=>0,05=>5,10=>10,15=>15,20=>20,25=>25,30=>30,35=>35,40=>40,45=>45,50=>50,55=>55));
	$app->tpl->assign( 'ampm' , array("AM"=>"AM","PM"=>"PM"));
	$app->tpl->assign( 'locations' , reserveDatabaseAPI::locations());
	$app->tpl->assign( 'reserve', $_SESSION['cts'] );
	$app->tpl->assign( 'step', $_SESSION['cts']['step']);
	$app->tpl->display( 'event.tpl' );

});//end /

respond( '/agreement', function( $request, $response, $app){
	$app->tpl->assign( 'agreement', "This is the agreement" );
	$app->tpl->display( 'agreement.tpl' );
});

respond('POST', '/confirm', function( $request, $response, $app){
	if(count($_SESSION['cts']['equipment'])>0){
		$_SESSION['cts']['step']="2";
		PSU::dbug($_SESSION['username']);	
		PSU::dbug($_SESSION['cts']);

		$app->tpl->assign( 'locations' , reserveDatabaseAPI::locations());
		$app->tpl->assign( 'categories', reserveDatabaseAPI::categories());
		$app->tpl->assign( 'step', $_SESSION['cts']['step']);	
		$app->tpl->assign( 'reserve', $_SESSION['cts']);
		$app->tpl->display( 'confirm.tpl');
	}else{
		$_SESSION['errors'][]="Please select at least one item from the list of equipment.";
		$response->redirect($GLOBALS['BASE_URL'] . '/reserve/equipment');
	}
	
});//end confirm POST

respond( '/confirm/[i:id]/remove', function( $request, $response, $app){
	$equipment_id=$request->id;
	if($equipment_id || $equipment_id =="0" ){
		unset($_SESSION['cts']['equipment'][$equipment_id]);
	}

	$app->tpl->assign( 'equipment', $_SESSION['cts']['equipment']); 
	$response->redirect( $GLOBALS['BASE_URL'] . '/reserve/confirm' );
	
});

respond( '/confirm', function( $request, $response, $app){
	if($_SESSION['cts']['step']==2){
		$app->tpl->assign( 'locations' , reserveDatabaseAPI::locations());
		$app->tpl->assign( 'categories', reserveDatabaseAPI::categories());
		$app->tpl->assign( 'step', $_SESSION['cts']['step']);

		$app->tpl->assign( 'reserve', $_SESSION['cts']);
		$app->tpl->display( 'confirm.tpl');
	}elseif($_SESSION['cts']['step']==1){
		$response->redirect($GLOBALS['BASE_URL'] . '/reserve/equipment');
	}else{	
		$response->redirect($GLOBALS['BASE_URL'] . '/reserve/');
	}
	
});//end confirm


respond ( '/equipment', function( $request, $response, $app){
	if($_SESSION['cts']['step']>=1){

	PSU::db('cts')->debug=true;
		PSU::dbug($_SESSION['cts']);	
		$equipment_id=$request->param('equipment_id');
		if($equipment_id || $equipment_id == "0"){
			$app->tpl->assign( 'description',reserveDatabaseAPI::itemInfo($equipment_id));
		}

		$app->tpl->assign( 'step', $_SESSION['cts']['step']);
		$app->tpl->assign( 'equipment_id', $equipment_id);
		$app->tpl->assign( 'categories', reserveDatabaseAPI::categories());
		$app->tpl->assign( 'equipment', $_SESSION['cts']['equipment']); 
		$app->tpl->display( 'equipment.tpl' );
	}elseif($_SESSION['cts']['step']==NULL){
		$response->redirect($GLOBALS['BASE_URL'] . '/reserve/');
	}
	

});//end equipment

respond( '/equipment/add', function ($request, $response, $app){
	$equipment_id=$request->equipment_id;
	if($equipment_id || $equipment_id =="0" ){
		$_SESSION['cts']['equipment'][]=$equipment_id;
	}

	$app->tpl->assign( 'equipment', $_SESSION['cts']['equipment']); 
	$response->redirect( $GLOBALS['BASE_URL'] . '/reserve/equipment' );

	
});//end equipment add

respond( '/equipment/[i:id]/remove', function ($request, $response, $app){
	$equipment_id=$request->id;
	if($equipment_id || $equipment_id =="0" ){
		unset($_SESSION['cts']['equipment'][$equipment_id]);
	}

	$app->tpl->assign( 'equipment', $_SESSION['cts']['equipment']); 
	$response->redirect( $GLOBALS['BASE_URL'] . '/reserve/equipment' );


	
});//end equipment remove

respond( 'POST', '/event',function( $request, $response, $app){

	//required parameters
	$curr_page="/";
	$first_name=$request->param('first_name');
	$last_name=$request->param('last_name');
	$phone=$request->param('phone');
	$secondary_phone=$request->param('secondary_phone');
	$email=$request->param('email');
	$submit_first_name=$app->user['first_name'];
	$submit_last_name=$app->user['last_name'];

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
	$startminute=sprintf("%02d",$startminute);
	$startampm=$request->param('startampm');
	$start_time=$starthour . ':' . $startminute . ' ' . $startampm;

	$endhour=$request->param('endhour');
	$endminute=$request->param('endminute');
	$endminute=sprintf("%02d",$endminute);
	$endampm=$request->param('endampm');
	$end_time=$endhour . ':' . $endminute . ' ' . $endampm;
	$agreement=$request->param('agreement');


	if( ! $first_name ){ //if there is no first name
		$_SESSION['errors'][]='First name not found'; //throw error
	}elseif( ! $last_name ){ //if there is no last name
		$_SESSION['errors'][]='Last name not found'; //throw error
	}elseif( ! $phone ){ //if there is no phone number
		$_SESSION['errors'][]='Phone number not found'; //throw error
	}elseif( !filter_var($phone, FILTER_VALIDATE_INT)){
	    $_SESSION['errors'][]='Phone number incorrect';	
	}elseif( $secondary_phone ){
		if( !filter_var($secondary_phone, FILTER_VALIDATE_INT) ){
			$_SESSION['errors'][]='Secondary phone incorrect';
		}
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
	}elseif( ! $agreement ){
	 	$_SESSION['errors'][]='You did not accept the agreement.';   
	}	//end elseif


	if( count($_SESSION['errors'])>0 ){//if the number of errors is > 0
		$response->redirect( $GLOBALS['BASE_URL'] . '/reserve/' );
	}else{
		$_SESSION['cts']['first_name']=$first_name;
		$_SESSION['cts']['last_name']=$last_name;
		$_SESSION['cts']['username']=$_SESSION['username'];
		$_SESSION['cts']['phone']=$phone;
		$_SESSION['cts']['submit_first_name']=$submit_first_name;
		$_SESSION['cts']['submit_last_name']=$submit_last_name;
		
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
		$_SESSION['cts']['starthour']=$starthour;
		$_SESSION['cts']['startminute']=$startminute;
		$_SESSION['cts']['startampm']=$startampm;
		$_SESSION['cts']['end_time']=$end_time;
		$_SESSION['cts']['endhour']=$endhour;
		$_SESSION['cts']['endminute']=$endminute;
		$_SESSION['cts']['endampm']=$endampm;
		$_SESSION['cts']['reserve_type']=$reserve_type;
		$_SESSION['cts']['step']="1";

		$app->tpl->assign( 'step', $_SESSION['cts']['step']);

		$response->redirect($GLOBALS['BASE_URL'] . '/reserve/equipment');
		$app->tpl->display( 'equipment.tpl' );
	}//end else
});//end event respond

respond ('/new', function($request, $response, $app){
	unset($_SESSION['cts']);//delete the cts session array
	$response->redirect($GLOBALS['BASE_URL'] . '/reserve/'); 	
});//end new reservation

respond ('POST','/success', function($request, $response, $app){
	\PSU::db('cts')->debug=true;
	\PSU::dbug($_SESSION['cts']);
	if(count($_SESSION['cts']['equipment'])>0){
		$currtime=date('Y-n-j G:i:s');
		$categories=reserveDatabaseAPI::categories();

		$start_time = date("H:i:s", strtotime($_SESSION['cts']['start_time']));

		$end_time = date("H:i:s", strtotime($_SESSION['cts']['end_time']));


		$start_date=date("Y-m-d", strtotime($_SESSION['cts']['start_date']));

		$end_date=date("Y-m-d" , strtotime($_SESSION['cts']['end_date']));

		foreach(($_SESSION['cts']['equipment']) as $i){
			$name=$categories[$i]; 
			$equipment .= $name . ", ";
		}
		reserveDatabaseAPI::insertReservation(
			//need to use binding in the SQL
			//VALUES(?,?)
			$_SESSION['cts']['last_name'],
			$_SESSION['cts']['first_name'],
			$_SESSION['cts']['phone'],
			$_SESSION['cts']['email'],
			$currtime,
			$start_date,
			$start_time,
			$end_date,
			$end_time,
			$_SESSION['cts']['comments'],
			$_SESSION['cts']['location'],
			$_SESSION['cts']['room'],
			$_SESSION['cts']['title'],
			$_SESSION['cts']['reserve_type'],
			$equipment,
			"pending"
		);
		$insert_id=mysql_insert_id();
		CTSemailAPI::emailUser($_SESSION['cts']);
		CTSemailAPI::emailCTS($_SESSION['cts'],$insert_id);	
		//unset($_SESSION['cts']);//delete the cts session array
		$app->tpl->display( 'success.tpl' );
	}else{
		$_SESSION['errors'][]="Please select at least one item from the list of equipment.";
		$response->redirect($GLOBALS['BASE_URL'] . '/reserve/equipment');
	}
});//end success
