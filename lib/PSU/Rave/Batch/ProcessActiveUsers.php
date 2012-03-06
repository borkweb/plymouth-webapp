<?php

namespace PSU\Rave\Batch;

class ProcessActiveUsers extends \PSU\Rave\Batch {
	/*
	 * constructor
	 */
	public function __construct() {
		parent::__construct( 'rave_process_active_users' );
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

		$this->to_process = $this->eligible_users;

		foreach($this->rave_users as $rave_user) {
			$wp_id = $rave_user['internal_school_id'];

			/*
			 * This checks to see if the user exists in both rave and our active users,
			 * choosing to not act on the user if they exist in both systems
			 */
			if( $this->to_process[$wp_id] ) {
				// If in here, we don't need to add to Rave. Remove from to_process.
				unset( $this->to_process[$wp_id] ); 
			}//end if
		}//end foreach	

		$this->prepare_script_util( $records ?: $this->to_process );
	}//end prepare

	/*
	 * Loop through the array that we have to process, and perform the add action.
	 * I wish there was a way to move the loop logic to the parent class...
	 */
	public function process( $num = 50 ) {
		$select = $this->util->select();

		$selected_users = array_chunk($select, $num, true);

		foreach( (array) $selected_users[0] as $wp_id => $script_info ) {

			$args = array(
				'primary_field' => 'wp_id', 
				'primary_field_data' => $wp_id,
			);

			try {
				if( $rave_user = \PSU\Rave\User::create( $wp_id ) ) {
					$this->util->delete( $args );
				} else {
					$this->util->set_flag( $args, 'createfail' );
				}//end else
			} catch( \Exception $e ) { 
				$this->util->set_flag( $args, 'createfail' );
			}//end catch

			if( $this->debug ) {
				echo ( $rave_user ? 'Created: ' : 'Failed to create: ' ) . $wp_id . "\n";
			}//end if

		}//end foreach

		return count( $this->util->select() );
	}//end process

}//end ProcessActiveUsers
