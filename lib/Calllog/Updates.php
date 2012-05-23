<?php

namespace Calllog;

/**
 * 
 */
class Updates extends Collection {
	static $child = '\Calllog\Update';
	static $child_key = 'id';
	static $parent_key = 'call_history_system_id';
	static $table = 'call_history';

	// Create an array of possible fields
	static $possible_keys = array( 
		'call_id', 'current', 'updated_by', 
		'tlc_assigned_to', 'its_assigned_group', 
		'comments', 'datetime_assigned', 
		'date_assigned', 'time_assigned', 
		'call_status', 'call_priority' 
	);
	
}//end class Updates
