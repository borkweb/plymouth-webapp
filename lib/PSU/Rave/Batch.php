<?php

namespace PSU\Rave;

class Batch {
	public $util_name;
	public $util_db;
	public $util;
	public $rave_users;
	public $to_process = array();

	public $debug = false;

	/**
	 * constructor
	 */
	public function __construct( $util_name, $util_db = null ) {
		if( ! $util_name ) {
			throw new \UnexpectedValueException('You must specify a PSUScriptUtility process name');
		}//end if

		$this->util_name = $util_name;
		$this->util_db = $util_db ?: \PSU::db( 'banner' );
		$this->util = new \PSUScriptUtility( $this->util_db, $this->util_name );
	}//end constructor

	/**
	 * Clear everything out of the util database for this instance.
	 * This is just here to help us out in case we need to clean up after
	 * ourselves.
	 */
	public function clear_util(){
		$this->util->purge();
	}//end clear_util

	public function flagged_records( $flag ) {
		if( ! $flag ) {
			throw new \UnexpectedValueException('You must pass a flag for PSUScriptUtility selects');
		}//end if

		return $this->util->select( array( 'flag' => $flag ) );
	}//end flagged_records

	public function load_population( $query ) {
		$userfactory = new \PSU_Population_UserFactory_Simple();
		$population = new \PSU_Population( $query, $userfactory );
		$population->query();
		$users = $population->matches;

		return $users;
	}//end load_population

	/**
	 * return non-flagged records
	 */
	public function pending_util_records() {
		return $this->util->select( array( 'flag' => null ) );
	}//end pending_records

	/**
	 * This is a loose way to populate the script utility so that we can run it as different types
	 * of batch process (eg. sync, stop, etc...)
	 */
	public function prepare_script_util( $users ) {
		$this->clear_util();

		foreach( $users as $user ) {
			$args = array(
				'primary_field' => 'wp_id', 
				'primary_field_data' => $user['wp_id'], 
				'field' => 'mobile_number', 
				'field_data' => $user['mobile_number'],
			);

			$this->util->insert( $args );
		}//end foreach
	}//end prepare_script_util

	/**
	 * Return the number of entries remaining in the script utility
	 */
	public function remaining() {
		return count( $this->pending_util_records() );
	}//end remaining
}//end class \PSU\Rave\Batch
