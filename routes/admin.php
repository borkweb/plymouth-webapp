<?php
//this page will deal with all of the routing for the admin pages
//
//admin/equipment
//admin/reservation
require_once $GLOBALS['BASE_DIR'] . '/includes/CTSDatabaseAPI.class.php';
require_once $GLOBALS['BASE_DIR'] . '/includes/ReserveDatabaseAPI.class.php';

respond ( function( $request, $response, $app ){
	//this checks every admin page and makes sure the user is a manager, cts staff or helpdesk staff
	if(ReserveDatabaseAPI::user_level() > 2){
		die('You do not have permission to view this page.');
	}

});

respond('/admincp', function( $request, $response, $app){
	//page with the admin control panel links on it

	$app->tpl->display( 'admin-control-panel.tpl' );
});//admin cp

respond('/admincp/equipment', function( $request, $response, $app){
	//page with the equipment form options
	$app->tpl->assign( 'categories', ReserveDatabaseAPI::get_form_options());
	$app->tpl->display( 'admin-form-options.tpl' );

});//admincp equipment page

respond('/admincp/subitems', function( $request, $response, $app){
	//page with the subitem form options
	$app->tpl->assign( 'subitems', ReserveDatabaseAPI::get_subitems());
	$app->tpl->display( 'admin-subitems.tpl' );
});//admincp equipment page

respond('POST', '/admincp/subitems/add', function( $request, $response, $app){
	//when the administrator is trying to create a new subitem
	$name=$request->param('new_subitem');
	$name=filter_var($name, FILTER_SANITIZE_STRING);
	ReserveDatabaseAPI::insert_subitem($name);
	$response->redirect($GLOBALS['BASE_URL'] . '/admin/admincp/subitems' );
});//admin subitem add

respond('/admincp/subitems/[i:id]/remove', function( $request, $response, $app){
	//when the administrator is trying to delete a sub item
	$subitem_id=$request->id;
	ReserveDatabaseAPI::delete_subitem($subitem_id);
	$response->redirect($GLOBALS['BASE_URL'] . '/admin/admincp/subitems');
});//admin subitem 

respond('POST', '/admincp/equipment/add', function( $request, $response, $app){
	//when the administrator is trying to add a new equipment item to the form option list
	$category = $request->param('new_equipment');
	$category=filter_var($category, FILTER_SANITIZE_STRING);
	$description=$request->param('description');
	$description=filter_var($description, FILTER_SANITIZE_STRING);
	ReserveDatabaseAPI::insert_form_options($category,$description);
	$response->redirect($GLOBALS['BASE_URL'] . '/admin/admincp/equipment' );
});//admin equipment add

respond('/admincp/equipment/[i:id]/remove', function( $request, $response, $app){
	//when the administrator is trying to remove a piece of equipment from the form options list
	$equipment_id = $request->id;
	ReserveDatabaseAPI::delete_equipment($equipment_id);
	$response->redirect($GLOBALS['BASE_URL'] . '/admin/admincp/equipment');
});//admin equipment

respond('/admincp/announcements', function( $request, $response, $app ){
	//the admin page for added and editing announcements
	$app->tpl->assign('announcements', ReserveDatabaseAPI::get_announcements());
	$app->tpl->display('announcements.tpl');

});//admin announcements

respond('/admincp/announcements/add', function( $request, $response, $app ){
	//when the administrator is trying to add a new announcement to the list of avilable
	$message=$request->param('message');
	$message = PSU::makeClean($message);
	ReserveDatabaseAPI::insert_announcement($message);
	$response->redirect($GLOBALS['BASE_URL'] . '/admin/admincp/announcements');

});//admin accouncements/add

respond('/admincp/announcements/[i:id]/delete', function( $request, $response, $app){
	//when the administrator is trying to delete an announcement
	$announcement_id=$request->id;
	ReserveDatabaseAPI::delete_announcement($announcement_id);
	$response->redirect($GLOBALS['BASE_URL'] . '/admin/admincp/announcements');
});//admin equipment

