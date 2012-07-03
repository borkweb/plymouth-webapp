<?php
respond( '/', function( $request, $response, $app){
	//create the time arrays
	//put the current cts session into the templace
	$app->tpl->assign( 'reserve', $_SESSION['cts'] );
	//put the current session step variable into the template
	$app->tpl->assign( 'step', $_SESSION['cts']['step']);
	$app->tpl->display( 'event.tpl' );

});//end /

respond( '/agreement', function( $request, $response, $app){
	//display the reservation agreement for the user
	$app->tpl->assign( 'agreement', ReserveDatabaseAPI::get_reservation_agreement() );
	$app->tpl->display( 'agreement.tpl' );
});

respond('POST', '/confirm', function( $request, $response, $app){
	//if posted to the confirmation page check to make sure there is at least one equipment item in the session
	if(count($_SESSION['cts']['equipment'])>0){
		$_SESSION['cts']['step']=2;
		//grab the location and category data
		$app->tpl->assign( 'locations' , ReserveDatabaseAPI::locations());
		$app->tpl->assign( 'categories', ReserveDatabaseAPI::categories());
		$app->tpl->assign( 'step', $_SESSION['cts']['step']);	
		$app->tpl->assign( 'reserve', $_SESSION['cts']);
		$app->tpl->display( 'confirm.tpl');
	}else{
		//if there wasn't at least one equipment item selected, send the user back to the equipment page
		$_SESSION['errors'][]="Please select at least one item from the list of equipment.";
		$response->redirect($GLOBALS['BASE_URL'] . '/reserve/equipment');
	}
	
});//end confirm POST

respond( '/confirm/[i:id]/remove', function( $request, $response, $app){
	//removing equipment from the confirmation page
	$equipment_id=$request->id;
	//if there is an equipment id or it is 0 then unset the equipment id
	if($equipment_id || $equipment_id == 0 ){
		unset($_SESSION['cts']['equipment'][$equipment_id]);
	}

	$app->tpl->assign( 'equipment', $_SESSION['cts']['equipment']); 
	$response->redirect( $GLOBALS['BASE_URL'] . '/reserve/confirm' );
	
});

respond( 'GET','/confirm', function( $request, $response, $app){
	//when the confirmation page is "getted"
	if($_SESSION['cts']['step']==2){
		//grab the correct information
		$app->tpl->assign( 'locations' , ReserveDatabaseAPI::locations());
		$app->tpl->assign( 'categories', ReserveDatabaseAPI::categories());
		$app->tpl->assign( 'step', $_SESSION['cts']['step']);

		$app->tpl->assign( 'reserve', $_SESSION['cts']);
		$app->tpl->display( 'confirm.tpl');
	}elseif($_SESSION['cts']['step']==1){
		//if the user is not this far into the reservation process then kick them back to what process they are currently on
		$response->redirect($GLOBALS['BASE_URL'] . '/reserve/equipment');
	}else{	
		$response->redirect($GLOBALS['BASE_URL'] . '/reserve/');
	}
	
});//end confirm


respond ( '/equipment', function( $request, $response, $app){
	//user page to display the equipment
	if($_SESSION['cts']['step'] < 1){

		$response->redirect($GLOBALS['BASE_URL'] . '/reserve/');
		
	}
	$equipment_id=(int)$request->param('equipment_id');
	if($equipment_id || $equipment_id == 0){
		$app->tpl->assign( 'description',ReserveDatabaseAPI::item_info($equipment_id));
	}
	//grab all of the neccessary information
	$app->tpl->assign( 'step', $_SESSION['cts']['step']);
	$app->tpl->assign( 'equipment_id', $equipment_id);
	$app->tpl->assign( 'categories', ReserveDatabaseAPI::categories());
	$app->tpl->assign( 'equipment', $_SESSION['cts']['equipment']); 
	$app->tpl->display( 'equipment.tpl' );

});//end equipment

respond( '/equipment/add', function ($request, $response, $app){
	//when a piece of equipment is added by the user
	$equipment_id=(int)$request->equipment_id;
	if($equipment_id || $equipment_id == 0){
		$_SESSION['cts']['equipment'][]=$equipment_id;
	}

	$app->tpl->assign( 'equipment', $_SESSION['cts']['equipment']); 
	$response->redirect( $GLOBALS['BASE_URL'] . '/reserve/equipment' );

	
});//end equipment add

