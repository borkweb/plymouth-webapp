<?php
class ReserveDatabaseAPI{

	public function add_message($reservation_idx, $message, $username){
		//adding a message to a reservation
		$date=date('Y-m-d');
		$time=date('G:i:s');
		$data=array(
			$message, 
			$reservation_idx, 
			$date, 
			$time, 
			$username,
		);
		$sql="
			INSERT INTO cts_reservation_note
			(
				message,
				reservation_idx, 
				date, 
				time, 
				author
			) 
			VALUES 
			(
				?, 
				?,
				?, 
				?, 
				?
			)";
		return PSU::db('cts')->Execute($sql,$data);

	}//function addMessage

	public function add_user_pickup($reservation_idx, $user_id){
		//adding a pickup user to a reservation
		$sql="
			UPDATE cts_reservation 
			   SET user_pickup = ? 
			 WHERE reservation_idx = ?
			";
		$data=array(
			$user_id,
			$reservation_idx,);
		return PSU::db('cts')->Execute($sql,$data);
	}//function addUserPickup

	public function add_user_dropoff($reservation_idx, $user_id){
		//adding a dropoff user to a reservation
		$sql="
			UPDATE cts_reservation 
			   SET user_dropoff = ? 
			 WHERE reservation_idx = ?
			";
		$data=array(
			$user_id,
			$reservation_idx,
			);
		return PSU::db('cts')->Execute($sql,$data);
	}
	//function addUserDropoff


	public function add_building($building_name){
		//add a new building to the list of buildings
		$sql="
			INSERT INTO cts_building 
			(	
				name
			) 
			VALUES 
			(
				?
			)
			";
		return PSU::db('cts')->Execute( $sql, $building_name );

	}//function add_building

	public function remove_equipment($reservation_idx){
		//remove a piece of equipment from a reservation
		$sql="
			UPDATE cts_reservation_equipment
			   SET deleted=true	
			 WHERE reservation_equipment_idx = ?
			";
		return PSU::db('cts')->Execute($sql, $reservation_idx);

	}//function removeEquipment

	public function add_equipment($reservation_idx, $GLPI_ID){
		//add a piece of equipment to a reservation	
		$GLPI_ID=self::format_glpi($GLPI_ID);	
		$sql="
			INSERT INTO cts_reservation_equipment 
			(
				reservation_idx,
				glpi_id
			) 
			VALUES 
			(
				?,
				?
			)
			";

		$data=array(
			$reservation_idx, 
			$GLPI_ID,
			);

		PSU::db('cts')->Execute($sql, $data);

	}//function add_equipment

	public function by_date($date){
		//filter reservation results by date
		$sql="
			SELECT * 
			  FROM cts_reservation
			 WHERE start_date = ? 
			    OR end_date =	?
			   AND delted = false
		   ORDER BY reservation_idx DESC";
		$dates=array(
				$date,
				$date,
				);
		return PSU::db('cts')->GetAssoc( $sql, $dates);

	}//end function by_date

	public function by_user_date($date, $user){
		//filter reservation results by a user and a date
		$data=array(
			$date,
			$date,
			$user,
			$user
			);
		$sql="
			SELECT * 
			  FROM cts_reservation
			 WHERE (start_date = ? OR end_date =?) 
			   AND (delivery_user = ? OR retrieval_user = ?)
			   AND deleted = false
		   ORDER BY start_time ASC
			";
		return PSU::db('cts')->GetAssoc( $sql, $data);

	}//end function by_date


	public function by_start_date($date){
		//filter reservation results by the start date
		$sql="
			SELECT * 
			  FROM cts_reservation
			 WHERE start_date = ?
			   AND deleted = false
		   ORDER BY reservation_idx DESC	
			";
		return PSU::db('cts')->GetAssoc( $sql, $date);

	}//end function by_start_date

	public function by_end_date($date){
		//filter reservation results by end date
		$sql="
			SELECT * 
		       FROM cts_reservation
			 WHERE end_date = ?
			   AND deleted = false
		   ORDER BY reservation_idx DESC	
			";
		return PSU::db('cts')->GetAssoc( $sql, $date);

	}//end function by_end_date

	public function by_lname($lname){
		//filter results by last name
		$sql="
			SELECT * 
			  FROM cts_reservation
			 WHERE lname = ?
			   AND deleted = false
		   ORDER BY reservation_idx DESC	
			";
		return PSU::db('cts')->GetAssoc( $sql, $lname);

	}//end function by_lname

	public function by_fname($fname){
		//filter results by first name
		$sql="
			SELECT * 
			  FROM cts_reservation
			 WHERE fname = ?
			   AND deleted = false
		   ORDER BY reservation_idx DESC	
			";
		return PSU::db('cts')->GetAssoc( $sql, $fname);

	}//end function by_fname

	public function by_status($status){
		//filter results by the status of the loan
		$sql="
			SELECT * 
			  FROM cts_reservation
			 WHERE status = ?
			   AND deleted = false
		   ORDER BY reservation_idx DESC	
			";
		return PSU::db('cts')->GetAssoc( $sql, $status);

	}//end function by_status