respond('/admincp/announcements/save', function( $request, $response, $app){
	//when the administrator is trying to save which announcements are going to be displayed
	$announcements=ReserveDatabaseAPI::get_announcements();
	foreach($announcements as $key => $announcement){
	//iterate through all of the announcements
	//if the announcement is found in the post array than set the change variable to yes, otherwise change it to no
		if($_POST[$key]){
			ReserveDatabaseAPI::change_announcement($key, "yes");
		}else{
			ReserveDatabaseAPI::change_announcement($key, "no");
		}	
	}
	$response->redirect($GLOBALS['BASE_URL'] . '/admin/admincp');
});//admin equipment


respond('/admincp/announcements/[i:id]/edit', function( $request, $response, $app){
	//when the administrator is trying to edit an existing announcement
	$announcement_id=$request->id;
	$app->tpl->assign('announcement',ReserveDatabaseAPI::get_announcement($announcement_id));
	$app->tpl->display( 'announcement-edit.tpl' );
});//admin equipment

respond('/admincp/announcements/[i:id]/edit/save', function( $request, $response, $app){
	//when the edited announcement is saved into the database
	$announcement_id=$request->id;
	$message=stripslashes($request->param('message'));
	ReserveDatabaseAPI::edit_announcement($announcement_id, $message);
	$response->redirect($GLOBALS['BASE_URL'] . '/admin/admincp/announcements');
});//admin equipment

respond('/admincp/agreement', function( $request, $response, $app ){
	//display the agreement to the administrator for editing
	$app->tpl->assign( 'agreement',ReserveDatabaseAPI::get_reservation_agreement());
	$app->tpl->display('reservation-agreement.tpl');
});//admin agreement

respond('/admincp/agreement/change', function( $request, $response, $app ){
	//when the administrator is submiting the agreement for editing
	$agreement=$request->param('agreement');
	ReserveDatabaseAPI::change_reservation_agreement($agreement);
	$response->redirect($GLOBALS['BASE_URL'] . '/admin/admincp');

});//admin agreement change

respond('/admincp/buildings', function( $request, $response, $app ){
	//show the list of buildings to the administrator for editing
	$app->tpl->assign( 'buildings' , ReserveDatabaseAPI::locations());

	$app->tpl->display( 'buildings.tpl' );
	//$response->redirect($GLOBALS['BASE_URL'] . '/admin/admincp');

});//admin buildings

respond('/admincp/buildings/add', function( $request, $response, $app ){
	//when the administrator is trying to add a new building
	$building_name=$request->param('building_name');
	$building_name=filter_var($building_name, FILTER_SANITIZE_STRING);
	ReserveDatabaseAPI::add_building($building_name);
	$response->redirect($GLOBALS['BASE_URL'] . '/admin/admincp/buildings');

});//admin buildings add

respond('/admincp/buildings/[i:id]/delete', function( $request, $response, $app ){
	//when the adminstrator is trying to delete a building from the list
	$building_idx=$request->id;
	ReserveDatabaseAPI::delete_building($building_idx);
	$response->redirect($GLOBALS['BASE_URL'] . '/admin/admincp/buildings');

});//admin buildings add

respond('/equipment/[i:id]?/filter/', function( $request, $response, $app) {
	//when it is filterd by type
	if($request->id){
		//if you are adding equipment to a reservation
		$reservation_idx=$request->id;
		//--------------------FOR BEST FIT---------------
		/*
		$reservation= ReserveDatabaseAPI::by_id($reservation_idx);

		$start_date=$reservation[$reservation_idx]['start_date'];
		$start_date=date('Y-m-d',strtotime($start_date));

		$end_date=$reservation[$reservation_idx]['end_date'];
		$end_date=date('Y-m-d',strtotime($end_date));
		$dates=array($start_date,$end_date);

		$fixed_start_date=ReserveDatabaseAPI::fix_date($start_date);
		$fixed_end_date=ReserveDatabaseAPI::fix_date($end_date);
		$app->tpl->assign('title',"Reservations from $fixed_start_date to $fixed_end_date");
		$equipment_reservations=CTSDatabaseAPI::equipment_by_date($dates);

		foreach($equipment_reservations as $glpi_id => $item){
			$glpi_ids[]=$glpi_id;
			foreach($item['reservations'] as $glpi_reservation){
				PSU::dbug($glpi_reservation);
			}
		}

		//----------------------------------------------------
		 */

		$_SESSION['warnings'][]='You are adding equipment to reservation index ' . '<a target="blank" href="'. $GLOBALS['BASE_URL'] . '/admin/reservation/search/id/' . $reservation_idx . '">' . $reservation_idx . '</a>';
		$app->tpl->assign('reservation_idx', $reservation_idx);
	}

	//filter the equipment 
	$app->tpl->assign( 'search_term', $_GET['search_term'] );
	$app->tpl->assign( 'by_model', CTSDatabaseAPI::by_model( $_GET ));
	$app->tpl->assign( 'models', CTSDatabaseAPI::model_keys( $_GET ));
	$app->tpl->assign( 'types', CTSDatabaseAPI::types( $_GET ));
	PSU::dbug(CTSDatabaseAPI::by_model( $_GET ));

	$app->tpl->display('glpi-equipment.tpl');

});//end equipment/filter

