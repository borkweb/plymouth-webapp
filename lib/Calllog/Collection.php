<?php

namespace Calllog;

/**
 * CallLog-specific Collection.
 */
class Collection extends \PSU\Collection {
	/*
	 * Basic constructor
	 */
	public function __construct( $id = null ) {
		$this->_collection_key = $id;
	}

	/*
	 * Returns array of keys which can be used to add children to a collection
	 */
	public function get( ) {
		
		// Make all the static values cleaner
		$table = static::$table;
		$possible_keys = static::$possible_keys;
		$child_key = static::$child_key;

		// If not array, default to the child key 
		if ( ! is_array( $this->_collection_key ) ) {
			$collection = array( $child_key => $this->_collection_key );
		}
		else {
			$collection = $this->_collection_key;
		}

		$arg_count = 0;
		$where = '';
		$value_array = array();

		// Loop through to build the SQL WHERE
		foreach( $collection as $key=>$value ) {
			if ( in_array( $key, $possible_keys, true ) ) { 
				// Add an and to append each statement
				if ( $arg_count++ != 0 ) {
				    $where .= " AND ";	
				}

				$where .= " {$key} = ?"; 
				$value_array[] = $value;
			}
		}

		// Query for the desired collection	
		$sql = "SELECT {$child_key} FROM {$table} WHERE {$where} ORDER BY {$child_key} DESC"; 
		$call_entry = \PSU::db('calllog')->GetAll ( $sql, $value_array );

		$ticketArr = array();
		// Clean up the array
		foreach ( $call_entry as $id ) {
				$ticketArr[] = $id[$child_key];
		}
		return $ticketArr;
	}
}//end class Collection
