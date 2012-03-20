<?php
class reserveDatabaseAPI{


	function categories(){
		
		$sql="SELECT categoryID, category_name FROM cts_form_options";

		return PSU::db('cts')->GetAssoc( $sql );

	}//end function categories

	function insertReservation($data){
		$sql="INSERT INTO cts_reservation";

		PSU::db('cts')->Execute( $sql );
			
	}//end function insertReservation

	function itemInfo($item_id){
		$sql="SELECT description FROM cts_form_options WHERE categoryID=$item_id";

		return PSU::db('cts')->GetOne( $sql );
	}

	function locations(){

		$sql="SELECT building_idx, name  FROM cts_building";
		return PSU::db('cts')->GetAssoc( $sql );

	}//end function locations

}//end class reserveDatabaseAPI