respond('/equipment/[i:id]?/item/model/[:model]/?', function( $request, $response, $app) {
	if($request->id){
		$reservation_idx=$request->id;
		$_SESSION['warnings'][]='You are adding equipment to reservation index ' . '<a target="blank" href="'. $GLOBALS['BASE_URL'] . '/admin/reservation/search/id/' . $reservation_idx . '">' . $reservation_idx . '</a>';
		$app->tpl->assign('reservation_idx', $reservation_idx);
	}
	$models=CTSDatabaseAPI::by_model( array('model' =>array( $request->model ), ));
	$app->tpl->assign('model_info', $models[ $request->model ]);
	$app->tpl->display('model.tpl');

});//end equipment/filter/model

respond('/equipment/[i:id]?/item/[:glpi_id]/[:action]?', function( $request, $response, $app) {
	if($request->id){
		$reservation_idx=$request->id;
		$_SESSION['warnings'][]='You are adding equipment to reservation index ' . '<a target="blank" href="'. $GLOBALS['BASE_URL'] . '/admin/reservation/search/id/' . $reservation_idx . '">' . $reservation_idx . '</a>';
		$app->tpl->assign('reservation_idx', $reservation_idx);
	}
	
	$glpi_id=$request->glpi_id;
	
	$data = ReserveDatabaseAPI::search($request);
	if($data['redirect_url']){
		//if there was a redirect url in the data, redirect the user there
		$response->redirect($data['redirect_url']);
	}
	//otherwise assign the title and reservations
	$app->tpl->assign('title', $data['title']);
	//we only need the first two dates
	$start_date=$data['dates'][0];
	$end_date=$data['dates'][1];

	$dates=array(
		$start_date,
		$end_date,
	);
	//-----------------------GANTT VIEW
	include 'jpgraph/jpgraph.php';
	include 'jpgraph/jpgraph_gantt.php';
	//graph code found in loan_sched.html in /cts
	$graph = new GanttGraph(900);
	$graph->SetFrame(true, 'darkblue', 2);
	$graph->scale->day->SetStyle(5);
	$graph->scale->day->SetFont(FF_FONT1,FS_BOLD,12);
	$graph->SetMargin(-1,-1,-1,-1);
	$graph->ShowHeaders( GANTT_HDAY );
	$graph->scale->day->SetStyle(5);
	$graph->hgrid->Show();
	//$graph->scale->day->SetFont(15);
	$graph->hgrid->SetRowFillColor( '#B5BD88@0.5');
	$graph->SetDateRange($start_date,$end_date);

	$equipment_reservations=CTSDatabaseAPI::equipment_by_date($dates);

		
			//iterate through the ids and check to see if they are in the equipment array
			if($equipment_reservations[$glpi_id]){
				//put the information in a new array
				$gantt_data[$glpi_id]=$equipment_reservations[$glpi_id]['reservations'];
			}else{
				$gantt_data[$glpi_id][]=array(
					'reservation_idx'=>NULL, 
					'start_date' => '0000-00-00', 
					'end_date' => '0000-00-00',
				);
			}
		if($gantt_data){
			foreach($gantt_data as $id => $item){
				foreach($item as $reservation){
					if($id != $current_id){
						//this makes it so that it will only add the item on the same line if it has multiple dates
						$counter++;
						$current_id=$id;
					}
					$activity= new GanttBar($counter, $id, $reservation['start_date'],$reservation['end_date']);
					$activity->SetCSIMTarget($GLOBALS['BASE_URL'] . '/admin/reservation/search/id/' . $reservation['reservation_idx'], "Reservation ID: " .$reservation['reservation_idx']);
					$graph->Add($activity);
					
				}
			}
		}

		
		
		//DRAW GRAPH
		$graph->SetDateRange($start_date,$end_date);

		$is_rendering_image = (boolean) $_GET['_jpg_csimd'];

		// if jpgraph isn't rendering the image, it is instead rendering the image map.  
		// turn on outputbuffering to capture the html
		if( ! $is_rendering_image ) {
			ob_start();
		}//end if

		// generate the graph (this dumps either the HTML OR the image contents)
		$graph->StrokeCSIM();

		// if jpgraph is not rendering the image, pull the HTML from the buffer and assign to a variable
		// for template assignment magic
		if( ! $is_rendering_image ) {
			$gantt_chart = ob_get_clean();
		}//end if

		$app->tpl->assign('gantt_chart', $gantt_chart);

	//grab the items
	//------------------------------------------------------------------
	$count=CTSDatabaseAPI::count($glpi_id);
	$app->tpl->assign('count', $count);
	$app->tpl->assign('glpi_id', $glpi_id);	
	$app->tpl->display('single-gantt.tpl');

});//end equipment/id/item
respond('/equipment/[i:id]?/item/model/[:model]/list/?/[:action]?', function( $request, $response, $app) {
	//this is where the gantt view will be
	if($request->id){
		$reservation_idx=$request->id;
		$_SESSION['warnings'][]='You are adding equipment to reservation index ' . '<a target="blank" href="'. $GLOBALS['BASE_URL'] . '/admin/reservation/search/id/' . $reservation_idx . '">' . $reservation_idx . '</a>';
		$app->tpl->assign('reservation_idx', $reservation_idx);
	}
	$data = ReserveDatabaseAPI::search($request);
	if($data['redirect_url']){
		//if there was a redirect url in the data, redirect the user there
		$response->redirect($data['redirect_url']);
	}
	//otherwise assign the title and reservations
	$app->tpl->assign('title', $data['title']);
	//we only need the first two dates
	$start_date=$data['dates'][0];
	$end_date=$data['dates'][1];

	$dates=array(
		$start_date,
		$end_date,
	);

	$items=CTSDatabaseAPI::by_model( array('model' =>array( $request->model ), ));
	$items=$items[ $request->model]['machines'];
	foreach($items as $item){
		//iterate through each item, grab the count and add it to the item
		$glpi_id=$item['psu_name'];
		$glpi_ids[]=$glpi_id;//get the list of just the glpi_ids for the gannt view
		$count=CTSDatabaseAPI::count($glpi_id);
		$item['count']=$count;
		//hold all of the items in an array
		$temp_items[]=$item;
	}
	//---------------------------------------------------------------
	//                     Gantt view
	//---------------------------------------------------------------
	include 'jpgraph/jpgraph.php';
	include 'jpgraph/jpgraph_gantt.php';
	//graph code found in loan_sched.html in /cts
	$graph = new GanttGraph(900);
	$graph->SetFrame(true, 'darkblue', 2);
	$graph->scale->day->SetStyle(5);
	$graph->scale->day->SetFont(FF_FONT1,FS_BOLD,12);
	$graph->SetMargin(-1,-1,-1,-1);
	$graph->ShowHeaders( GANTT_HDAY );
	$graph->scale->day->SetStyle(5);
	$graph->hgrid->Show();
	//$graph->scale->day->SetFont(15);
	$graph->hgrid->SetRowFillColor( '#B5BD88@0.5');
	$graph->SetDateRange($start_date,$end_date);

	$equipment_reservations=CTSDatabaseAPI::equipment_by_date($dates);

		

		foreach($glpi_ids as $id){
			//iterate through the ids and check to see if they are in the equipment array
			if($equipment_reservations[$id]){
				//put the information in a new array
				$gantt_data[$id]=$equipment_reservations[$id]['reservations'];
			}else{
				$gantt_data[$id][]=array('reservation_idx'=>NULL, 'start_date' => '0000-00-00', 'end_date' => '0000-00-00');
			}
		}
		if($gantt_data){
			foreach($gantt_data as $id => $item){
				foreach($item as $reservation){
					if($id != $current_id){
						//this makes it so that it will only add the item on the same line if it has multiple dates
						$counter++;
						$current_id=$id;
					}
					$activity= new GanttBar($counter, $id, $reservation['start_date'],$reservation['end_date']);
					$activity->SetCSIMTarget($GLOBALS['BASE_URL'] . '/admin/reservation/search/id/' . $reservation['reservation_idx'], "Reservation ID: " .$reservation['reservation_idx']);
					$graph->Add($activity);
					
				}
			}
		}

		
		
		//DRAW GRAPH
		$graph->SetDateRange($start_date,$end_date);

		$is_rendering_image = (boolean) $_GET['_jpg_csimd'];

		// if jpgraph isn't rendering the image, it is instead rendering the image map.  
		// turn on outputbuffering to capture the html
		if( ! $is_rendering_image ) {
			ob_start();
		}//end if

		// generate the graph (this dumps either the HTML OR the image contents)
		$graph->StrokeCSIM();

		// if jpgraph is not rendering the image, pull the HTML from the buffer and assign to a variable
		// for template assignment magic
		if( ! $is_rendering_image ) {
			$gantt_chart = ob_get_clean();
		}//end if

		$app->tpl->assign('gantt_chart', $gantt_chart);

		//PSU::dbug($gantt_data);
	//grab the items
	//------------------------------------------------------------------
	$app->tpl->assign('reservations', $reservations);
	$app->tpl->assign('items', $temp_items);
	$app->tpl->display('item-list.tpl');

});//end equipment/filter/model