	public function by_current_status($status){
		//filter the results by the current status of the loan
		$sql="
			SELECT * 
			  FROM cts_reservation
			 WHERE current_status = ?	
			   AND deleted = false
		   ORDER BY reservation_idx DESC	
			";
		return PSU::db('cts')->GetAssoc( $sql, $status);

	}//end function by_status

	public function by_title($title){
		//filter the results by the title of the reservation
		$sql="
			SELECT * FROM cts_reservation
			 WHERE title = ?
			   AND deleted = false
		   ORDER BY reservation_idx DESC	
			";
		return PSU::db('cts')->GetAssoc( $sql, $title);

	}//end function by_status
	
	public function by_date_range($dates){
		//filter results by a range of dates
		$sql="
			SELECT * FROM cts_reservation
			 WHERE (start_date BETWEEN ? AND ? OR end_date BETWEEN ? AND ?)
			   AND deleted = false
		   ORDER BY reservation_idx DESC	
			";
		return PSU::db('cts')->GetAssoc( $sql, $dates);


	}//end function by date_range

	public function by_date_range_equipment($dates){
		//filter results by a range of dates
		$sql="
			SELECT * FROM cts_reservation
			 WHERE (? BETWEEN start_date AND end_date OR ? BETWEEN start_date AND end_date)
			   AND deleted = false
		   ORDER BY reservation_idx DESC	
			";
		return PSU::db('cts')->GetAssoc( $sql, $dates);


	}//end function by date_range

	public function by_id($id){
		//filter results by it's ID
		$sql="
			SELECT * FROM cts_reservation
			 WHERE reservation_idx = ?
		        AND deleted = false
		   ORDER BY reservation_idx DESC	
			";
		return PSU::db('cts')->GetAssoc( $sql, $id);

	}//end function by_id


	public function by_wp_id($wp_id){
		//filter results by the wp_id, which is the user that made the reservation
		$sql="
			SELECT * FROM cts_reservation
			 WHERE wp_id = ? AND NOT status = 'pending'
			   AND deleted = false
		   ORDER BY reservation_idx DESC	
			";
		return PSU::db('cts')->GetAssoc( $sql, $wp_id);

	}//end function by_wp_id


	public function by_wp_id_pending($wp_id){
		//filter results by the wp_id and the fact that the reservation is pending
		//this is used for the user to view their currently pending reservations
		$sql="
			SELECT * FROM cts_reservation
			 WHERE wp_id = ? AND status='pending'
			   AND deleted = false
		   ORDER BY reservation_idx DESC	
			";
		return PSU::db('cts')->GetAssoc( $sql, $wp_id);

	}//end function by_wp_id

	public function change_pickup($reservation_idx, $user){
		//this changes the pickup user
		$sql="
			UPDATE cts_reservation 
                  SET retrieval_user= ? 
                WHERE reservation_idx= ?";
		$data=array(
				$user,
				$reservation_idx,
				);
		return PSU::db('cts')->Execute( $sql, $data);

	}//end change pickup

	public function change_dropoff($reservation_idx, $user){
		//this changes the dropoff user
		$sql="
			UPDATE cts_reservation 
			   SET delivery_user = ? 
                WHERE reservation_idx= ?
			";
		$data=array(
 				$user,
				$reservation_idx,
				);
		return PSU::db('cts')->Execute( $sql, $data);

	}//end change dropoff


	public function change_status($reservation_idx, $status){
		//this changes the status of a loan
		$sql="
			UPDATE cts_reservation 
			   SET status= ? 
			 WHERE reservation_idx= ?";
		$data=array(
				$status,
				$reservation_idx,
				);
		return PSU::db('cts')->Execute( $sql, $data);

	}//end change status

	public function change_priority($reservation_idx, $priority){
		//this changes the priority of a loan
		$sql="
			UPDATE cts_reservation 
			   SET priority= ? 
			 WHERE reservation_idx= ?";
		$data=array(
				$priority,
				$reservation_idx,
				);
		return PSU::db('cts')->Execute( $sql, $data);

	}//end change status

	public function check_glpi($glpi_id){
		//check the GLPI database in the computer table to look for the data
		if($parts=self::get_GLPI($glpi_id)){
			return true;
		}else{
			return false;
		}

	}//end function check_glpi
	
	public function check_reservation($reservation_idx){
	//this function checks if a reservation exists, it returns true or false
		$sql="
			SELECT reservation_idx 
			  FROM cts_reservation 
			 WHERE reservation_idx=?
			   AND deleted = false";
		$bool = PSU::db('cts')->GetOne( $sql, $reservation_idx );
		return (boolean)$bool;

	}//end function check_reservation

