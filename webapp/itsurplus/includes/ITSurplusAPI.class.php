<?php

class ITSurplusAPI {

	public function by_model( $search = null ){

		$by_model = array();
		$models = self::models( $search );

		foreach( self::items( $search ) as $item ){
			$by_model[ $item['model'] ]['machines'][] = $item;
			$by_model[ $item['model'] ]['type'] = $item['type'];
			$by_model[ $item['model'] ]['manufacturer'] = $item['manufacturer'];
			$by_model[ $item['model'] ]['model'] = $item['model'];
			$by_model[ $item['model'] ]['description'] = ( strlen($by_model[$item['model']]['description']) > 0 ? $by_model[$item['model']]['description'] : $item['description']);
			$by_model[ $item['model'] ]['quantity'] = $models[ $item['model'] ];
			$by_model[ $item['model'] ]['filepath'] = ( is_null($item['filepath']) ? NULL : $item['filepath'] );

			$by_model[ $item['model'] ]['price']['min'] = ( ($item['price'] < $by_model[ $item['model'] ]['price']['min'] || !$by_model[ $item['model'] ]['price']['min']) ? $item['price'] : $by_model[ $item['model'] ]['price']['min'] );
			$by_model[ $item['model'] ]['price']['max'] = ( ($item['price'] > $by_model[ $item['model'] ]['price']['min'] || !$by_model[ $item['model'] ]['price']['max']) ? $item['price'] : $by_model[ $item['model'] ]['price']['max'] );

		}//end foreach

		ksort($by_model);
		return $by_model;

	}//end by_model

	public function get( $type, $search = null ) {

		$query_parts = self::sql( $type, $search );

		$items = PSU::db('mysql/glpi/pdo')->GetAll( $query_parts['sql'], $query_parts['params'] );
		return (array)$items;

	}

	public function items( $search = null ) {
		return array_merge( self::get( 'computer', $search ), self::get( 'peripheral', $search ), self::get( 'printer', $search ), self::get( 'networkequipment', $search ) );
	}//end items

	public function manufacturers( $search = null ) {
		
		$manufacturers = array();
		foreach( self::items( $search ) as $item ) {
			$manufacturers[ $item['manufacturer'] ] = $item['manufacturer'];	
		}//end foreach

		return $manufacturers;

	}//end manufactureres

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

		return PSU::db('mysql/glpi/pdo')->GetAll( $sql );
	}//end notifications

	public function price_range( $search = null ) {
		$prices = self::prices( $search );

		$price_range =  array(
			'min' => (sizeof($prices) > 0 ? min( $prices ) : 0),
			'max' => (sizeof($prices) > 0 ? max( $prices ) : 0),
		);

		return $price_range;
	}//end price_range

	public function prices( $search = null ) {
		$prices = array();

		foreach( self::items( $search ) as $item ) {
			$prices[ $item['price'] ] = $item['price'];
		}//end foreach

		return $prices;
	}//end prices 

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
			  FROM glpi_".$item_type."s item 
			  JOIN glpi_states s 
				ON item.states_id = s.id
			  JOIN glpi_".$item_type."models `mod` 
				ON item.".$item_type."models_id = `mod`.id 
			  JOIN glpi_manufacturers man 
				ON item.manufacturers_id = man.id 
			  JOIN glpi_".$item_type."types t 
				ON item.".$item_type."types_id = t.id 
			  JOIN glpi_infocoms i 
				ON i.items_id = item.id 
		 LEFT JOIN glpi_documents d 
				ON d.name = `mod`.name
			 WHERE s.name = 'Surplused'
			   AND i.itemtype = '".ucfirst( $item_type )."'
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
				$types[] = \PSU::db('mysql/glpi/pdo')->qstr( $type );	
			}//end foreach

			$where[] = "(
				type IN (".implode(",", $types).")	
			)";
		}//end if

		if( isset($search['manufacturer']) ) {
			$manufacturers = array();

			foreach( $search['manufacturer'] as $manufacturer ) {
				$manufacturers[] = \PSU::db('mysql/glpi/pdo')->qstr( $manufacturer );	
			}//end foreach

			$where[] = "(
				manufacturer IN (".implode(",", $manufacturers).")	
			)";
		}//end if

		if( isset($search['model']) ) {
			$models = array();

			foreach( $search['model'] as $model ) {
				$models[] = \PSU::db('mysql/glpi/pdo')->qstr( $model );	
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

}//end class ITSurplusAPI