respond('/equipment/[i:id]?', function( $request, $response, $app) {
	if($request->id){
		$reservation_idx=$request->id;
		$_SESSION['warnings'][]='You are adding equipment to reservation index ' . '<a target="blank" href="'. $GLOBALS['BASE_URL'] . '/admin/reservation/search/id/' . $reservation_idx . '">' . $reservation_idx . '</a>';
		$app->tpl->assign('reservation_idx', $reservation_idx);
	}
	//display the equipment list to teh administrator
	if( $_GET['search_term'] ){
		$app->tpl->assign( 'search_term', $_GET['search_term'] );
		$app->tpl->assign( 'models',CTSDatabaseAPI::model_keys() );//Only show the models if a search as been done, this cuts down on the large list.
	}	
	$app->tpl->assign( 'models', $models );

	$app->tpl->assign( 'types', CTSDatabaseAPI::types() );
	$app->tpl->display('glpi-equipment.tpl');

});//end equipment
respond( '/equipment/add-id', function( $request, $response, $app ){
	$reservation_idx=$request->param( 'reservation_idx' );
	$reservation_idx=filter_var($reservation_idx, FILTER_SANITIZE_NUMBER_INT);
	//checks to see if this is a reservation or not
	if(ReserveDatabaseAPI::check_reservation($reservation_idx)){
		//if it is
		$response->redirect($GLOBALS['BASE_URL'] . '/admin/equipment/' . $reservation_idx);
	}else{
		//if it is not a reservation
		$_SESSION['errors'][]='That is not a valid reservation id.';
		$response->redirect($GLOBALS['BASE_URL'] . '/admin/equipment');
	}


});