	public function check_user_id($user_id){
		if(filter_var($user_id, FILTER_VALIDATE_INT)){
			if(strlen($user_id)>9){//check to make sure it is less than 9 digits
				$_SESSION['errors'][]='User ID too long.';
				return false;
			}else{
				return filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);

			}
		}else{
			$_SESSION['errors'][]='User ID in incorrect format.';
			return false;
		}

	}//end function check_user_id

	public function categories(){
		//this selects the categories that the users can select equipment from
		//This is basically the equipment list
		$sql="
			SELECT categoryID, category_name 
			  FROM cts_form_options
			 WHERE deleted = false";

		return PSU::db('cts')->GetAssoc( $sql );

	}//end function categories

	public function get_form_options(){
		//this grabs the form options which is the equipment list
	
		$sql="
			SELECT * 
			  FROM cts_form_options
			  WHERE deleted = false";

		return PSU::db('cts')->GetAssoc( $sql );

	}//end function get form options

	public function delete_reservation($reservation_idx){
		//this delets a reservation, this can only be done by the manager
		$sql="
			UPDATE cts_reservation 
			   SET deleted=true
			 WHERE reservation_idx = ?";
		
		return PSU::db('cts')->Execute( $sql, $reservation_idx );
	
	}//end function deleteReservation

	public function delete_equipment($equipment_id){
		//this deletes a piece of equipment from the list of available equipment on the reservation page
		$sql="
			UPDATE cts_form_options
			   SET deleted=true 
			 WHERE categoryID= ?";

		return PSU::db('cts')->Execute( $sql, $equipment_id );

	}//end function deleteEquipment

	public function delete_subitem($subitem_id){
		//this deletes a subitem from the list of available subitems
		$sql="UPDATE cts_subitem 
			    SET deleted = true
			  WHERE id= ?";

		return PSU::db('cts')->Execute( $sql, $subitem_id );

	}//end function deleteSubitem

	public function delete_reserve_subitem($id){
		//this deletes a subitem from a specific reservation
		$sql="
			UPDATE cts_reservation_subitem 
			   SET deleted=true
			 WHERE reservation_subitem_id=?";

		return PSU::db('cts')->Execute( $sql, $id );
		
	}//end function delete reserve subitem

	public function delete_messages($reservation_idx){
		//this deletes a reservation message 
		$sql="
			UPDATE cts_reservation_note 
			   SET deleted=true
			 WHERE reservation_idx = ?";
		
		PSU::db('cts')->Execute( $sql, $reservation_idx );
	
	}//end function deleteReservation

	public function delete_announcement($announcement_id){
		//this deltes an announcement
		$sql="
			UPDATE cts_form_announcements 
			   SET deleted=true
			 WHERE announceID=?";
		PSU::db('cts')->Execute( $sql, $announcement_id);

	}//end function delete_announcement

	public function delete_building($building_idx){
		//this deletes a building from the list of available buildings
		$sql="
			UPDATE cts_building 
			   SET deleted=true
	 		 WHERE building_idx = ?";
		return PSU::db('cts')->Execute( $sql, $building_idx );
	}

	public function edit_announcement($announcement_id, $message){
		//this edits an existing announcement
		$sql="
			UPDATE cts_form_announcements 
                  SET message = ? 
                WHERE announceID = ?";
		$data=array(
				$message,
				$announcement_id,
				);
		return PSU::db('cts')->Execute( $sql, $data);
	}//end function edit_announcement

	public function change_announcement($announcement_id, $value){
		//this changes the viewable property of an announcement
		$sql="
			UPDATE cts_form_announcements 
			   SET form_viewable = ? 
			 WHERE announceID = ?";
		$data=array(
				$value,
				$announcement_id,
				);
		return PSU::db('cts')->Execute( $sql, $data);
	}//end function change_announcement

	public function change_reservation_agreement($agreement){
		//this changes the reservation agreement
		$sql="
			UPDATE cts_reservation_agreement 
			   SET agreement = ? 
	   	   	 WHERE agreement_id = 1 ";
		return PSU::db('cts')->Execute( $sql, $agreement );

	}//end function change_reservation_agreement

	public function search($request){
		define('ONE_DAY', 60*60*24);//defining what one day is
		$week=date('w');//define the current week
		if($request->action=="nextweek"){
			//$start_date=date('Y-m-d', strtotime("+1 week"));
			//$end_date=date('Y-m-d', strtotime("+2 week"));
			//this takes the current time and goes to the next week
			$start_date=date('Y-m-d',time()- ($week - 7) * ONE_DAY);
			$end_date=date('Y-m-d',time()- ($week - 13) * ONE_DAY);
			$fixed_start_date=self::fix_date($start_date);
			$fixed_end_date=self::fix_date($end_date);

			$title="Reservations from $fixed_start_date to $fixed_end_date";

			$dates=array(
				$start_date, 
				$end_date, 
				$start_date, 
				$end_date,
			);
			$reservation =  self::by_date_range($dates);
		}elseif($request->action=="thisweek"){
			//this shows the information for this week
			$start_date=date('Y-m-d',time()- ($week) * ONE_DAY);
			$end_date=date('Y-m-d',time()- ($week - 6) * ONE_DAY);
			$dates=array(
				$start_date, 
				$end_date, 
				$start_date, 
				$end_date
				);
			$fixed_start_date=self::fix_date($start_date);
			$fixed_end_date=self::fix_date($end_date);

			$title="Reservations from $fixed_start_date to $fixed_end_date";
			$reservation= self::by_date_range($dates);

		}elseif($request->action=="daterange"){
			$start_date=$request->param('from_date');
			$start_date=date('Y-m-d',strtotime($start_date));

			$end_date=$request->param('to_date');
			$end_date=date('Y-m-d',strtotime($end_date));

			$fixed_start_date=ReserveDatabaseAPI::fix_date($start_date);
			$fixed_end_date=ReserveDatabaseAPI::fix_date($end_date);

			$dates=array(
					$start_date, 
					$end_date, 
					$start_date, 
					$end_date,
					);
			$title="Reservations from $fixed_start_date to $fixed_end_date";
			$reservation=self::by_date_range($dates);
		
		
		}elseif($request->action=="lastweek"){
			//this shows the information from last week
			$start_date=date('Y-m-d',time()- ($week + 7) * ONE_DAY);
			$end_date=date('Y-m-d',time()- ($week + 1) * ONE_DAY);
			$fixed_start_date=self::fix_date($start_date);
			$fixed_end_date=self::fix_date($end_date);
			
			$dates=array(
				$start_date, 
				$end_date, 
				$start_date, 
				$end_date
				);


			$title="Reservations from $fixed_start_date to $fixed_end_date";
			$reservation= self::by_date_range($dates);

		}elseif($request->action=="today")
		{
			//this shows the information from today, which is default
			
			$start_date=date('Y-m-d');
			$fixed_start_date=self::fix_date($start_date);

			$title="Reservations for today, the $fixed_start_date";
			$reservation= self::by_date($start_date);


		}elseif($request->action=="yesterday")
		{
			//this shows the information from yesterday
			$start_date=date('Y-m-d', strtotime("-1 day"));
			$fixed_start_date=self::fix_date($start_date);

			$title="Reservations for yesterday, the $fixed_start_date";
			$reservation= self::by_date($start_date);


		}elseif($request->action=="tommorrow")
			//this shows the information from tomorrow
		{
			$start_date=date('Y-m-d', strtotime("+1 day"));
			$fixed_start_date=self::fix_date($start_date);

			$title="Reservations for tomorrow, the $fixed_start_date";
			$reservation= self::by_date($start_date);

		}elseif($request->action=="pending"){
			//this shows any loans that are pending
			$query="pending";

			$title = "Pending Reservations";

			$reservation = self::by_status($query);

		}elseif($request->action=="loaned"){
			//this shows any loans that are pending
			$query="loaned out";

			$title = "Loaned Reservations";

			$reservation = self::by_status($query);
		}elseif($request->action=="outstanding"){
			//this shows any loans that are pending
			$query="outstanding";

			$title = "Outstanding Reservations";

			$reservation = self::by_status($query);

		}elseif($request->action=="missing"){
			//this shows any loans that are pending
			$query="missing";

			$title = "Missing Reservations";

			$reservation =  self::by_status($query);


		}elseif($request->action=="detailed"){
			//this searches between two dates
			if($start_date=$request->param('from_date'))
			{
				$start_date=date('Y-m-d',strtotime($start_date));
				$args['start_date'] = $start_date;
				$fixed_start_date=self::fix_date($start_date);

			}

			if($end_date=$request->param('to_date')){
				$end_date=date('Y-m-d',strtotime($end_date));
				$args['end_date'] = $end_date;
				$fixed_end_date=self::fix_date($end_date);

			}

			if($first_name=$request->param('first_name')){

				$args['first_name'] = $first_name;
			}

			if($last_name=$request->param('last_name')){
				$args['last_name'] = $last_name;
			}

			if($location=$request->param('location')){
				$args['location'] = $location;
			}

			if($reservation_id=$request->param('reservation_id')){

				$args['reservation_id'] = $reservation_id;
			}
			if(count($args)<1){
				$_SESSION['errors'][]='You need to specify at least one criteria.';
				$redirect_url = $GLOBALS['BASE_URL'] . '/admin/reservation';
			}else{
				$reservation=self::search_reservation($args);
			}

			if($start_date && $end_date){
				//only change the title if there is a start and end date
				$title = "Reservations from $fixed_start_date to $fixed_end_date";
			}
		}else{
			//if there was no parameter, return the dates and reservations for this week.
			$start_date=date('Y-m-d',time()- ($week) * ONE_DAY);
			$end_date=date('Y-m-d',time()- ($week - 6) * ONE_DAY);
			$dates=array(
				$start_date, 
				$end_date, 
				$start_date, 
				$end_date
				);
			$fixed_start_date=self::fix_date($start_date);
			$fixed_end_date=self::fix_date($end_date);

			$title="Reservations from $fixed_start_date to $fixed_end_date";
			$reservation= self::by_date_range($dates);

		}
		$data=array(
				'title'=>$title,
				'dates'=>$dates,
				'redirect_url'=>$redirect_url,
				'reservations'=>$reservation,
				);
			return $data;


	}//function search 
	
	public function fix_date($date){
		//this is a function that is used to fix a date to the proper format for inserting into the database
		return date( 'n-j-Y', strtotime($date));
	}//end function fix date

	public function format_glpi($GLPI_ID){
		//check the length of the GLPI_ID id
		if(strlen($GLPI_ID)!=4 && strlen($GLPI_ID) !=13){
		//make sure that it is either 4 or 13 characters
			$_SESSION['errors'][]="Incorrect format.";
		}elseif(strlen($GLPI_ID)==4){
			//if the length is 4, add the prepended characters for the GLPI ID
			$GLPI_ID='PSU-0000-' . $GLPI_ID;
		}
		return $GLPI_ID;
	}//end function format_glpi


	public function insert_form_options($category, $description){
		//this creates a new piece of equipment in the list of availavle
		$sql="
			INSERT INTO cts_form_options 
			(
				category_name, 
				description
			) 
			VALUES 
			(
				?,
				?
			)";
		$values=array(
					$category, 
					$description
					);
		return PSU::db('cts')->Execute( $sql, $values );

	}//end function insertCategory

	public function init_vars($app){

		$hours=array();
		//generate numbers 1 through 12 for the hours
		for($i = 1; $i <=12; $i++){

			$hours[$i]=$i;
		}
		$minutes=array();
		//generate numbers 0 through 55 every 5 numbers (0,5,10,15 etc.)
		for($x = 0;$x <=55; $x+= 5){
			$minutes[$x]=$x;

		}
	
		$app->tpl->assign( 'hours', $hours );
		$app->tpl->assign( 'minutes', $minutes );	
		$app->tpl->assign( 'ampm' , array("AM"=>"AM","PM"=>"PM"));

		$app->tpl->assign( 'user', $app->user );

		//assign vars that are used throughout the whole system
		$app->tpl->assign('date_format','%m-%d-%Y');
		$app->tpl->assign('time_format','%l:%M %p');
		$app->tpl->assign('locations',ReserveDatabaseAPI::locations(false)); 
		$app->tpl->assign('user_level',ReserveDatabaseAPI::user_level());//this assigns the user to a manager (0) cts staff (1) or helpdesk (2)
		$status=array(
			"approved"=>"approved",
			"pending"=>"pending",
			"loaned out"=>"loaned out",
			"returned"=> "returned", 
			"cancelled"=>"cancelled",
		);
		$app->tpl->assign('status', $status);
		$app->tpl->assign('priority', array("normal", "high"));
		$app->tpl->assign( 'subitemlist', ReserveDatabaseAPI::get_subitems());

		
	}//end function int_vars

	public function init_technicians($app){

		$query=new \PSU\Population\Query\IDMAttribute('mis','permission');
		$factory = new \PSU_Population_UserFactory_PSUPerson;
		$population= new \PSU_Population( $query, $factory );
		$cts_technicians=$population->query();
		$app->tpl->assign( 'subitemlist', ReserveDatabaseAPI::get_subitems());
		$app->tpl->assign( 'cts_technicians', array(NULL=>"Select a Technician","p5lydnqia"=>"David Allen", "poasdfe"=>"Todd Kent"));
	//PSU::dbug($population);
	//PSU::dbug($cts_technicians);
	//$pop = iterator_to_array($population);
	//PSU::dbug($pop[0]);


	}//init_technicians
	
	public function init_all_reservation_info($app,$reservation_idx){
		self::init_technicians($app);	
				$app->tpl->assign( 'subitems', ReserveDatabaseAPI::get_reserve_subitems($reservation_idx));

		$app->tpl->assign( 'messages', ReserveDatabaseAPI::get_messages($reservation_idx));
		$app->tpl->assign( 'equipment', ReserveDatabaseAPI::get_equipment($reservation_idx));
		$equipment=ReserveDatabaseAPI::get_equipment($reservation_idx);
		$app->tpl->assign( 'equipment', $equipment);
		$equipment_info=ReserveDatabaseAPI::get_equipment_info($equipment);
		$app->tpl->assign('equipment_info',$equipment_info);

		$app->tpl->assign( 'reservation_idx', $reservation_idx);
		$app->tpl->assign( 'reservation' , ReserveDatabaseAPI::by_id($reservation_idx));


	}//get_all_reservation_info

	public function insert_subitem($name){
		//this creates a new subitem 
		$sql="
			INSERT INTO cts_subitem 
				(name) 
			VALUES 
				(?)";
		return PSU::db('cts')->Execute( $sql, $name );

	}//end function insertCategory

	public function insert_announcement($message){
		//this creates a new announcement
		$sql=
			'INSERT INTO cts_form_announcements 
			 (
				message, 
				form_viewable
	 		 )
			 VALUES 
			 (
				?,
				"yes"
			)';
		return PSU::db('cts')->Execute( $sql, $message );

	}//end function insert_announcement
	
	public function insert_reservation_subitem($reservation_idx, $subitem_id){
		//this adds a subitem to the reservation
		$sql=
			"INSERT INTO cts_reservation_subitem 
			(
				reservation_id,
				subitem_id
			) 
			VALUES 
			(
				?,
				?
			)";
		$values=array($reservation_idx,$subitem_id);
		return PSU::db('cts')->Execute( $sql, $values );

	}//end function insertCategory


	public function insert_reservation($data){
		//this is used to insert a reservation into the database

		$sql="
		INSERT INTO cts_reservation 
			(wp_id,
			lname,
			fname,
			phone,
			email,
			application_date,
			start_date,
			start_time,
			end_date,
			end_time,
			memo,
			building_idx,
			room,
			title,
			delivery_type,
			request_items,
			status) 
		VALUES 
			(?,
			?, 
			?, 
			?, 
			?, 
			?, 
			?, 
			?, 
			?, 
			?, 
			?, 
			?, 
			?, 
			?, 
			?, 
			?, 
			?)";
			
		PSU::db('cts')->Execute( $sql, $data);
		return PSU::db('cts')->Insert_ID();
			
	}//end function insertReservation

	public function item_info($item_id){
		//this grabs the information for a specific equipment item
		$sql="
			SELECT description 
			  FROM cts_form_options 
			 WHERE categoryID = ?";

		return PSU::db('cts')->GetOne( $sql, $item_id );
	}//end function itemInfo

	public function locations($default = true){
		//this grabs the list of buildings
		$sql="
			SELECT building_idx, name  
			  FROM cts_building
			 WHERE deleted = false";
		$locations = PSU::db('cts')->GetAssoc( $sql );
		if($default==false){
			$locations=array(NULL=>'Please select a location') + $locations;
		}
		return $locations;

	}//end function locations

	
	public function get_reservation_agreement(){
		//this grabs the reservation agreement
		$sql="
			SELECT agreement 
			  FROM cts_reservation_agreement 
			 WHERE agreement_id = 1";
		return PSU::db('cts')->GetOne( $sql );

	}//end function get_announcements

	
	public function get_announcements(){
		//this grabs the announcements
		$sql="
			SELECT * 
			  FROM cts_form_announcements
			 WHERE deleted = false";
		return PSU::db('cts')->GetAssoc( $sql );

	}//end function get_announcements

	public function get_current_announcements(){
		//this grabs the announcements that are currently viewable, this is used to show the announcements to the users
		$sql='
			SELECT * 
			  FROM cts_form_announcements 
			 WHERE form_viewable = "yes"
		 	   AND deleted = false';
		return PSU::db('cts')->GetAssoc( $sql );

	}//end function get_current_announcements


	public function get_announcement($announcement_id){
		//this grabs a specific announcement for editing
		$sql="
			SELECT * 
			  FROM cts_form_announcements 
			 WHERE announceID = ?";
		return PSU::db('cts')->GetRow( $sql,$announcement_id );

	}//end function get_announcements

	public function get_GLPI($item_id){
		//this grabs GLPI information for a specific item
		$sql = "
			SELECT 
				   item.name as psu_name,
				   `mod`.name as model,
				   t.name as type
			  FROM glpi_computers item 
			  JOIN glpi_computermodels `mod` 
				ON item.computermodels_id = `mod`.id 
			  JOIN glpi_computertypes t 
				ON item.computertypes_id = t.id 
			 WHERE item.name = ?
			UNION ALL
			SELECT 
				   item.name as psu_name,
				   `mod`.name as model,
				   t.name as type
			  FROM glpi_peripherals item 
			  JOIN glpi_peripheralmodels `mod` 
				ON item.peripheralmodels_id = `mod`.id 
			  JOIN glpi_peripheraltypes t 
				ON item.peripheraltypes_id = t.id 
			 WHERE item.name = ?
			 ";
		$args=array(
			$item_id,
			$item_id,
		);
		return PSU::db('glpi')->GetAssoc( $sql , $args);
	}//end function get_GLPI
	
	public function get_messages($reservation_idx){
		//this grabs the messages for a reservation
		$sql="
			SELECT * 
			  FROM cts_reservation_note 
			 WHERE reservation_idx = ?
			   AND deleted = false
		   ORDER BY date DESC, time DESC";
		return PSU::db('cts')->GetAssoc( $sql , $reservation_idx);
	}//end function get messages

	public function get_subitems(){
		//this grabs the subitems for a reservation
		$sql="
			SELECT id,name 
			  FROM cts_subitem
			 WHERE deleted = false";
		return PSU::db('cts')->GetAssoc( $sql );

	}//end function get subitems


	public function get_reserve_subitems($reservation_id){
		//this grabs the information from the subitem that is in the reservation
		$sql="
			SELECT s.*,c.name 
			  FROM cts_reservation_subitem s,cts_subitem c 
			 WHERE s.reservation_id= ? AND c.id=s.subitem_id";

		return PSU::db('cts')->GetAssoc( $sql, $reservation_id );

	}//end function get subitmes

	public function get_equipment_info($equipment){
		foreach($equipment as $glpi_id){

			if($parts=self::get_GLPI($glpi_id['glpi_id'])){
				$equipment_info[]=$parts;

			}else{
				$parts=array($glpi_id['glpi_id'] => array("model" => "N/A", "type" => "N/A", "reservation_equipment_idx" => $glpi_id['reservation_equipment_idx']));
				$equipment_info[]=$parts;

			}
		}
	return $equipment_info;

	}//end function get_equipment_info
		
	public function get_equipment($reservation_idx, $params = array()){
		$args=array(
			$reservation_idx,
		);
		if($params['glpi_id']){
			$where=" AND glpi_id = ?";
			$args[]=$params['glpi_id'];
		}
		//this grabs the equipment that is on a reservation
		$sql="
			SELECT reservation_equipment_idx,glpi_id 
			  FROM cts_reservation_equipment 
			 WHERE reservation_idx = ? {$where} 
                  AND deleted = false";
		return PSU::db('cts')->GetAll( $sql , $args);
	}//end fuction get equipment

	public function user_level(){
		
		/*
		if( IDMObject::authZ('permission', 'cts_admin') ) {
			return 0;
		}elseif( IDMObject::authZ('permission', 'cts') ){
			return 1;
		}elseif( IDMObject::authZ('role', 'calllog') ){
			return 2;
		}else{
			return 3;
		}
		*/
		 
		//THIS IS TO OVERRIDE FOR TESTING
		return 0;

	}//end function user level

	public function reservation_sanitize($request){

		$reservation_idx=$request->id;
		$first_name=$request->param('first_name');
		$first_name=filter_var($first_name, FILTER_SANITIZE_STRING);

		$last_name=$request->param('last_name');
		$last_name=filter_var($last_name, FILTER_SANITIZE_STRING);

		$phone=$request->param('phone');
		$phone=filter_var($phone, FILTER_SANITIZE_STRING);

		$email=$request->param('email');
		$email=filter_var($email, FILTER_SANITIZE_STRING);

		$reserve_type=$request->param('radio');
		$reserve_type=filter_var($reserve_type, FILTER_SANITIZE_STRING);

		$start_date=$request->param('start_date');//request a parameter for start_date
		$start_date=filter_var($start_date, FILTER_SANITIZE_STRING);

		$end_date=$request->param('end_date');//request a parameter for enddate
		$end_date=filter_var($end_date, FILTER_SANITIZE_STRING);

		$title=$request->param('title');//request a parameter for title
		$title=filter_var($title, FILTER_SANITIZE_STRING);

		$location=$request->param('location');//request a parameter for location
		$location=filter_var($location, FILTER_SANITIZE_STRING);

		$room=$request->param('room');
		$room=filter_var($room, FILTER_SANITIZE_STRING);

		$comments=$request->param('comments');
		$comments=filter_var($comments,FILTER_SANITIZE_STRING);


		$starthour=$request->param('starthour');
		$starthour=filter_var($starthour, FILTER_SANITIZE_STRING);

		$startminute=$request->param('startminute');
		$startminute=filter_var($startminute, FILTER_SANITIZE_STRING);

		$startminute=sprintf("%02d",$startminute);
		$startampm=$request->param('startampm');
		$startampm=filter_var($startampm, FILTER_SANITIZE_STRING);

		$start_time=$starthour . ':' . $startminute . ' ' . $startampm;

		$endhour=$request->param('endhour');
		$endhour=filter_var($endhour, FILTER_SANITIZE_STRING);

		$endminute=$request->param('endminute');
		$endminute=filter_var($endminute, FILTER_SANITIZE_STRING);

		$endminute=sprintf("%02d",$endminute);
		$endampm=$request->param('endampm');
		$endampm=filter_var($endampm, FILTER_SANITIZE_STRING);

		$end_time=$endhour . ':' . $endminute . ' ' . $endampm;

		if( ! $first_name ){ //if there is no first name
			$_SESSION['errors'][]='First name not found'; //throw error
		}
		
		if( ! $last_name ){ //if there is no last name
			$_SESSION['errors'][]='Last name not found'; //throw error
		}
		
		if( ! $phone ){ //if there is no phone number
			$_SESSION['errors'][]='Phone number not found'; //throw error
		}
		
		if( !filter_var($phone, FILTER_VALIDATE_INT)){
		    $_SESSION['errors'][]='Phone number incorrect';	
		}
		
		if( ! $email ){
			$_SESSION['errors'][]='Email not found';
		}else{
			if(! filter_var($email, FILTER_VALIDATE_EMAIL)){
				$_SESSION['errors'][]='Email not correct.';
			}
		}
		
		if( ! $title ){
			$_SESSION['errors'][]='Event Title not found';
		}
		
		if( ! $location){
			$_SESSION['errors'][]='Location not found';
		}
		
		if( $location == "Please select a location" ) {
			$_SESSION['errors'][]='Location not found';
		}
		
		if( ! $room ){
			$_SESSION['errors'][]='Room not found';
		}
		
		if( ! $start_date ){//if there is no start date
			$_SESSION['errors'][]='Start Date not found';
		}
		
		if( ! $end_date ){ //if there is no end date
			$_SESSION['errors'][]='End Date not found';
		}

		if( count($_SESSION['errors'])>0 ){//if the number of errors is > 0
			$complete=false;
		}else{
			$cts_admin['first_name']=$first_name;
			$cts_admin['last_name']=$last_name;
			$cts_admin['phone']=$phone;
			$cts_admin['email']=$email;

			$start_time = date("H:i:s", strtotime($start_time));
			$end_time = date("H:i:s", strtotime($end_time));
			$start_date=date("Y-m-d", strtotime($start_date));
			$end_date=date("Y-m-d" , strtotime($end_date));
			$cts_admin['start_date']=$start_date;
			$cts_admin['end_date']=$end_date;
			$cts_admin['start_time']=$start_time;
			$cts_admin['end_time']=$end_time;

			if( $comments ) {
				$comments= filter_var($comments, FILTER_SANITIZE_STRING);
				$cts_admin['comments']=$comments;
			}else{
				$comments = NULL;
			}

			$cts_admin['location']=$location;
			$cts_admin['room']=$room;
			$cts_admin['title']=$title;
			$cts_admin['reserve_type']=$reserve_type;
			$cts_admin['reservation_idx']=$reservation_idx;
			//update the reservation with the new information
			$complete=true;
		}//end else
		$data=array(
			'complete' => $complete,
			'cts_admin' => $cts_admin,
			);
		return $data;

	}//end reservation_sanitize

	public function search_reservation($params = array()){
		
		if($params['first_name']){
			$where .= " AND fname = ?";
			$args[] = $params['first_name'];
		}

		if($params['last_name']){
			$where .= " AND lname = ?";
			$args[] = $params['last_name'];
		}

		if($params['location']){
			$where .= " AND building_idx = ?";
			$args[] = $params['location'];
		}

		if($params['reservation_id']){
			$where .= " AND reservation_idx = ?";
			$args[] = $params['reservation_id'];

		}

		if($params['start_date'] && $params['end_date']){
			//if there is a start_date and an end_date
			$where .= " AND (start_date  BETWEEN ? AND ? OR end_date BETWEEN ? AND ?)";
			$args[] = $params['start_date'];
			$args[] = $params['end_date'];
			$args[] = $params['start_date'];
			$args[] = $params['end_date'];

		}elseif($params['start_date']){
			//if there isn't both a start and end, then there should only be one
			//either start
			$where .= " AND start_date = ?";
			$args[] = $params['start_date'];
		}elseif($params['end_date']){
			//or end
			$where .= " AND end_date = ?";
			$args[] = $params['end_date'];
		}//end elseif

		$sql="
			SELECT * 
			  FROM cts_reservation 
			 WHERE 1=1 {$where}
			   AND deleted = false 
		   ORDER BY reservation_idx DESC LIMIT 100";
		return PSU::db('cts')->GetAssoc($sql, $args);

	}//end function search_reservation

	public function statistics(){
	//grab some statistics
		$sql="
			SELECT COUNT(reservation_idx) 
		       FROM cts_reservation";
		$count_of_reservations=PSU::db('cts')->GetOne( $sql );

		$sql="
			SELECT COUNT(reservation_equipment_idx) 
			   FROM cts_reservation_equipment";
		$count_of_equipment=PSU::db('cts')->GetOne( $sql );

		$sql="
			SELECT * 
			   FROM cts_reservation 
		    ORDER BY reservation_idx ASC";
		//return the first reservation
		$first_reservation=PSU::db('cts')->GetRow( $sql );

		$sql="
			SELECT * 
			  FROM cts_reservation 
		   ORDER BY reservation_idx DESC";
		$last_reservation=PSU::db('cts')->GetRow( $sql );

		$sql="SELECT glpi_id, COUNT(glpi_id) AS count 
			   FROM cts_reservation_equipment 
		    GROUP BY glpi_id 
		    ORDER BY count DESC";
		$equipment_use=PSU::db('cts')->GetAll( $sql );

		return array(
			'count_of_reservations' => $count_of_reservations, 
			'count_of_equipment' => $count_of_equipment,
			'first_reservation' => $first_reservation,
			'last_reservation' => $last_reservation,
			'equipment_use' => $equipment_use
		);

	}//end function statistics

	public function update_reservation($data){
		//this updates a reservation after editing
		$sql="
		UPDATE cts_reservation SET
			fname=?,
			lname=?,
			phone=?,
			email=?,	
			start_date=?,
			end_date=?,
			start_time=?,
			end_time=?,
			memo=?,
			building_idx=?,
			room=?,
			title=?,
			delivery_type=?
		WHERE
		reservation_idx=?";
			
		PSU::db('cts')->Execute( $sql,$data );
		

	}//end function insertReservation


}//end class reserveDatabaseAPI
