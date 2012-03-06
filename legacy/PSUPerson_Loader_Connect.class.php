<?php

require_once 'PSUPerson_Loader.class.php';

class PSUPerson_Loader_Connect extends PSUPerson_Loader implements PSUPerson_Loader_Interface
{
	public $data = array();
	public $priority = 20;

	public static $loaders = array(
		'emergency_phone' => 'emergency_phones',
		'emergency_phone_status' => 'emergency_phones',
		'first_name' => 'identifiers',
		'go_states' => 'go',
		'last_name' => 'identifiers',
		'pidm' => 'identifiers',
		'psuname' => 'identifiers',
		'rave_role' => 'rave_status',
		'rave_state' => 'rave_status',
		'wp_email' => 'identifiers',
		'wp_email_alt' => 'identifiers',
		'wpuser_array' => 'wpuser',
	);

	public function __construct( PSUPerson $person ) {
		parent::__construct();
		$this->person = $person;
	}

	public function loader_preflight( $identifier ) {
		if( PSU::is_wpid( $identifier ) ) {
			$this->person->identifier_type = 'wp_id';
			$this->person->wp_id = $this->person->wpid = $identifier;

			if( $this->_get_userdatabylogin( $identifier ) ) {
				return true;
			}
		} elseif( $this->person->wp_id ) {
			if( $this->_get_userdatabylogin( $this->person->wp_id ) ) {
				return true;
			}
		}

		return false;
	}//end loader_preflight

	protected function _get_userdatabylogin( $wp_id ) {
		static $data = null;

		if( null === $data[ $wp_id ] ) {
			$data[ $wp_id ] = \PSU::api('backend')->get('wp/{{wp_id}}', array(
				'wp_id' => $wp_id,
			));
		}//end if

		return $data[ $wp_id ];
	}//end _get_userdata

	public function _load_emergency_phones() {
		$phones = new \PSU\Rave\Phones( $this->person->wpid );
		$phones->load();

		$current = $phones->current( $phones->type('CE') );

		$this->person->emergency_phones = $phones;
		$this->person->emergency_phone = $current;
		$this->person->emergency_phone_status = $current ? $current->friendly_status() : 'No phone provided';
	}//end _load_emergency_phones

	/**
	 * lazy loads the person's go object
	 */
	public function _load_go()
	{
		require_once 'go.class.php';
		$go = new go($this->person->wp_id);
		$this->person->go = $go;
		$this->person->go_states = $go->getStates();
	}//end _load_go

	/**
	 * Load Mercury relationship grants.
	 */
	function _load_myrelationship_grants() {
		$this->person->data['myrelationship_grants'] = array();

		foreach( $this->person->myrelationships->get() as $rel ) {
			if( $rel->initiator->wpid == $this->person->wpid ) {
				$wpid = $rel->target->wpid;
			} else {
				$wpid = $rel->initiator->wpid;
			}//end if

			foreach( (array) $rel->{$wpid}->grants() as $g ) {
				$this->person->data['myrelationship_grants'][$g->permission->code] = $g->permission;
			}//end foreach
		}//end foreach
	}//end _load_myrelationship_grants

	/**
	 * Load Mercury relationship data.
	 */
	function _load_myrelationships() {
		require_once 'MyRelationships.class.php';

		$this->person->myrelationships = new MyRelationships( $this->person );
	}//end _load_myrelationships

	public function _load_rave_status() {
		$rave_user = \Rave\User::get( $this->person->wpid );

		$rave_state = '';
		if( !isset( $rave_user->administrationRole ) ) {
			$rave_state = 'No Emergency Account';
		} else {
			if( isset( $rave_user->mobileNumber1 ) ) {
				if( $rave_user->mobile1Confirmed == 'true' ) {
					$rave_state = 'Registered and confirmed';
				} else {
					$rave_state = 'Unconfirmed number';
				} // end else
			} else {
				$rave_state = 'No number registered';
			} // end else
		} // end else

		$this->person->rave_state = $rave_state;
		$this->person->rave_role = $rave_user->administrationRole;

		$this->person->rave_status = $this->person->rave_state . ' (' . $this->person->rave_role . ')';
	}//end _load_rave_status

	public function _load_support_locked() {
		$this->person->support_locked = sl_service_lock_check( $this->person->pidm );
	}
	
	public function _load_identifiers() {
		$this->person->first_name = $this->person->wpuser->first_name;
		$this->person->last_name = $this->person->wpuser->last_name;
		$this->person->pidm = $this->person->wpuser->pidm;
		$this->person->psuname = $this->person->wpuser->psuname;
		$this->person->wp_email = $this->person->wpuser->user_email;
		$this->person->wp_email_alt = $this->person->wpuser->email_alt;

		// todo -- what's it called?
		// $this->person->wp_phone = $this->person->wpuser->wp_phone;
	}

	public function _load_wpuser() {
		$this->person->data['wpuser'] = $this->_get_userdatabylogin( $this->person->wp_id );
		//$this->person->data['wpuser'] = get_userdatabylogin( $this->person->wp_id );

		$this->person->data['wpuser_array'] = (array)$this->person->data['wpuser'];
		ksort($this->person->data['wpuser_array']);
	}

	/**
	 * Function used to add a locked attribute to a user's wp meta information
	 */
	public function lock_wp_account() {
		update_user_meta( $this->person->wpuser_array['ID'], 'psuname_locked', TRUE);
	}//end lock_wp_account

	/**
	 * Function to remove the locked attribute from a user's wp meta information
	 */
	public function unlock_wp_account() {
		delete_user_meta( $this->person->wpuser_array['ID'], 'psuname_locked' );
	}//end unlock_wp_account

}//end PSUPerson_Loader_Connect
