<?php

namespace Calllog;

/**
 * A group of tickets.
 */
class Tickets extends Collection {
	static $_name = 'Tickets';
	static $child = '\Calllog\Ticket';
	static $child_key = 'call_id';
	static $parent_key = 'call_id';
	static $table = 'call_log';

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

}//end class Ticket
