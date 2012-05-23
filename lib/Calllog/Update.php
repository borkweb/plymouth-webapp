<?php

namespace Calllog;

/**
 * An update to a ticket.
 */
class Update extends ActiveRecord {
	static $table = 'call_history';
	static $_name = 'Ticket';	
	static $table_key = 'id';

	// Create an array of possible fields
	static $possible_keys = array( 
		'call_id', 'current', 'updated_by', 
		'tlc_assigned_to', 'its_assigned_group', 
		'comments', 'datetime_assigned', 
		'date_assigned', 'time_assigned', 
		'call_status', 'call_priority' 
	);


	/*
	 * Take in changed fields through args
	 * Check for validity then put into DB
	 */
	public function create_new_update( $args ) {
		$table = static::$table;
		$possible_keys = static::$possible_keys;

		// Fill in previous data gaps if possible 
		if ( null == $args['datetime_assigned'] ) {
			// If possible build date-time from other values
			if ( null != $args['date_assigned'] && null != $args['time_assigned'] ) {
				$args['datetime_assigned'] = "{$args['date_assigned']} {$args['time_assigned']}"; 
			}
			// If no time default to 00:00:00
			elseif ( null != $args['date_assigned'] ) {
				$args['datetime_assigned'] = "{$args['date_assigned']} 00:00:00";
			}
		}

		// Prep args to create a new
		$insert_string = "INSERT INTO {$table} (";
		$values_string = "VALUES (";
		$values_array  = array();
		$cnt = 0;

		// Get all fields that were updated
		foreach( $args as $key=>$value ) {
			// Check to make sure it is a valid field, if not throw it away
			if( in_array( $key, $possible_keys, true ) && $value != null  ) {
				// Only add a comma if there is a value before it
				if( $cnt++ != 0 ) {
					$insert_string .= ", ";
					$values_string .= ", ";
				}
				$insert_string .= $key;
				$values_string .= "?";
				$values_array[] = $value;
			}	
		}
		
		$updateSQL = "{$insert_string} ) {$values_string} )";	
		$result = \PSU::db('calllog')->Execute( $updateSQL, $values_array );
		$ticket_id = \PSU::db('calllog')->Insert_ID();	

		// Create a new update object
		if ( $result ) {
			$update = new Update($ticket_id);
			return $update;
		}
		return false;

		// Set the values up into a string to merge
	}//end update

	/*
	 * Return an array of fields of
	 * the object to update
	 */
	public function save_fields() {

		$update_array = array(
			'call_id'				=> $this->call_id,  
			'current'				=> $this->current, 
			'updated_by'			=> $this->updated_by,
			'tlc_assigned_to'		=> $this->tlc_assigned_to,
			'its_assigned_group'	=> $this->its_assigned_group,
			'comments'			=> $this->comments,
			'datetime_assigned'		=> $this->datetime_assigned,
			'date_assigned'		=> $this->date_assigned,
			'time_assigned'		=> $this->time_assigned,
			'call_status'			=> $this->call_status,
			'call_priority'		=> $this->call_priority,
		);

		return $update_array;
	}


}//end class Update