respond( '/equipment/[i:id]/remove', function ($request, $response, $app){
	//when a piece of equipment is removed by the user
	$equipment_id=(int)$request->id;
	if($equipment_id || $equipment_id == 0){
		//make sure that they aren't removing something that doesn't exist
		unset($_SESSION['cts']['equipment'][$equipment_id]);
	}

	$app->tpl->assign( 'equipment', $_SESSION['cts']['equipment']); 
	$response->redirect( $GLOBALS['BASE_URL'] . '/reserve/equipment' );


	
});//end equipment remove

respond( 'POST', '/event',function( $request, $response, $app){
	//this is where the information for the event page is inserted into the session

	//grab all of the neccessary parameters

	//first check to make sure that they accepted the agreement
	$agreement=$request->param('agreement');
	$data=ReserveDatabaseAPI::reservation_sanitize($request);
	$reserve = $data['reserve'];
	$app->tpl->assign('reserve', $reserve);
	
	if( !$agreement ){
		$_SESSION['errors'][]='You did not accept the agreement.';
		$app->tpl->display('event.tpl');

		//$response->redirect( $GLOBALS['BASE_URL'] . '/reserve/' );
	}else{
		if( $data['complete'] == false){//if the number of errors is > 0
			$reserve = $data['reserve'];
			$app->tpl->assign('reserve', $reserve);
				$app->tpl->display('event.tpl');
				//$response->redirect( $GLOBALS['BASE_URL'] . '/reserve/' );
			}else{
				//otherwise add all of the information from the request into the session
				$_SESSION['cts']=$data['cts_admin'];
				$_SESSION['cts']['submit_first_name']=$app->user['first_name'];
				$_SESSION['cts']['submit_last_name']=$app->user['last_name'];
				
				$_SESSION['cts']['step']="1";

				//assign a step variable so that we can keep track of where the user should be	

				$app->tpl->assign( 'step', $_SESSION['cts']['step']);
				$response->redirect($GLOBALS['BASE_URL'] . '/reserve/equipment');
				$app->tpl->display( 'equipment.tpl' );
		}//end else

	}
	 
	});//end event respond

respond ('/new', function($request, $response, $app){
	//when a user decides to do a new reservation, delete anything that is currently stored in the session
	unset($_SESSION['cts']);//delete the cts session array
	$response->redirect($GLOBALS['BASE_URL'] . '/reserve/'); 	
});//end new reservation

respond ('POST','/success', function($request, $response, $app){
	//when the user has finally confirmed their reservation
	if(count($_SESSION['cts']['equipment'])<=0){//check to make sure that there is at least one equipment item selected
	
		$_SESSION['errors'][]="Please select at least one item from the list of equipment.";
		$response->redirect($GLOBALS['BASE_URL'] . '/reserve/equipment');
	}
	//put the data in the correct form before inserting it into the database
		$currtime=date('Y-n-j G:i:s');
		$categories=ReserveDatabaseAPI::categories();

		$start_time = date("H:i:s", strtotime($_SESSION['cts']['start_time']));

		$end_time = date("H:i:s", strtotime($_SESSION['cts']['end_time']));


		$start_date=date("Y-m-d", strtotime($_SESSION['cts']['start_date']));

		$end_date=date("Y-m-d" , strtotime($_SESSION['cts']['end_date']));

		foreach(($_SESSION['cts']['equipment']) as $i){
			$name=$categories[$i]; 
			$equipment .= $name . ", ";
		}
		die($SESSION['reserve_type']);
		$data=array(	
			$_SESSION['wp_id'],
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
			"pending",
		);

		$insert_id=ReserveDatabaseAPI::insert_reservation($data);
		//mail the user and the cts staff
		CTSEmailAPI::email_user($_SESSION['cts']);
		CTSEmailAPI::email_CTS($_SESSION['cts'],$insert_id);	
		unset($_SESSION['cts']);//delete the cts session array
		$app->tpl->display( 'success.tpl' );
});//end success
