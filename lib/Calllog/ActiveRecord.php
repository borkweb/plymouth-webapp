<?php

namespace Calllog;

/*
 * CallLog-specific Active Record pattern.
 */
abstract class ActiveRecord extends \PSU_DataObject implements \PSU\ActiveRecord {
	static $rowcache = null;	
	static $table = null;

	/*
	 * Default constructor
	 */
	public function __construct( $row = null ) {
		// Is row actually a row identifier
		if( $row && ! is_array( $row ) ) {
			$row = static::row( $row );
		}
		parent::__construct( $row );
	}
	
	/*
	 * Implement ActiveRecord::delete(). Currently disabled.
	 */
	public function delete() {
		$tableKey = static::$table_key;

		$key = $this->$tableKey; 

		throw new \Exception( 'deletion is currently disabled' );

		if( !static::$table ) 
			throw new \Exception( 'static::$table must be defined' );
		if( !static::$table_key )
			throw new \Exception( 'static::$table_key must be defined' );

		$table = static::$table;
		$table_key = static::$table_key;

		$sql = "DELETE FROM {$table} WHERE {$table_key} = ?";
		$result = \PSU::db('calllog')->Execute( $sql, $this->$key );		

	}//end delete

	/*
	 * Get a single object of the static type.
	 */
	public static function get( $key ) {
		static $cache = array();

		// If the key is not a usable value throw an error
		if( !is_scalar( $key ) ) {
			throw new \InvalidArgumentException( 'key must be scalar' );
		}//end if
		$class_name = get_called_class();

		$idx = "{$class_name}-{$key}-{$field->value}";

		if( !isset( $cache[$idx] ) ) {
			$args = null;		// Initiate the args

			// Get the values	
			if( null !== self::$rowcache ) {
				$args = self::$rowcache->get( get_called_class(),	$key, $key );
			}
						
			if( null === $args ) {
				$args = $key;
			}
				
			$obj = new static( $args );
	
			$cache[$idx] = $obj;
		}
		return $cache[$idx];
	}//end get

	/*
	 * Return a record array from the data store, identified
	 * by the given key
	 *
	 * @return array
	 */
	public static function row( $key ) {
		$table = static::$table;

		// Define the paremeter we are searching by	
		$tableKey = static::$table_key;	

		$where = "{$tableKey} = {$key}";
			
		$sql = "SELECT * FROM {$table} WHERE {$tableKey} = ?";
		
		$row = \PSU::db('calllog')->GetRow( $sql, $key );

		if ( !$row ) { 
			return false;
		}
		return $row;	
	}//end row


	/*
	 * Take in an array of arguments that includes the key
	 * Use that array to update the table row for that key
	 */
	public static function update( $args ) {
		$table = static::$table;
		$possible_keys = static::$possible_keys;
		$tableKey = static::$table_key;

		// Get all fields that were updated
		$changed_values = "UPDATE {$table} SET ";
		$values_array = array();
		$arg_count = 0;

		// Get all fields that were updated
		foreach( $args as $key=>$value ) {
			// Check to make sure it is a valid field, if not throw it away
			// Cannot update the key for the record
			if( in_array( $key, $possible_keys, true ) && $key != $tableKey ) {
				if( $arg_count++ != 0 ) {
					$changed_values .= ", ";
				}
				$changed_values .= "{$key} = ?";
				$values_array[]  = $value;
			}	
		}	

		// If there were no changes quit before the db call
		if ( 0 == $arg_count ) {
			return false;	
		}

		// Verify the key exists before trying to update it
		$probe = \PSU::db('calllog')->GetRow( "SELECT {$tableKey} FROM {$table} WHERE {$tableKey} = ?", $args[$tableKey] );
		if ( !$probe ) {
			return false;
		}

		// Add the table key at the end 
		$values_array[] = $args[$tableKey];

		// Create the update statement
		$changed_values .= " WHERE {$tableKey} = ?";
		$result = \PSU::db('calllog')->Execute( $changed_values, $values_array );

		if ( $result ) {
			return true;
		}
		else {
			return false;
		}
	}//end update


	/*
	 * Take in the Active record
	 * Format the data in the record into an array
	 * Send the data to the update function
	 */
	public function save( $method = 'merge' ) {
		//throw new \Exception( 'saving is currently disabled' );

		$update_array = $this->save_fields();

		$this->update( $update_array );

	}//end save
}//end class ActiveRecord