respond('/equipment/by-week/[:action]?', function( $request, $response, $app){

	$data = ReserveDatabaseAPI::search($request);
	if($data['redirect_url']){
		//if there was a redirect url in the data, redirect the user there
		$response->redirect($data['redirect_url']);
	}
	//otherwise assign the title and reservations
	$app->tpl->assign('title', $data['title']);
	$dates=$data['dates'];
	$reservations=CTSDatabaseAPI::reservation_information($dates);
	$app->tpl->assign('reservations', $reservations);
	$app->tpl->display('equipment-by-week.tpl');

});//equipment by week


respond('/mypage', function( $request, $response, $app){
	//display mypage for CTS technicians to view their assignments for the day
	$app->tpl->assign( 'locations' , ReserveDatabaseAPI::locations());
	$wp_id=$_SESSION['wp_id'];
	$start_date=date('Y-m-d');
	$count=count(ReserveDatabaseAPI::by_status("pending"));
	$app->tpl->assign( 'count', $count );
	$app->tpl->assign( 'reservation' , ReserveDatabaseAPI::by_user_date($start_date, $wp_id));
	$app->tpl->display( 'mypage.tpl' );

});//mypage


respond('/reservation' , function( $request, $response, $app){
	//the page to display todays reservation to the cts and helpdesk staff
	//$app->tpl->assign( 'locations' , ReserveDatabaseAPI::locations());
	$data = ReserveDatabaseAPI::search($request);
	if($data['redirect_url']){
		//if there was a redirect url in the data, redirect the user there
		$response->redirect($data['redirect_url']);
	}
	//otherwise assign the title and reservations
	$app->tpl->assign('title', $data['title']);
	$app->tpl->assign('reservation', $data['reservations']);


	$app->tpl->display( 'reservation.tpl' );

});//end reservation

