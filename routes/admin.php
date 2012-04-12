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

respond('/admincp/subitems', function( $request, $response, $app){
	$app->tpl->assign( 'subitems', reserveDatabaseAPI::getSubItems());
	$app->tpl->display( 'adminsubitems.tpl' );
	PSU::db('cts')->debug=true;
});//admincp equipment page

respond('POST', '/admincp/subitems/add', function( $request, $response, $app){
	$name=$request->param('new_subitem');
	$name=filter_var($name, FILTER_SANITIZE_STRING);
	reserveDatabaseAPI::insertSubitem($name);
	$response->redirect($GLOBALS['BASE_URL'] . '/admin/admincp/subitems' );
});//admin subitem add

respond('/admincp/subitems/[i:id]/remove', function( $request, $response, $app){
	$subitem_id=$request->id;
	reserveDatabaseAPI::deleteSubitem($subitem_id);
	$response->redirect($GLOBALS['BASE_URL'] . '/admin/admincp/subitems');
});//admin subitem 

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

respond('/reservation/[i:id]/edit',function( $request, $response, $app){
	//required parameters
	$reservation_idx=$request->id;
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
	}

	if( count($_SESSION['errors'])>0 ){//if the number of errors is > 0
		$response->redirect( $GLOBALS['BASE_URL'] . '/admin/reservation/search/id/' . $reservation_idx .'/edit');
	}else{
		$cts_admin['first_name']=$first_name;
		$cts_admin['last_name']=$last_name;
		$cts_admin['username']=$_SESSION['username'];
		$cts_admin['phone']=$phone;
		$cts_admin['submit_first_name']=$submit_first_name;
		$cts_admin['submit_last_name']=$submit_last_name;
		
		if( $secondary_phone ){
			$cts_admin['secondary_phone']=$secondary_phone;
		}
		$cts_admin['email']=$email;
		$cts_admin['title']=$title;
		$cts_admin['location']=$location;
		$cts_admin['room']=$room;
		
		if( $comments ) {
			$cts_admin['comments']=$comments;
		}

		$cts_admin['start_date']=$start_date;
		$cts_admin['end_date']=$end_date;
		$cts_admin['start_time']=$start_time;
		$cts_admin['starthour']=$starthour;
		$cts_admin['startminute']=$startminute;
		$cts_admin['startampm']=$startampm;
		$cts_admin['end_time']=$end_time;
		$cts_admin['endhour']=$endhour;
		$cts_admin['endminute']=$endminute;
		$cts_admin['endampm']=$endampm;
		$cts_admin['reserve_type']=$reserve_type;
		$start_time = date("H:i:s", strtotime($start_time));
		$end_time = date("H:i:s", strtotime($end_time));
		$start_date=date("Y-m-d", strtotime($start_date));
		$end_date=date("Y-m-d" , strtotime($end_date));

		reserveDatabaseAPI::updateReservation($reservation_idx,$last_name, $first_name, $phone, $email, $start_date, $start_time, $end_date, $end_time, $comments, $location, $room, $title, $delivery_type);
		$response->redirect( $GLOBALS['BASE_URL'] . '/admin/reservation/search/id/' . $reservation_idx);
	}//end else


});

respond('/reservation/id/[i:id]/status', function( $request, $response, $app){
	$reservation_idx=$request->id;
	$status=$request->param('status');
	if($status=="approved"){
		CTSemailAPI::emailUserApproved($reservation_idx);
	}elseif($status=="cancelled"){
		CTSemailAPI::emailUserCancelled($reservation_idx);
	}
	reserveDatabaseAPI::changeStatus($reservation_idx, $status);
	$response->redirect($GLOBALS['BASE_URL'] . '/admin/reservation/search/id/'.$reservation_idx);	
});

respond('/reservation/[i:id]/subitem/add', function( $request, $response, $app){
	$reservation_idx=$request->id;
	$subitem_id=$request->param('subitems');
	reserveDatabaseAPI::insertReservationSubitem($reservation_idx,$subitem_id);
	$response->redirect($GLOBALS['BASE_URL'] . '/admin/reservation/search/id/'.$reservation_idx);	

});//reservation id subitem add

respond('/reservation/subitem/remove/[i:id]/[:key]', function( $request, $response, $app){
	$id=$request->id;
	$reservation_idx=$request->key;
	reserveDatabaseAPI::deleteReserveSubitem($id);
	$response->redirect($GLOBALS['BASE_URL'] . '/admin/reservation/search/id/'.$reservation_idx);	

}); //reservation subitem remove

respond('/reservation/search/id/[i:id]' , function( $request, $response, $app){
	$reservation_idx=$request->id;
	$query=new \PSU\Population\Query\IDMAttribute('mis','permission');
	$factory = new \PSU_Population_UserFactory_PSUPerson;
	$population= new \PSU_Population( $query, $factory );
	$app->tpl->assign('status',array("approved"=>"approved","pending"=>"pending","loaned out"=>"loaned out","returned"=> "returned", "cancelled"=>"cancelled"));	
	$cts_technicians=$population->query();
	$app->tpl->assign( 'subitemlist', reserveDatabaseAPI::getSubItems());
	PSU::dbug($population);
	PSU::dbug($cts_technicians);
	$app->tpl->assign( 'subitems', reserveDatabaseAPI::getReserveSubItems($reservation_idx)); //reservation search id

	$app->tpl->assign( 'cts_technicians',$cts_technicians );
	//$app->tpl->assign( 'cts_technicians',array(000256614=>"David Allen",000256615 => "Technician Dave"));//list of CTS technicians
	$app->tpl->assign( 'messages', reserveDatabaseAPI::getMessages($reservation_idx));
	$app->tpl->assign( 'equipment', reserveDatabaseAPI::getEquipment($reservation_idx));
	$app->tpl->assign( 'locations' , reserveDatabaseAPI::locations());
	$app->tpl->assign( 'reservation_idx', $reservation_idx);
	$app->tpl->assign( 'reservation' , reserveDatabaseAPI::by_id($reservation_idx));
	$app->tpl->display( 'singlereservation.tpl' );

});//end reservation/search/

respond('/reservation/search/id/[i:id]/[a:action]' , function( $request, $response, $app){
	if($request->action=="edit"){//if the action is to edit the current reservation
		$editable=true;
		$app->tpl->assign( 'editable', $editable);
		$reservation_idx=$request->id;
		$app->tpl->assign( 'messages', reserveDatabaseAPI::getMessages($reservation_idx));
		$app->tpl->assign( 'equipment', reserveDatabaseAPI::getEquipment($reservation_idx));
		$app->tpl->assign( 'locations' , reserveDatabaseAPI::locations());
		$app->tpl->assign( 'hours' , array(1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,11=>11,12=>12));
	$app->tpl->assign( 'minutes', array(00=>0,05=>5,10=>10,15=>15,20=>20,25=>25,30=>30,35=>35,40=>40,45=>45,50=>50,55=>55));
		$app->tpl->assign( 'ampm' , array("AM"=>"AM","PM"=>"PM"));

		$app->tpl->assign( 'reservation_idx', $reservation_idx);
		$reservation=reserveDatabaseAPI::by_id($reservation_idx);

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

