<?php
//Most of this class was written by Nathan Porter and was modified to work with the CTS System
//don't reinvent the whel
class CTSDatabaseAPI {

	public function by_model( $search = null ){
		//searches GLPI items by model

		$by_model = array();
		$models = self::models( $search );

		foreach( self::items( $search ) as $item ){
			$by_model[ $item['model'] ]['machines'][] = $item;
			$by_model[ $item['model'] ]['type'] = $item['type'];
			$by_model[ $item['model'] ]['manufacturer'] = $item['manufacturer'];
			$by_model[ $item['model'] ]['model'] = $item['model'];
			$by_model[ $item['model'] ]['description'] = ( strlen($by_model[$item['model']]['description']) > 0 ? $by_model[$item['model']]['description'] : $item['description']);
			$by_model[ $item['model'] ]['quantity'] = $models[ $item['model'] ];
		}//end foreach

		ksort($by_model);
		return $by_model;

	}//end by_model


	
	public function count( $GLPI_ID ){
		//grabs the count for how many times a specific item has been reserved
		$sql="SELECT COUNT(glpi_id) FROM cts_reservation_equipment WHERE glpi_id = ?";
		return PSU::db('cts')->GetOne( $sql, $GLPI_ID );

	}//end count

	public function get( $type, $search = null ) {

		$query_parts = self::sql( $type, $search );
		$items = PSU::db('glpi')->GetAll( $query_parts['sql'] , $query_parts['params']);
		return (array)$items;

	}

	public function items( $search = null ) {
		return self::get( 'computer', $search );
	}//end items

	public function manufacturers( $search = null ) {
		
		$manufacturers = array();
		foreach( self::items( $search ) as $item ) {
			$manufacturers[ $item['manufacturer'] ] = $item['manufacturer'];	
		}//end foreach

		return $manufacturers;

	}//end manufactureres

	public function model_keys($models){
		return array_keys(self::models($models));

	}//end function model_keys

	public function models( $search = null ) {
		
		$models = array();
		foreach( self::items( $search ) as $item ) {
			$models[ $item['model'] ]++;	
		}//end foreach

		ksort( $models );
		return $models;

	}//end models

	public function notifications( $all = false) {

		$sql = "
			SELECT r.id,
				   r.name,
				   r.text,
				   r.begin,
				   r.end,
				   u.name as 'username'
			  FROM glpi_reminders r,
			       glpi_users u
			 WHERE r.name LIKE '%Surplus%'
			   AND r.users_id = u.id
		";

		if( !$all ) {
			$sql .= " AND (r.begin <= NOW() OR r.begin IS NULL) AND (r.end >= NOW() OR r.end IS NULL)";
		}

		return PSU::db('glpi')->GetAll( $sql );
	}//end notifications


	public function reservation_information($dates){
		//grab the reservation indexes for the specific week
		$reservations=self::reservation_by_range_equipment($dates);
		
		//grab the equipment information for each reservation and make an array
		//that has the reservation idx, start and end date and time 
		//and all of the equipment information for that reservation
		foreach($reservations as $reservation){
			$part=ReserveDatabaseAPI::get_equipment($reservation['reservation_idx']);
			$information=ReserveDatabaseAPI::get_equipment_info($part);
			$equipment[]=$reservation + array('equipment'=> $information);
				
		}
		return $equipment;
	}

	function equipment_by_date($dates){

		$reservations=self::reservation_by_range_equipment($dates);
		
		//This grabs all of the information for the reservations during a period of time
		//it then grabs all of the equipment from said loan
		//it takes all of the information and re organizes it to be used for this purpose
		//an array is created as follows: (glpi_id=>reservation start date, reservation end_date)
		//
		//this returns the equipment that is reserved during the said week, with the glpi_id as the key and the reservation_idx and the start_date and end_date of the loan 

		foreach($reservations as $reservation){
			$part=ReserveDatabaseAPI::get_equipment($reservation['reservation_idx']);
			foreach($part as $glpi){
				if($id=$glpi['glpi_id']){
					$equipment[$id]['reservations'][]=array('reservation_idx'=>$reservation['reservation_idx'],'start_date'=>$reservation['start_date'], 'end_date' => $reservation['end_date']);
				
				}
			}
		}
		return $equipment;
		//return $reservations;



	}//function reservation_by_equipment

	function gantt_view_by_equipment($items, $dates){
	//this function takes the list of equipment, grabs the reservations
	//from the dates given and returns a list with the reservation
	//start date, end date and index with the glpi_id of the equipment as the key
		$reservations=self::reservation_by_range_equipment($dates);
		//grab the equipment list for said week
		foreach($reservations as $reservation){
			//grab the list of equipment from all of the reservations
			$equipment[]=ReserveDatabaseAPI::get_equipment($reservation);
			
		}

		foreach($items	as $item){
			//grab the glpi id from the item
			$glpi_id=$item['psu_name'];

		}
		return $equipment;
	}//function gantt_view_by_equipment


	function reservation_by_range_equipment($dates){
		//filter results by a range of dates
		$start_date=$dates[0];
		$end_date=$dates[1];
		$dates=array($start_date, $start_date, $end_date, $start_date, $end_date, $end_date);
		$sql="
			SELECT * FROM cts_reservation
			WHERE 
			? BETWEEN start_date AND end_date
			OR 
			end_date BETWEEN ? AND ?
			OR 
			start_date BETWEEN ? AND ?
			OR
			? BETWEEN start_date AND end_date
			ORDER BY reservation_idx DESC
		
			";
		return PSU::db('cts')->GetAll( $sql, $dates);

	}//end function reservation_by_range_equipment