respond('/reservation/id/[i:id]/userpickup', function( $request, $response, $app ){
	//when adding a user that picked up the equipment from the helpdesk
	$reservation_idx=$request->id;
	$user_id=$request->USER_ID;
	if(ReserveDatabaseAPI::check_user_id($user_id)){//check to make sure it is less than 9 digits
		ReserveDatabaseAPI::add_user_pickup($reservation_idx,$user_id);
	}
	$response->redirect( $GLOBALS['BASE_URL'] . '/admin/reservation/search/id/' . $reservation_idx);


});//reservation id userpickup

respond('/reservation/id/[i:id]/userdropoff', function( $request, $response, $app ){
	//when adding a user that dropped off the equipment at the helpdesk
	$reservation_idx=$request->id;
	$user_id=$request->USER_ID;
	if(ReserveDatabaseAPI::check_user_id($user_id)){//check to make sure it is less than 9 digits
		ReserveDatabaseAPI::add_user_dropoff($reservation_idx,$user_id);
	}
	$response->redirect( $GLOBALS['BASE_URL'] . '/admin/reservation/search/id/' . $reservation_idx);


});//reservation userdropoff


respond('/reservation/[i:id]/edit',function( $request, $response, $app){
	//when the reservation is edited by the adminstrator. This page works the same as in routes/reserve
	//required parameters
	$reservation_idx=$request->id;	
	$data=ReserveDatabaseAPI::reservation_sanitize($request);

	if( $data['complete'] == false ){//if the number of errors is > 0
		$response->redirect( $GLOBALS['BASE_URL'] . '/admin/reservation/search/id/' . $reservation_idx .'/edit');
	}else{
		ReserveDatabaseAPI::update_reservation($data['cts_admin']);
		$response->redirect( $GLOBALS['BASE_URL'] . '/admin/reservation/search/id/' . $reservation_idx);
	}//end else


});//edit reservation

respond('/reservation/id/[i:id]/status', function( $request, $response, $app){
	//when the staff member is trying to change the status of the loan
	$reservation_idx=$request->id;
	$status=$request->param('status');
	if($status=="approved"){
		CTSemailAPI::email_user_approved($reservation_idx);
	}elseif($status=="cancelled"){
		CTSemailAPI::email_user_cancelled($reservation_idx);
	}
	ReserveDatabaseAPI::change_status($reservation_idx, $status);
	$response->redirect($GLOBALS['BASE_URL'] . '/admin/reservation/search/id/'.$reservation_idx);	
});//chnage status

