<?php
/*
 *   Inventory class used to grab GLPI Inventory 
 *   Written by David Allen
 *
 */
namespace PSU;
class Inventory extends \PSU\Collection{
	//create variables
	public $search;
	static $_name = 'Inventory';
	static $child = 'PSU\\Inventory\\Item';
	//constuctor class
	public function __construct( $args = array() ){
		//grab and assign search
		$args = \PSU::params( $args );
		$this->search = $args['search'];
	}//end construct

	public function get(){
		$query_parts = self::sql( $args );

		$items = \PSU::db('mysql/glpi/pdo')->GetAll( $query_parts['sql'], $query_parts['params'] );
		return (array)$items;

	}//end get
	
	public function sql( $args  ) {
		$args = array(
			'item_type' => 'computer',
			'item_status' => 'Surplused',
			);
		$item_type = $args['item_type'];
		$item_status = $args['item_status'];
		
		$sql = "
			SELECT item.id,
				   item.name as PSU_name,
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
			 WHERE s.name = '".$item_status."'
			   AND i.itemtype = '".ucfirst( $item_type )."'
		";

		$args = array(
				'sql' => $sql, 
		);

		if( $search ) {
			$sql_components = self::where( $args );
		} else {
			$sql_components = array('sql' => $sql, 'params' => NULL);
		}//end else
		return $sql_components;


	}//end sql


	public function where( $args ) {
		$search = $this->search;
		$sql = $args['search'];
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
			 OR PSU_name LIKE CONCAT('%',?,'%') 
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
}//end Inventory