	public function reservation_by_range($dates){
		//filter results by a range of dates
		$sql="
			SELECT * FROM cts_reservation
			WHERE start_date BETWEEN ? AND ? OR end_date BETWEEN ? AND ?
			ORDER BY reservation_idx DESC	
			";
		
		return PSU::db('cts')->GetAll( $sql, $dates);


	}//end function by date_range


	public function sql( $item_type, $search = null) {
		
		$sql = "
			SELECT item.id,
				   item.name as psu_name,
				   item.serial,
				   item.notepad as notes,
				   s.name as state,
				   `mod`.name as model,
				   man.name as manufacturer,
				   t.name as type,
				   i.warranty_value as price,
				   i.warranty_info as `condition`,
				   i.comment as description,
				   d.filepath
			  FROM glpi_computers item 
			  JOIN glpi_states s 
				ON item.states_id = s.id
			  JOIN glpi_computermodels `mod` 
				ON item.computermodels_id = `mod`.id 
			  JOIN glpi_manufacturers man 
				ON item.manufacturers_id = man.id 
			  JOIN glpi_computertypes t 
				ON item.computertypes_id = t.id 
			  JOIN glpi_infocoms i 
				ON i.items_id = item.id 
		 LEFT JOIN glpi_documents d 
				ON d.name = `mod`.name
			   AND i.itemtype = 'Computer'
				WHERE s.name= 'Available for Loan'
			UNION ALL
				SELECT item.id,
				   item.name as psu_name,
				   item.serial,
				   item.notepad as notes,
				   s.name as state,
				   `mod`.name as model,
				   man.name as manufacturer,
				   t.name as type,
				   i.warranty_value as price,
				   i.warranty_info as `condition`,
				   i.comment as description,
				   d.filepath
			  FROM glpi_peripherals item 
			  JOIN glpi_states s 
				ON item.states_id = s.id
			  JOIN glpi_peripheralmodels `mod` 
				ON item.peripheralmodels_id = `mod`.id 
			  JOIN glpi_manufacturers man 
				ON item.manufacturers_id = man.id 
			  JOIN glpi_peripheraltypes t 
				ON item.peripheraltypes_id = t.id 
			  JOIN glpi_infocoms i 
				ON i.items_id = item.id 
		 LEFT JOIN glpi_documents d 
				ON d.name = `mod`.name
			   AND i.itemtype = 'Peripheral'
				WHERE s.name= 'Available for Loan'

			   ";
		if( $search ) {
			$sql_components = self::where( $sql, $search );
		} else {
			$sql_components = array('sql' => $sql, 'params' => NULL);
		}//end else

		return $sql_components;

	}//end sql

	public function types( $search = null ) {
		$types = array();

		foreach( self::items( $search ) as $item ) {
			$types[ \PSU::createSlug( $item['type'] ) ] = $item['type'];	
		}//end foreach

		return $types;
	}//end types

	public function where( $sql, $search ) {

		$params = array();

		if( isset($search['condition']) ) {
			$params[] = $search['condition'];
			$condition_where = "(`condition` LIKE ?";

			if( $search['condition'] == 'Good' ) {
				$condition_where .= " OR `condition` IS NULL";
			}//end if

			$condition_where .= ")";
			$where[] = $condition_where;
		}//end if

		if( isset($search['type']) ) {
			$types = array();

			foreach( $search['type'] as $type ) {
				$types[] = \PSU::db('glpi')->qstr( $type );	
			}//end foreach

			$where[] = "(
				type IN (".implode(",", $types).")	
			)";
		}//end if

		if( isset($search['manufacturer']) ) {
			$manufacturers = array();

			foreach( $search['manufacturer'] as $manufacturer ) {
				$manufacturers[] = \PSU::db('glpi')->qstr( $manufacturer );	
			}//end foreach

			$where[] = "(
				manufacturer IN (".implode(",", $manufacturers).")	
			)";
		}//end if

		if( isset($search['model']) ) {
			$models = array();

			foreach( $search['model'] as $model ) {
				$models[] = \PSU::db('glpi')->qstr( $model );	
			}//end foreach

			$where[] = "(
				model IN (".implode(",", $models).")	
			)";
		}//end if

		if( isset($search['price']) ) {
			$price = explode(' - ', $search['price']);

			$params[] = ltrim($price[0], '$');
			$params[] = ltrim($price[1], '$');

			$where[] = "(
				price >= ? 
			AND price <= ?	
			)";
		}//end if

		if( isset($search['search_term']) && strlen( $search['search_term'] ) > 0 ) {

			for( $i = 0; $i < 7; $i++ ) {
				$params[] = $search['search_term'];
			}//end for
			
			$where[] = "(
				id LIKE ? 
			 OR psu_name LIKE CONCAT('%',?,'%') 
			 OR notes LIKE CONCAT('%',?,'%')
			 OR model LIKE CONCAT('%',?,'%')
			 OR manufacturer LIKE CONCAT('%',?,'%')
			 OR type LIKE CONCAT('%',?,'%')
			 OR price LIKE CONCAT('%',?,'%')
			)";
			
		}//end if

		if( sizeof( $where ) > 1 ){
			$where_str = "WHERE " . implode(" AND ", $where);
		} elseif( sizeof( $where ) == 1 ) {
			$where_str = "WHERE " . $where[0];
		} else {
			$where_str = "";
		}//end else

		return array( 'sql' => "SELECT i.* FROM (".$sql.") i ".$where_str, 'params' => $params );

	}//where

}//end class CTSdatabaseAPI