respond('/reservation/id/[i:id]/pickup', function( $request, $response, $app){
	//when the administrator is assigning a technician to pickup the equipment
	$reservation_idx=$request->id;
	$user=$request->param('assigned_tech_pickup');
	$user=filter_var($user,FILTER_SANITIZE_STRING);
	ReserveDatabaseAPI::change_pickup($reservation_idx, $user);
	$response->redirect($GLOBALS['BASE_URL'] . '/admin/reservation/search/id/'.$reservation_idx);	
});//chnage status

respond('/reservation/id/[i:id]/dropoff', function( $request, $response, $app){
	//when the administrator is assigning a technician to dropoff the equipment
	$reservation_idx=$request->id;
	$user=$request->param('assigned_tech_dropoff');
	$user=filter_var($user,FILTER_SANITIZE_STRING);
	ReserveDatabaseAPI::change_dropoff($reservation_idx, $user);
	$response->redirect($GLOBALS['BASE_URL'] . '/admin/reservation/search/id/'.$reservation_idx);	
});//chnage status


respond('/reservation/id/[i:id]/priority', function( $request, $response, $app){
	//when the staff member is trying to change the priority of a loan
	$reservation_idx=$request->id;
	$priority=$request->param('priority');
	$priority=filter_var($priority, FILTER_SANITIZE_STRING);
	ReserveDatabaseAPI::change_priority($reservation_idx, $priority);
	$response->redirect($GLOBALS['BASE_URL'] . '/admin/reservation/search/id/'.$reservation_idx);	
});//change priority

respond('/reservation/id/[i:id]/equipment', function( $request, $response, $app){
	//when a piece of equipment is added to a loan
	$reservation_idx=$request->id;
	$GLPI_ID=$request->param('GLPI_ID');
	$GLPI_ID=filter_var($GLPI_ID, FILTER_SANITIZE_STRING);
	
	$GLPI_ID=ReserveDatabaseAPI::format_glpi($GLPI_ID);

	if(count($_SESSION['errors'])<=0){
		if(ReserveDatabaseAPI::check_glpi($GLPI_ID)){
			//if the GLPI_ID is found then add it
			ReserveDatabaseAPI::add_equipment($reservation_idx, $GLPI_ID);
		}else{
			//otherwise show an error
			$_SESSION['errors'][]="GLPI ID not found.";
		}
	}
	$response->redirect($GLOBALS['BASE_URL'] . '/admin/reservation/search/id/'.$reservation_idx);	
});//add equipment manually

respond('/reservation/equipment/[i:id]/remove/[i:key]', function( $request, $response, $app){
	//when removing a piece of equipment from a loan
	//the key is used to delete the specific reservation_equipment field in the database where the
	//id is used as the reservation for redirecting
	$reservation_idx=$request->id;
	$equipment_reservation_idx=$request->key;
	ReserveDatabaseAPI::remove_equipment($equipment_reservation_idx);	
	$response->redirect($GLOBALS['BASE_URL'] . '/admin/reservation/search/id/'.$reservation_idx);	
});//add equipment manually


respond('/reservation/[i:id]/subitem/add', function( $request, $response, $app){
	//when the staff member is adding a subitem to a loan
	$reservation_idx=$request->id;
	$subitem_id=$request->param('subitems');
	$subitem_id=filter_var($subitem_id, FILTER_SANITIZE_STRING);
	ReserveDatabaseAPI::insert_reservation_subitem($reservation_idx,$subitem_id);
	$response->redirect($GLOBALS['BASE_URL'] . '/admin/reservation/search/id/'.$reservation_idx);	

});//reservation id subitem add

respond('/reservation/subitem/remove/[i:id]/[i:key]', function( $request, $response, $app){
	//when the staff member is removing a subitem from a loan
	$id=$request->id;
	$reservation_idx=$request->key;
	ReserveDatabaseAPI::delete_reserve_subitem($id);
	$response->redirect($GLOBALS['BASE_URL'] . '/admin/reservation/search/id/'.$reservation_idx);	

}); //reservation subitem remove

respond('/reservation/search/id/[i:id]' , function( $request, $response, $app){
	//when searching for a specific reservation by ID
	$reservation_idx=$request->id;
	ReserveDatabaseAPI::init_all_reservation_info($app,$reservation_idx);
	$app->tpl->display( 'single-reservation.tpl' );

});//end reservation/search/

