<?php

namespace Calllog;

/*
 * A single ticket object.
 */
class Ticket extends ActiveRecord {
	static $table = 'call_log';
	static $_name = 'Ticket';	
	static $table_key = 'call_id';

	// Create an array of possible fields
	static $possible_keys = array( 
		'call_id', 'wp_id', 'pidm', 'call_status', 
		'caller_username', 'caller_first_name', 
		'caller_last_name', 'caller_phone_number', 
		'calllog_username', 'call_type', 
		'keywords', 'call_time', 'call_date',
		'other', 'location_building_id', 
		'location_building_room_number',
		'location_call_logged_from', 'title',
		'feelings_face', 'feelings'	
	);


	/*
	 * Create a new ticket
	 *  Take in the user values and create a new database entry
	 *  Once the entry is in the database create a new ticket item
	 */
	public static function create_new_ticket ( $args ) {
		$table = static::$table;
		$possible_keys = static::$possible_keys;

		// Get the date and the time
		if ( null == $args['call_date'] && null == $args['call_time'] ) {
			$args['call_time'] = date("H:i:00");
			$args['call_date'] = date("Y-m-d");
		}
		
		// When a ticket is created defaults to open
		if ( null == $args['call_status'] ) {
			$args['call_status'] = 'open';
		}

		// Initialize all the query construction variables
		$insert_string = "INSERT INTO {$table} (";
		$values_string = 'VALUES (';
		$values_array = array();
		$cnt = 0;

		// Get all fields that were updated
		foreach( $args as $key=>$value ) {
			// Check to make sure it is a valid field, if not throw it away
			if( in_array( $key, $possible_keys, true )  ) {
				// Only add a comma if there is a value before it
				if( $cnt++ != 0 ) {
					$insert_string .= ", ";
					$values_string .= ", ";
				}
				$insert_string .= "{$key}";
				$values_string .= "?";
				$values_array[] = $value;
			}	
		}

		$updateSQL = "{$insert_string}) {$values_string})";	

		$result = \PSU::db('calllog')->Execute( $updateSQL, $values_array );
		$call_id = \PSU::db('calllog')->Insert_ID();	

		// If success create a new update as well
		if ( $result ) {
			$args['date_assigned'] = $args['call_date'];
			$args['time_assigned'] = $args['call_time'];
			$args['datetime_assigned'] = $args['call_date'] . ' ' . $args['call_time'];
			$args['call_id'] = $call_id;

			$origin_update = Update::create_new_update($args);
		}
		// Create a new Ticket object
		$ticket = new Ticket($call_id);
		return $ticket;
	}

	public function save_fields() {
		// Prepare the record to be sent as an update()
		$update_array = array (
			'call_id'						=> $this->call_id,
			'wp_id'						=> $this->wp_id,
			'pidm'						=> $this->pidm,
			'call_status'					=> $this->call_status,
			'caller_username' 				=> $this->caller_username,
			'caller_first_name' 			=> $this->caller_first_name,
			'caller_last_name' 				=> $this->caller_last_name,
			'caller_phone_number' 			=> $this->one_number,
			'call_type' 					=> $this->call_type,
			'call_date'					=> $this->call_date,
			'keywords'					=> $this->keywords,
			'other'						=> $this->other,
			'location_building_id' 			=> $this->location_building_id,
			'location_building_room_number'	=> $this->location_building_room_number,
			'location_call_logged_from'		=> $this->location_call_logged_from,
			'title'						=> $this->title,
			'feelings_face'				=> $this->feelings_face,
			'feelings'					=> $this->feelings,
		);
		return $update_array;	
	}
}//end class Ticket
