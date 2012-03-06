<?php

namespace PSU\Rave\Batch;

class ProcessRaveStops extends \PSU\Rave\Batch {
	/*
	 * constructor
	 */
	public function __construct() {
		parent::__construct( 'stops_from_rave' );
	}//end __construct

	/*
	 * This function is used to load the necessary population
	 */
	public function load() {
		$rb = new \Rave\Batch();
		$this->rave_users = $rb->getEnrollmentReport();
	}//end load

	/*
	 * This function is used to populate the necessary user array for processing.
	 *
	 * @param $records \b override the records to be sent to script utility.  Format: array( $wp_id => $wp_id, etc )
	 */
	public function prepare( $records = null ) {
		if( ! $this->rave_users ) {
			$this->load();
		}//end if

		foreach($this->rave_users as $rave_user) {
			$wp_id = $rave_user['internal_school_id'];

			//Is there a stop flag?
			if( $rave_user['primary_mobile_stop_flag'] ) {
				//They are flagged to stop. Lets put them in for processing.
				$this->to_process[$wp_id] = array( 
					'wp_id' => $wp_id,
			   		'mobile_number => $rave_user['mobile_phone_1'],
				);
			}//end if
		}//end foreach	

		$this->prepare_script_util( $records ?: $this->to_process );
	}//end prepare

	/*
	 * Loop through the array that we have to process, and perform the stop action.
	 */
	public function process( $num = 50 ) {
		$select = $this->util->select();

		$selected_users = array_chunk($select, $num, true);

		foreach( (array) $selected_users[0] as $wp_id => $script_info ) {

			//Try to opt out the users phone.
			$args = array(
				'primary_field' => 'wp_id', 
				'primary_field_data' => $wp_id,
			);

			$phone_args = array(
				'wp_id' => $wp_id,
				'phone' => $script_info['mobile_number'],
			);
			$phone = new \PSU\Rave\Phone( $phone_args );
			$saved = $phone->save('opted_out');

			//Sis the phone save?
			if( $saved ) {
				//The phone was opted out, remove user from utility.
				$this->util->delete( $args );
			} else {
				//Expire failed, set flag.
				$this->util->set_flag( $args, 'expirefail' );
			}//end else

			if( $this->debug ) {
				echo ( $expired ? 'Expired: ' : 'Failed to expire: ' ) . $wp_id . "\n";
			}//end if
		}//end foreach

		return count( $this->util->select() );
	}//end process

}//end ProcessRaveStops
