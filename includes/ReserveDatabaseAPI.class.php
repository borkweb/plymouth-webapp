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
			$reservation_idx,
		);
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
	}//function addUserDropoff

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

	public function add_equipment($reservation_idx, $glpi_id){
		//add a piece of equipment to a reservation	
		$glpi_id=self::format_glpi($glpi_id);	
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
			$glpi_id,
		);

		return PSU::db('cts')->Execute($sql, $data);

	}//function add_equipment

	public function by($where, $args){
		$sql="SELECT *
	 	   FROM cts_reservation
		  WHERE 1=1
		     AND (deleted = false)
			AND {$where}
	    ORDER BY start_time ASC, reservation_idx DESC
		";

	return PSU::db('cts')->GetAssoc( $sql, $args );;
	}//function by
	
	public function by_date($date){
		//filter reservation results by date
		$args=array(
			$date,
			$date,
		);
		return self::by( "start_date = ? OR end_date = ?",$args ); 

	}//end function by_date

	public function by_user_date($date, $user){
		//filter reservation results by a user and a date
		$args=array(
			$date,
			$date,
			$user,
			$user
			);
		return self::by("(start_date = ? OR end_date =?)  AND (delivery_user = ? OR retrieval_user = ?)", $args);

	}//end function by_date

	public function by_start_date($date){
		//filter reservation results by the start date
		return self::by("start_date = ?", $date);
	}//end function by_start_date

	public function by_end_date($date){
		//filter reservation results by end date
		return self::by("end_date = ?", $date);
	}//end function by_end_date

	public function by_lname($lname){
		//filter results by last name
		return self::by("lname = ?", $lname);
	}//end function by_lname

	public function by_fname($fname){
		//filter results by first name
		return self::by("fname = ?", $fname);
	}//end function by_fname

	public function by_status($status){
		//filter results by the status of the loan
		return self::by("status = ?", $status);
	}//end function by_status

	public function by_current_status($status){
		//filter the results by the current status of the loan
		return self::by("current_status = ?", $status);
	}//end function by_status

	public function by_title($title){
		//filter the results by the title of the reservation
		return self::by("title = ?", $title);
	}//end function by_status
	
	public function by_date_range($dates){
		//filter results by a range of dates
		return self::by("(start_date BETWEEN ? AND ? OR end_date BETWEEN ? AND ?)", $dates);
	}//end function by date_range

	public function by_date_range_equipment($dates){
		//filter results by a range of dates
		return self::by("(? BETWEEN start_date AND end_date OR ? BETWEEN start_date AND end_date)", $dates);
	}//end function by date_range

	public function by_id($id){
		return self::by("reservation_idx = ?", $id );
	}//end function by_id


	public function by_wp_id($wp_id){
		//filter results by the wp_id, which is the user that made the reservation
		return self::by("wp_id = ? AND NOT status = 'pending'", $wp_id);
	}//end function by_wp_id


	public function by_wp_id_pending($wp_id){
		//filter results by the wp_id and the fact that the reservation is pending
		//this is used for the user to view their currently pending reservations
		return self::by("wp_id = ? AND status = 'pending'", $wp_id);
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

		$regex = '/^[0-9]+$/';
		preg_match($regex,$user_id, $matches);
		if( !$matches ){//if there are no matches
			$_SESSION['errors'][]='User ID in incorrect format.';
			return false;
		}
		
		if(strlen($user_id)>9){//check to make sure it is less than 9 digits
				$_SESSION['errors'][]='User ID too long.';
				return false;
			}else{
				return $user_id;

			}
	}//end function check_user_id

	public function categories(){
		//this selects the categories that the users can select equipment from
		//This is basically the equipment list
		$sql="
			SELECT categoryID, 
				  category_name 
			  FROM cts_form_options
			 WHERE deleted = false
		   ORDER BY category name";

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
		switch($request->action){
			case "nextweek":
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
				break;
			case "thisweek":
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
				break;
			case "daterange":
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
				break;	
			
			case "lastweek":
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
				break;
			case "today":
			
				//this shows the information from today, which is default
				
				$start_date=date('Y-m-d');
				$fixed_start_date=self::fix_date($start_date);

				$title="Reservations for today -  $fixed_start_date";
				$reservation= self::by_date($start_date);
				break;
				
			case "yesterday":
				//this shows the information from yesterday
				$start_date=date('Y-m-d', strtotime("-1 day"));
				$fixed_start_date=self::fix_date($start_date);

				$title="Reservations for yesterday - $fixed_start_date";
				$reservation= self::by_date($start_date);
				break;

			case "tommorrow":
				//this shows the information from tomorrow
				$start_date=date('Y-m-d', strtotime("+1 day"));
				$fixed_start_date=self::fix_date($start_date);

				$title="Reservations for tomorrow - $fixed_start_date";
				$reservation= self::by_date($start_date);
				break;

			case "pending":
				//this shows any loans that are pending
				$query="pending";

				$title = "Pending Reservations";

				$reservation = self::by_status($query);
				break;

			case "loaned":
				//this shows any loans that are pending
				$query="loaned out";

				$title = "Loaned Reservations";

				$reservation = self::by_status($query);
				break;
			case "outstanding":
				//this shows any loans that are pending
				$query="outstanding";

				$title = "Outstanding Reservations";

				$reservation = self::by_status($query);
				break;

			case "missing":
				//this shows any loans that are pending
				$query="missing";

				$title = "Missing Reservations";

				$reservation =  self::by_status($query);
				break;

			case "detailed":
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
				break;
			default:
				//if there was no parameter, return the dates and reservations for today
				$start_date=date('Y-m-d');
				$fixed_start_date=self::fix_date($start_date);

				$title="Reservations for today - $fixed_start_date";
				$reservation= self::by_date($start_date);
				break;

		}//end switch	
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

	public function format_glpi($glpi_id){
		//check the length of the GLPI_ID id
		if(strlen($glpi_id)!=4 && strlen($glpi_id) !=13 && strlen($glpi_id) !=46 ){
		//make sure that it is either 4 or 13 or 46 (which is URL) characters
			$_SESSION['errors'][]="Incorrect format.";
		}elseif(strlen($glpi_id)==4){
			//if the length is 4, add the prepended characters for the GLPI ID
			$glpi_id='PSU-0000-' . $glpi_id;
		}elseif(strlen($glpi_id)==46){
			//if the code is scanned
			$glpi_id=substr($glpi_id,-13);//return the last 4 digits

		}
		return $glpi_id;
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
		$sql = "
			INSERT INTO cts_form_announcements (
				message, 
				form_viewable
	 		) VALUES (
				?,
				'yes'
			)
		";
		$results = PSU::db('cts')->Execute( $sql, array( $message ) );
		return $results;

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
		$values=array(
			$reservation_idx,
			$subitem_id,
		);
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
			 WHERE deleted = false
		    SORT BY name";
		$locations = PSU::db('cts')->GetAssoc( $sql );
		if( $default == false ){
			$locations = array_merge( array( NULL => 'Please select a location'), $locations );
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
				    man.name as manufacturer,
				   t.name as type
			  FROM glpi_computers item 
			  JOIN glpi_computermodels `mod` 
				ON item.computermodels_id = `mod`.id
				JOIN glpi_manufacturers man
				ON  item.manufacturers_id = man.id
			  JOIN glpi_computertypes t 
				ON item.computertypes_id = t.id 
			 WHERE item.name = ?
			UNION ALL
			SELECT 
				   item.name as psu_name,
				   `mod`.name as model,
 				man.name as manufacturer,
				   t.name as type
			  FROM glpi_peripherals item 
			  JOIN glpi_peripheralmodels `mod` 
				ON item.peripheralmodels_id = `mod`.id
				JOIN glpi_manufacturers man
				ON  item.manufacturers_id = man.id
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
			 WHERE s.deleted=false
			   AND s.reservation_id= ? AND c.id=s.subitem_id";

		return PSU::db('cts')->GetAssoc( $sql, $reservation_id );

	}//end function get subitmes

	public function get_equipment_info($equipment){
		foreach($equipment as $glpi_id){

			if($parts=self::get_GLPI($glpi_id['glpi_id'])){
				$parts[$glpi_id['glpi_id']]['reservation_equipment_idx']=$glpi_id['reservation_equipment_idx'];
				$equipment_info[]= $parts;

			}else{
				$parts=array(
						$glpi_id['glpi_id'] => array(
										"model" => "N/A", 
										"type" => "N/A", 
										"reservation_equipment_idx" => $glpi_id['reservation_equipment_idx']
				));
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
		
		if( IDMObject::authZ('permission', 'cts_admin') ) {
			return 1;
		}elseif( IDMObject::authZ('permission', 'cts') ){
			return 2;
		}elseif( IDMObject::authZ('role', 'calllog') ){
			return 3;
		}else{
			return 4;
		}
	}//end function user level

	public function reservation_sanitize($request){

		$reservation_idx=$request->id;
		$first_name=$request->param('first_name');
		$first_name=filter_var($first_name, FILTER_SANITIZE_STRING);
		$reserve['first_name'] = $first_name;

		$last_name=$request->param('last_name');
		$last_name=filter_var($last_name, FILTER_SANITIZE_STRING);
		$reserve['last_name'] = $last_name; 

		$phone=$request->param('phone');
		$phone=filter_var($phone, FILTER_SANITIZE_STRING);
		$reserve['phone'] = $phone;

		$email=$request->param('email');
		$email=filter_var($email, FILTER_SANITIZE_STRING);
		$reserve['email'] = $email;

		$reserve_type=$request->param('radio');
		$reserve_type=filter_var($reserve_type, FILTER_SANITIZE_STRING);
		$reserve['reserve_type'] = $reserve_type;

		$start_date=$request->param('start_date');//request a parameter for start_date
		$start_date=filter_var($start_date, FILTER_SANITIZE_STRING);
		$reserve['start_date'] = $start_date;

		$end_date=$request->param('end_date');//request a parameter for enddate
		$end_date=filter_var($end_date, FILTER_SANITIZE_STRING);
		$reserve['end_date'] = $end_date;

		$title=$request->param('title');//request a parameter for title
		$title=filter_var($title, FILTER_SANITIZE_STRING);
		$reserve['title'] = $title;

		$location=$request->param('location');//request a parameter for location
		$location=filter_var($location, FILTER_SANITIZE_STRING);
		$reserve['location'] = $location;

		$room=$request->param('room');
		$room=filter_var($room, FILTER_SANITIZE_STRING);
		$reserve['room'] = $room;

		$comments=$request->param('comments');
		$comments=filter_var($comments,FILTER_SANITIZE_STRING);
		$reserve['comments'] = $comments;

		$starthour=$request->param('starthour');
		$starthour=filter_var($starthour, FILTER_SANITIZE_STRING);
		$reserve['starthour'] = $starthour;

		$startminute=$request->param('startminute');
		$startminute=filter_var($startminute, FILTER_SANITIZE_STRING);
		$reserve['startminute'] = $startminute;

		$startminute=sprintf("%02d",$startminute);
		$startampm=$request->param('startampm');
		$startampm=filter_var($startampm, FILTER_SANITIZE_STRING);
		$reserve['startampm'] = $startampm;

		$start_time=$starthour . ':' . $startminute . ' ' . $startampm;

		$endhour=$request->param('endhour');
		$endhour=filter_var($endhour, FILTER_SANITIZE_STRING);
		$reserve['hour'] = $endhour;

		$endminute=$request->param('endminute');
		$endminute=filter_var($endminute, FILTER_SANITIZE_STRING);
		$reserve['endminute'] = $endminute;

		$endminute=sprintf("%02d",$endminute);
		$endampm=$request->param('endampm');
		$endampm=filter_var($endampm, FILTER_SANITIZE_STRING);
		$reserve['endampm'] = $endampm;

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
			$data=array(
				'complete' => false,
				'reserve' => $reserve,
			);
			return $data;
		}

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
		$data=array(
			'complete' => true,
			'cts_admin' => $cts_admin,
			'reserve' => $reserve,
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
		$sql="SELECT
				(SELECT COUNT( reservation_idx ) FROM cts_reservation) count_of_reservations,
				(SELECT COUNT( reservation_equipment_idx ) FROM cts_reservation_equipment) count_of_equipment
			FROM DUAL
			";
		$counts= PSU::db('cts')->GetRow( $sql );
		
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
			'count_of_reservations' => $counts['count_of_reservations'], 
			'count_of_equipment' => $counts['count_of_equipment'],
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