respond('/reservation/id/[i:id]/print' , function( $request, $response, $app){
	//this page is just like single reservation, but has a cleaner look and no editing for easy printing
	$reservation_idx=$request->id;
	ReserveDatabaseAPI::init_all_reservation_info($app,$reservation_idx);
	$app->tpl->display( 'print.tpl' );

});//end reservation/search/


respond('/reservation/search/id/[i:id]/[a:action]' , function( $request, $response, $app){
	//this is used to edit or delete a single reservation
	if($request->action=="edit"){//if the action is to edit the current reservation
		if(ReserveDatabaseAPI::user_level()>1){
			die('You do not have permission to edit a reservation.');
		}
			$editable=true;
			$app->tpl->assign( 'editable', $editable);
			$reservation_idx=$request->id;
			$app->tpl->assign( 'messages', ReserveDatabaseAPI::get_messages($reservation_idx));
			$app->tpl->assign( 'equipment', ReserveDatabaseAPI::get_equipment($reservation_idx));
			$app->tpl->assign( 'locations' , ReserveDatabaseAPI::locations());
			$app->tpl->assign( 'reservation_idx', $reservation_idx);
			$reservation=ReserveDatabaseAPI::by_id($reservation_idx);

			//this section takes the date stored in the reservation and extracts the
			//hour, minute and Ante meridiem and Post meridiem
			$starthour=date("g",strtotime($reservation[$reservation_idx]['start_time']));
			$startminute=date("i",strtotime($reservation[$reservation_idx]['start_time']));
			$startampm=date("A",strtotime($reservation[$reservation_idx]['start_time']));
			
			$endhour=date("g",strtotime($reservation[$reservation_idx]['end_time']));
			$endminute=date("i",strtotime($reservation[$reservation_idx]['end_time']));
			$endampm=date("A",strtotime($reservation[$reservation_idx]['end_time']));

			$app->tpl->assign('starthour',$starthour);
			$app->tpl->assign('startminute',$startminute);
			$app->tpl->assign('startampm',$startampm);
			$app->tpl->assign('endhour',$endhour);
			$app->tpl->assign('endminute',$endminute);
			$app->tpl->assign('endampm',$endampm);


			$app->tpl->assign( 'reservation' , $reservation);
			$app->tpl->display( 'single-reservation.tpl' );
		}//edit

	if($request->action=="delete"){
		if(ReserveDatabaseAPI::user_level()>0){
			die('You do not have permission to delete a reservation.');
		}
			$reservation_idx=$request->id;
			ReserveDatabaseAPI::delete_reservation($reservation_idx);
			ReserveDatabaseAPI::delete_messages($reservation_idx);
			$response->redirect($GLOBALS['BASE_URL'].'/admin/reservation');
		}//delete
	
});//end reservation/searach/id


respond('/reservation/addmessage/[i:id]', function( $request, $response, $app){
	//adding a message to a loan 
	$username=$_SESSION['username'];
	$message=$request->message;
	$message=filter_var($message, FILTER_SANITIZE_STRING);
	$reservation_idx=$request->id;
	ReserveDatabaseAPI::add_message($reservation_idx,$message, $username);
	$response->redirect($GLOBALS['BASE_URL'].'/admin/reservation/search/id/'.$reservation_idx);
});//add message to reservation

respond('/reservation/search/[a:action]' , function( $request, $response, $app){
	//searching the reservations by their specfic filters
	//send the data to the search function
	$data = ReserveDatabaseAPI::search($request);
	if($data['redirect_url']){
		//if there was a redirect url in the data, redirect the user there
		$response->redirect($data['redirect_url']);
	}
	//otherwise assign the title and reservations
	$app->tpl->assign('title', $data['title']);
	$app->tpl->assign('reservation', $data['reservations']);
	$app->tpl->display( 'reservation.tpl' );

});//end reservation/search/action

	
respond ('/statistics', function( $request, $response, $app ){
	$statistics=ReserveDatabaseAPI::statistics();
	$app->tpl->assign('statistics', $statistics);
	$app->tpl->display( 'statistics.tpl' );

});
