<?php

class CTSdatabaseAPI{

	public function get( $type, $search = null ){
		
		$query_parts = self::sql( $type, $search );

		$items = PSU::db('mysql/glpi/pdo')->GetAll ($query_parts['sql'], $query_parts['params'] );
		return (array)$items;

	}//end function get

	public function sql( $item_type, $search = null){
		$sql= "
		SELECT item.id,
			item.name,
			item.serial,
			item.notepad as notes,
			s.name as state,
			`mod`.name as model,
			man.name as manufacturer,
			t.name as type,
			i.comment as description,
			d.fllepath
		FROM glpi_".$item_type."s item
		JOIN glpi_states s
		  ON item.states_id = s.id
		JOIN glpi_".$item_type."models `mod`
		  ON item."$item_type."models_id = `mod`.id
		JOIN glpi_manufacturers man
		  ON item.manufacturers_id = man.id
		JOIN glpi_".$item_type."types t
		  ON item."$item_type."types_id = t.id
		JOIN glpi_infocoms i
		  ON i.items_id = item.id
	LEFT JOIN glpi_documents d
		  ON d.name = `mod`.name
	    WHERE s.name <> 'Surplused'
	      AND i.itemtype='".ucfirst( $item_type )."'

		 ";

		if( $search ){
			$sql_components = self::where( $sql, $search );
		}else{
			$sql_components = array('sql' => $sql, 'params' => NULL);
		}

		return $sql_componenets;
	}//end function sql

	public function types ( $search = null ){
		$types = array();

		foreach( self::items( $search ) as $item){
			$types[\PSU::createSlug( $item['type']) ] = $item['type'];
		}

		return $types;
		
	}//end function types


}//end class CTSdatabaseAPI

