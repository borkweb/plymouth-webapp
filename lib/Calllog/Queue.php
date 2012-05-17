<?php

namespace Calllog;

/**
 * A queue of tickets.
 */
class Queue extends ActiveRecord {
	static $table = 'itsgroups';
	static $_name = 'Queue'; 
	static $table_key = 'itsgroupid';

	// Define all possible keys for ticket
	static $ticket_possible_keys = array( 
		'wp_id', 'pidm', 'call_status', 
		'caller_username', 'caller_first_name', 
		'caller_last_name', 'caller_phone_number', 
		'calllog_username', 'call_type', 
		'keywords', 'call_time', 'call_date',
		'other', 'location_building_id', 
		'location_building_room_number',
		'location_call_logged_from', 'title',
		'feelings_face', 'feelings'	
	);

	// Define all possible keys for history 
	static $history_possible_keys = array( 
		'call_id', 'current', 'updated_by', 
		'tlc_assigned_to', 'its_assigned_group', 
		'comments', 'datetime_assigned', 
		'date_assigned', 'time_assigned', 
		'call_status', 'call_priority' 
	);


	/*
	 * Get all open calls for the ITS Group
	 */
	public function get_open_calls ( ) {
		// Set up the parameters for the search
		$params = array(
			'call_status' => 'open',
			'its_assigned_group' => $this->itsgroupid,
		);

		$open_tickets = Queue::search( $params );

		return $open_tickets;
	}

	/*
	 * Returns an array of tickets based on the search parameters
	 */
	public static function search( $param ) {
		$ticket_possible_keys = static::$ticket_possible_keys;
		$history_possible_keys = static::$history_possible_keys;	
	
		// Build a search that will only look at the most recent history 
		$sql = "SELECT * FROM call_log, call_history WHERE call_log.call_id = call_history.call_id AND call_history.current='1' ";

		$values_array = array();

		// Loop through to build the SQL WHERE
		foreach( $param as $key=>$value ) {
			// See if the key is valid, only take call status from history 
			if ( in_array( $key, $ticket_possible_keys, true ) && 'call_status' != $key ) { 
				$sql .= " AND call_log.{$key} = ?"; 
				$values_array[] = $value;
			}
			elseif ( in_array( $key, $history_possible_keys, true ) ) { 
				$sql .= " AND call_history.{$key} = ?"; 
				$values_array[] = $value;
			}
		}

		$result = \PSU::db('calllog')->GetAll($sql, $values_array );
		return $result;
	}
}//end class Queue
