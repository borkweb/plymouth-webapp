<?php

namespace PSU\Rave;

/**
 * A wrapper class for \Rave\User that layers on PSU-specific logic
 *
 * if a method doesn't exist in this class, __call will attempt to find it in
 * \Rave\User.  If it exists there, it'll call it.
 */
class User {
	public $aliases = array();
	public $user = null;

	/**
	 * constructor
	 *
	 * @param $identifier \b Either a WP_ID or \Rave\User object
	 */
	public function __construct( $identifier ) {
		if( is_object( $identifier ) && $identifier instanceof \Rave\User ) {
			$this->user = $identifier;
		} elseif( $identifier ) {
			$this->user = \Rave\User::get( $identifier );

			if( ! $this->user ) {
				throw new \UnexpectedValueException('The user identified by '.$identifier.' has not been provisioned into Rave.  Please call \PSU\Rave\User::create( $wp_id )');
			}//end if
		} else {
			throw new \UnexpectedValueException('The passed identifier must be either a WP_ID or a \Rave\User object');
		}//end else
	}//end constructor

	/**
	 * creates a new \Rave\User
	 *
	 * @param $identifier \b identifier for PSUPerson
	 */
	public static function create( $identifier ) {
		$person = \PSUPerson::get( $identifier );

		$rave_user = new \Rave\User(array(                                                                                                                                                                                                                                        
			'sisId' => $person->wp_id,                                                                                                                                                                                                                                                    
			'firstName' => $person->first_name,                                                                                                                                                                                                                                        
			'lastName' => $person->last_name,                                                                                                                                                                                                                                          
			'email' => $person->wp_email ?: $person->email['CA'][0],                                                                                                                                                                                                                                                 
			'ssoId' => $person->login_name,                                                                                                                                                                                                                                            
		));                                                                                                                                                                                                                                                                       

		if( $ok = $rave_user->save() ) {
			return new self( $rave_user );			
		}//end if

		return $ok;
	}//end create

	/**
	 * deletes a \Rave\User and inactivates the phone
	 */
	public function delete() {
		$person = \PSUPerson::get( $this->user->sisId );

		/**
		 * Going through the delete process for the user will be determined
		 * by whether or not we can successfully unconfirm the phone
		 */
		if( $person->emergency_phone && ! $person->emergency_phone->unconfirm() ) {
				throw new Exception( 'Unable to unconfirm the user\'s Rave phone' );
		}//end if

		return $this->user->delete();
	}//end delete

	/**
	 * gets a new \Rave\User
	 *
	 * @param $identifier \b identifier for PSUPerson
	 */
	public static function get( $identifier ) {
		$user = \Rave\User::get( $identifier );

		if( ! $user ) {
			return null;
		}//end if

		return new self( $user );
	}//end create

	/**
	 * returns the Rave User's phones
	 */
	public function phones() {
		static $phones = array();

		if( ! $phones ) {
			$phones = new \PSU\Rave\Phones( $this->user->sisId );
		}//end if

		return $phones;
	}//end phones

	/**
	 * directly sets the \Rave\User phone
	 *
	 * @param $phone \b Phone Number
	 * @param $which \b Which Rave number to set (1 - 3)
	 */
	public function rave_set_phone( $phone, $which = 1 ) {
		if( ! is_numeric( $which ) || ! in_array( $which, array( 1, 2, 3 ) ) ) {
			throw new \UnexpectedValueException('The second parameter MUST be a number (1 - 3)');
		}//end if

		$which = 'mobileNumber' . $which;
		$this->user->$which = $phone;
	}//end rave_set_phone

	/**
	 * BEGIN Magic
	 */

	/**
	 * offloads method calls to the \Rave\User object
	 */
	public function __call( $method, $args ) {
		if( method_exists( $this->user, $method ) ) {
			return call_user_func_array( array( $this->user, $method ), $args );
		}//end if

		throw new Exception('Cannot find method '.$name.' in \PSU\Rave\User or \Rave\User');
	}//end __call

	public function __get( $name ) {
		if( isset( $this->user->$name ) ) {
			return $this->user->$name;
		}//end if

		return null;
	}//end __get

	public function __isset( $name ) {
		return isset( $this->user->$name );
	}//end __isset

	public function __set( $name, $value ) {
		$this->user->$name = $value;
	}//end __set

	public function __unset( $name ) {
		if( isset( $this->user->$name ) ) {
			unset( $this->user->$name );
		}//end if
	}//end __unset
}//end class \PSU\Rave\User
