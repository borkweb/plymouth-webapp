<?php
class reserveDatabaseAPI{

	function categories(){
		
		$sql="SELECT * FROM cts_categories";

		return PSU::db('cts')->GetAssoc( $sql );

	}//end function categories

	function locations(){

		$sql="SELECT building_idx, name  FROM cts_building";
		return PSU::db('cts')->GetAssoc( $sql );

	}//end function locations

}//end class reserveDatabaseAPI
