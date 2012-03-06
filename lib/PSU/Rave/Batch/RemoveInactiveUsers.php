<?php

namespace PSU\Rave\Batch;

class RemoveInactiveUsers extends \PSU\Rave\Batch {
	/*
	 * constructor
	 */
	public function __construct() {
		parent::__construct( 'rave_remove_users' );
	}//end __construct

	/*
	 * This function is used to load the necessary population
	 */
	public function load() {
		$query = new \PSU\Population\Query\RaveEligibleUsers();
		$this->eligible_users = $this->load_population( $query );

		$rb = new \Rave\Batch();
		$this->rave_users = $rb->getEnrollmentReport();
	}//end load

	/*
	 * This function is used to populate the necessary user array for processing.
	 *
	 * @param $records \b override the records to be sent to script utility.  Format: array( $wp_id => $wp_id, etc )
	 */
	public function prepare( $records = null ) {
		if( ! $this->eligible_users || ! $this->rave_users ) {
			$this->load();
		}//end if

		foreach($this->rave_users as $rave_user) {
			$wp_id = $rave_user['internal_school_id'];

			if( !$this->eligible_users[$wp_id] && strlen( $wp_id ) > 0 ) {
				// If in here they have a wp_id. Queue them up to remove 'em.
				// NOTE: If no wp_id, they are a consultant/Rave employee
				$this->to_process[$wp_id] = array( 'wp_id' => $wp_id );
			}//end if
		}//end foreach	

		$this->prepare_script_util( $records ?: $this->to_process );
	}//end prepare

	/*
	 * Loop through the array that we have to process, and perform the delete action.
	 * I wish there was a way to move the loop logic to the parent class...
	 */
	public function process( $num = 50 ) {
		$select = $this->util->select();

		$selected_users = array_chunk($select, $num, true);

		foreach( (array)$selected_users[0] as $wp_id => $script_info ) {
			$args = array(
				'primary_field' => 'wp_id', 
				'primary_field_data' => $wp_id,
			);

			if( $rave_user = \PSU\Rave\User::get( $wp_id ) ) {
				if( $deleted = $rave_user->delete() ) {
					$this->util->delete( $args );
				} else {
					$this->util->set_flag( $args, 'deletefail' );
				}//end else

				if( $this->debug ) {
					echo ( $deleted ? 'Removed: ' : 'Failed to remove: ' ) . $wp_id;
				}//end if
			} else {
				// if this user doesn't have a Rave account, delete the util record and move on
				$this->util->delete( $args );
			}//end else

		}//end foreach

		return count( $this->util->select() );
	}//end process

}//end RemoveInactiveUsers
