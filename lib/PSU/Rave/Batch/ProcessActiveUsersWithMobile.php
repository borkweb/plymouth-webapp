<?php

namespace PSU\Rave\Batch;

class ProcessActiveUsersWithMobile extends \PSU\Rave\Batch {
	/*
	 * constructor
	 */
	public function __construct() {
		parent::__construct( 'rave_process_active_users_with_mobile' );
	}//end __construct

	/*
	 * This function is used to load the necessary population
	 */
	public function load() {
		$query = new \PSU\Population\Query\RaveEligibleUsersWithMobile();
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

			if( $rave_user = \PSU\Rave\User::get( $wp_id ) ) {
				//If the user exists in Rave, but has no phone numbers, lets add their Banner mobile number unconfirmed
				if( $rave_user->phones()->count() === 0 ) {

					$phone_args = array( 
						'wp_id' => $wp_id, 
						'phone' => $script_info['mobile_number'], 
					); 
					$phone = new \PSU\Rave\Phone( $phone_args ); 
					$phone->save('no_confirm'); 
					try {
						if( $rave_user->save() ) {
							$this->util->delete( $args );
						} else {
							$this->util->set_flag( $args, 'savefail' );
						}//end else
					} catch( \Exception $e ) {
						$this->util->set_flag( $args, 'savefail' );
					}//end catch
				}//end if
			} else {
				
				try {
					$rave_user = \PSU\Rave\User::create( $wp_id );

					$phone_args = array( 
						'wp_id' => $wp_id, 
						'phone' => $script_info['mobile_number'], 
					); 
					$phone = new \PSU\Rave\Phone( $phone_args ); 
					$phone->save('no_confirm'); 
					try {
						if( $rave_user->save() ) {
							$this->util->delete( $args );
						} else {
							$this->util->set_flag( $args, 'savefail' );
						}//end else
					} catch( \Exception $e ) {
						$this->util->set_flag( $args, 'savefail' );
					}//end catch
				} catch( \Exception $e ) { 
					$this->util->set_flag( $args, 'createfail' );
				}//end catch

				if( $this->debug ) {
					echo ( $rave_user ? 'Created: ' : 'Failed to create: ' ) . $wp_id . "\n";
				}//end if
			}// end else

		}//end foreach

		return count( $this->util->select() );
	}//end process

}//end ProcessActiveUsers
