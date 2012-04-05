<?php
class reserveDatabaseAPI{

	function addMessage($reservation_idx, $message, $username){
		$date=date('Y-m-d');
		$time=date('G:i:s');
		$data=array($message, $reservation_idx, $date, $time, $username);
		$sql="INSERT INTO cts_reservation_note (message,reservation_idx, date , time, author) VALUES (? , ? ,? , ?, ?)";
		PSU::db('cts')->Execute($sql,$data);

	}//function addMessage

	function by_date($date){
		$sql="
			SELECT * FROM cts_reservation
			WHERE start_date = ? OR end_date =	?
			";
		$dates=array($date,$date);
		return PSU::db('cts')->GetAssoc( $sql, $dates);

	}//end function by_date

	function by_start_date($date){
		$sql="
			SELECT * FROM cts_reservation
			WHERE start_date = ?	
			";
		return PSU::db('cts')->GetAssoc( $sql, $date);

	}//end function by_start_date

	function by_end_date($date){
		$sql="
			SELECT * FROM cts_reservation
			WHERE end_date = ?	
			";
		return PSU::db('cts')->GetAssoc( $sql, $date);

	}//end function by_end_date

	function by_lname($lname){
		$sql="
			SELECT * FROM cts_reservation
			WHERE lname = ?	
			";
		return PSU::db('cts')->GetAssoc( $sql, $lname);

	}//end function by_lname

	function by_fname($fname){
		$sql="
			SELECT * FROM cts_reservation
			WHERE fname = ?	
			";
		return PSU::db('cts')->GetAssoc( $sql, $fname);

	}//end function by_fname

	function by_status($status){
		$sql="
			SELECT * FROM cts_reservation
			WHERE status = ?	
			";
		return PSU::db('cts')->GetAssoc( $sql, $status);

	}//end function by_status

	function by_current_status($status){
		$sql="
			SELECT * FROM cts_reservation
			WHERE current_status = ?	
			";
		return PSU::db('cts')->GetAssoc( $sql, $status);

	}//end function by_status

	function by_building($building){
		$sql="SELECT building_idx  FROM cts_building WHERE name = ?";
		$building_idx=PSU::db('cts')->GetOne( $sql,$building);
		
		$sql="
			SELECT * FROM cts_reservation
			WHERE building_idx = ?	
			";
		return PSU::db('cts')->GetAssoc( $sql, $building_idx);

	}//end function by_building

	function by_title($title){
		$sql="
			SELECT * FROM cts_reservation
			WHERE title = ?	
			";
		return PSU::db('cts')->GetAssoc( $sql, $title);

	}//end function by_status
	
	function by_date_range($dates){
		$sql="
			SELECT * FROM cts_reservation
			WHERE start_date BETWEEN ? AND ? OR end_date BETWEEN ? AND ?	
			";
		return PSU::db('cts')->GetAssoc( $sql, $dates);


	}//end function by date_range

	function by_id($id){
		$sql="
			SELECT * FROM cts_reservation
			WHERE reservation_idx = ?	
			";
		return PSU::db('cts')->GetAssoc( $sql, $id);

	}//end function by_id

	function categories(){
		
		$sql="SELECT categoryID, category_name FROM cts_form_options";

		return PSU::db('cts')->GetAssoc( $sql );

	}//end function categories

	function getFormOptions(){
		
		$sql="SELECT * FROM cts_form_options";

		return PSU::db('cts')->GetAssoc( $sql );

	}//end function get form options


	function deleteReservation($reservation_idx){
		$sql="DELETE FROM cts_reservation WHERE reservation_idx = ?";
		
		PSU::db('cts')->Execute( $sql, $reservation_idx );
	
	}//end function deleteReservation
	
	function deleteEquipment($equipment_id){
		$sql="DELETE FROM cts_form_options WHERE categoryID= ?";

		PSU::db('cts')->Execute( $sql, $equipment_id );

	}//end function deleteEquipment

	function deleteSubitem($subitem_id){
		$sql="DELETE FROM cts_subitem WHERE id= ?";

		PSU::db('cts')->Execute( $sql, $subitem_id );

	}//end function deleteSubitem

	function deleteReserveSubitem($id){
		$sql="DELETE FROM cts_reservation_subitem WHERE reservation_subitem_id=?";

		PSU::db('cts')->Execute( $sql, $id );
		
	}//end function delete reserve subitem


	function deleteMessages($reservation_idx){
		$sql="DELETE FROM cts_reservation_note WHERE reservation_idx = ?";
		
		PSU::db('cts')->Execute( $sql, $reservation_idx );
	
	}//end function deleteReservation

	function insertFormOptions($category, $description){
		$sql="INSERT INTO cts_form_options (category_name, description) VALUES (?,?)";
		$values=array($category, $description);
		PSU::db('cts')->Execute( $sql, $values );

	}//end function insertCategory

	function insertSubitem($name){
		$sql="INSERT INTO cts_subitem (name) VALUES (?)";
		PSU::db('cts')->Execute( $sql, $name );

	}//end function insertCategory
	
	function insertReservationSubitem($reservation_idx, $subitem_id){
		$sql="INSERT INTO cts_reservation_subitem (reservation_id,subitem_id) VALUES (?,?)";
		$values=array($reservation_idx,$subitem_id);
		PSU::db('cts')->Execute( $sql, $values );

	}//end function insertCategory


	function insertReservation($last_name, $first_name, $phone, $email, $application_date, $start_date, $start_time, $end_date, $end_time, $comments, $building, $room, $title, $delivery_type, $requested_items, $status){

		$sql="
		INSERT INTO cts_reservation 
			(lname,
			fname,
			phone,
			email,
			application_date,
			start_date,
			end_date,
			start_time,
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
			?)";
			
		PSU::db('cts')->Execute( $sql, array($last_name, 
			$first_name, 
			$phone, 
			$email, 
			$application_date, 
			$start_date, 
			$end_date, 
			$start_time, 
			$end_time, 
			$comments, 
			$building, 
			$room, 
			$title, 
			$delivery_type, 
			$requested_items, 
			$status));
			
	}//end function insertReservation

	function itemInfo($item_id){
		$sql="SELECT description FROM cts_form_options WHERE categoryID=$item_id";

		return PSU::db('cts')->GetOne( $sql );
	}

	function locations(){

		$sql="SELECT building_idx, name  FROM cts_building";
		return PSU::db('cts')->GetAssoc( $sql );

	}//end function locations

	function getMessages($reservation_idx){
		$sql="SELECT * FROM cts_reservation_note WHERE reservation_idx = ?";
		return PSU::db('cts')->GetAssoc( $sql , $reservation_idx);
	}//end function get messages

	function getSubItems(){
		$sql="SELECT id,name FROM cts_subitem";
		return PSU::db('cts')->GetAssoc( $sql );

	}//end function get subitems


	function getReserveSubItems($reservation_id){
		//$sql="SELECT s.* FROM cts_reservation_subitem s INNER JOIN cts_subitem c ON s.subitem_id = c.id WHERE s.reservation_id= ?";
		$sql="SELECT s.*,c.name FROM cts_reservation_subitem s,cts_subitem c WHERE s.reservation_id= ? AND c.id=s.subitem_id";

		return PSU::db('cts')->GetAssoc( $sql, $reservation_id );

	}//end function get subitmes


	function getEquipment($reservation_idx){
		$sql="SELECT * FROM cts_reservation_equipment WHERE reservation_idx = ?";
		return PSU::db('cts')->GetAssoc( $sql , $reservation_idx);
	}//end fuction get equipment

}//end class reserveDatabaseAPI
