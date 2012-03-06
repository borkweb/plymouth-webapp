<?php

namespace PSU\Rave;

class Phone extends \PSU\Phone {
	public $aliases = array();

	/**
	 * constructor
	 */
	public function __construct( $data = null ) {
		// in the event that 'source' wasn't passed in,
		// prepare a default source user
		$default_user = \PSU::nvl( $_SESSION['wp_id'], 'script' );

		$defaults = array(
			'phone_type' => 'CE',
			'source' => 'USER:' . $default_user,
		);

		$data = \PSU::params( $data, $defaults );

		parent::__construct( $data );

		if( $this->phone ) {
			// remove any formatting on the passed in phone number
			$this->phone = self::unformat( $this->phone );

			// split the number out into parts (for compatibility with \PSU\Phone)
			$data = $this->parse( $this->phone );

			$this->area = $data['area'] ?: '603';
			$this->number = $data['number'];
		}//end if
	}//end constructor
	
	/**
	 * confirm the phone
	 *
	 * @param $pin \b Confirmation code
	 */
	public function confirm( $pin ) {
		if( $rave_user = \PSU\Rave\User::get( $this->wp_id ) ) {

			if( $ok = $rave_user->confirmPhone( $pin ) ) {
				$sql = "
					UPDATE person_phone
					   SET last_confirmed_date = NOW()
					 WHERE wp_id = ?
					   AND phone_type = ?
						 AND end_date is null
				";

				$args = $this->sanitize();

				$args = array(
					$args['wp_id'], 
					$args['phone_type'],
				); 

				return \PSU::db('emergency_notification')->Execute( $sql, $args );
			} // end if
		} // end if

		return false;
	} //end confirm

	/**
	 * expire all active numbers matching the provided sanitized data
	 */
	public static function expire_all_active( $wp_id, $phone_type ) {
		// make sure this insert has the correct order and values
		$args = array(
			$wp_id,
			$phone_type,
		);

		// TODO: sanitize the args

		// add an end date because we have a new number or they are resubmitting an opt out
		$sql = "
			UPDATE person_phone
			SET end_date = NOW()
			WHERE wp_id = ?
				AND phone_type = ?
				AND end_date is null
		";

		return \PSU::db('emergency_notificationt')->Execute($sql, $args ); 
	}//end expire_all_active

	/**
	 * returns a friendly string for the phone's Rave status
	 */
	public function friendly_status() {
		if( $this->end_date ) {
			return 'Phone record ended: '.$this->end_date;
		} elseif( $this->opt_notnow ) {
			return 'Has chosen deferred on: '.$this->opt_notnow;
		} elseif( $this->opt_nocell ) {
			return 'Has opted out on: '.$this->opt_nocell;
		} elseif( ! $this->last_confirmed_date ) {
			return 'Not confirmed, will prompt on next login';
		} else {
			return 'Confirmed: '.$this->last_confirmed_date;
		} // end else
	}//end friendly_status

	/**
	 * returns whether or not the given user has the 
	 * given phone as a confirmed number in Rave
	 */
	public static function is_confirmed( $rave_user, $phone ) {
		if( ! is_object( $rave_user ) ) {
			/**
			 * if $rave_user is not an object, assume it is a
			 * wp_id and instantiate \PSU\Rave\User
			 */
			$rave_user = \PSU\Rave\User::get( $rave_user );	
		}//end if

		// TODO: throw error if object is not a \PSU\Rave\User

		if( $rave_user ) {
			if( is_object( $phone ) ) {
				/**
				 * if $phone is an object, assume \PSU\Phone, 
				 * \PSU\Person\Phone, \PSU\Rave\Phone, etc and build
				 * a string accordingly
				 */
				$phone = $phone->area . $phone->number;
			}//end if

			$phone = self::unformat( $phone );

			// does the phone number in Rave match the passed phone?
			if( $rave_user->mobilePhone1 == $phone ) {
				// yes! return the confirmation status for that phone
				return $rave_user->mobile1Confirmed;	
			} // end if
		} // end if

		return false;
	}//end is_confirmed

	/**
	 * sanitize the object properties so we don't get h4xx0r3d
	 */
	public function sanitize() {
		// fields are ordered the same as in gb_telephone package
		$fields = array(
			'wp_id' => FILTER_SANITIZE_STRING,
			'phone_type' => FILTER_SANITIZE_STRING,
			'source' => FILTER_SANITIZE_STRING,
		);

		$data = array();

		// build the data array for inserts/updates
		foreach( $fields as $field => $filter ) {
			$data[ $field ] = filter_var( $this->$field, $filter ) ?: null;
		}//end foreach

		$data['phone'] = self::unformat( $this->phone );

		return $data;
	}//end sanitize

	/**
	 * save the Rave phone
	 *
	 * @param $option \b Rave save option (false, opted_out, deferred, resend, no_confirm)
	 */
	public function save( $option = false ) {
		if( ! $this->wp_id ) {
			throw new UnexpectedValueException('A user id (wp_id) is required before saving. No user id was provided.');
		}//end if

		$args = $this->sanitize();

		// we need to get the current phone for the user.
		// load the phones
		$phones = new Phones( $this->wp_id );
		$phones->load();

		// grab the most recent, active number whose type matches this phone's type
		$current = $phones->current( $phones->type( $args['phone_type'] ) );

		if($current || $option == 'deferred' || $option == 'opted_out' ) {
			/* it will enter the following if (not the ifesles) when:
			 *   1) they have a current phone that doesn't match the phone they are saving, OR
			 *   2) they are deferring, OR
			 *   3) they are opting out, OR
			 *   4) they have a current phone that matches the phone they are saving AND they 
			 *        are either continuing/not confirming AND the current phone is set to optout or deferred
			 */
			if( 
				   ($current && $current->phone != $args['phone'])
			  || $option == 'deferred' 
				|| $option == 'opted_out' 
				|| ( 
						/**
						 * if the phone being entered matches the current active phone
						 * and the save option is 'continue' AND the current active
						 * phone is set to either nocell or notnow, then we need to
						 * enter a new row in the table and inactivate all the active
						 * numbers.
						 */
						$current
						&& $current->phone == $args['phone'] 
						&& ( $option == 'continue' || $option == 'no_confirm' )
						&& ( $current->opt_nocell || $current->opt_notnow )
					 )
			) {

				\PSU::db('emergency_notificationt')->StartTrans();

				if( $current && ! ( $current->phone == $args['phone'] && $option == 'continue' ) ) {
					$current->unconfirm( $current->phone );
				}//end if

				// if we get here, we're going to be adding a new phone number.  Expire
				//   all of the active ones that match this number's type
				self::expire_all_active( $args['wp_id'], $args['phone_type'] );

				$do_final_commit = true;

			} elseif( $option === 'resend' ) {
				$rave_user = \PSU\Rave\User::get( $this->wp_id );
				return $rave_user->save( true );
			} else {
				// phone has not changed, do nothing
				return true;
			} // end else
		} //end if

		// assume we want to actually commit...we'll cancel 
		// the commit later if there was an error
		$commit = true;

		if( $ok = $this->_insert( $args, $option ) ) {
			// if we get in here, the insert worked swimmingly

			if( ! ( $ok = $this->_rave_save( $args['phone'], $option ) ) ) {
				// if we get in here, Rave had an error
				// explicitly fail the transaction 
				$commit = false;
			} // end rollback

			\PSU::db('emergency_notificationt')->CompleteTrans( $commit );

			if( $do_final_commit ) {
				\PSU::db('emergency_notificationt')->CompleteTrans( $commit );
			}

			return $ok;
		} // end if
		return false;
	}//end save

	/** 
	 * returns the phone's current status
	 */
	public function status() {
		if( $this->opt_nocell ) {
			return 'opt_nocell';
		} elseif( $this->opt_notnow ) {
			// is the "not now" more than a day ago?
			if( strtotime( $this->opt_notnow ) + ( 5 * 86400 ) < time() ) {
				// re-prompt
				return false; 
			} // end if

			return 'opt_notnow';	
		} elseif( ! $this->last_confirmed_date ) {
			return 'not_verified';
		} // end else if 

		// everything is setup and good
		return true; 	
	}//end status

	/** 
	 * function to end text services on a phone
	 */
	public function unconfirm( $phone = null ) {
		$sql = "
			UPDATE person_phone 
			   SET end_date = NOW(),
				     source = ? 
			 WHERE wp_id = ?
				 AND end_date is null
		";

		$args = $this->sanitize();

		$args = array( 
			$args['source'], 
			$args['wp_id'],
		);

		// if we only want to unconfirm a specific phone, specify it here
		if( $phone ) {
			$args[] = $phone;
			$sql .= " AND phone = ?";
		}//end if

		if( $ok = \PSU::db('emergency_notification')->Execute($sql, $args ) ) {
			if( $rave_user = \PSU\Rave\User::get( $this->wp_id ) ) {
				return $rave_user->unconfirmPhone();	
			}//end if
		} // end if

		return $ok;
	} // end unconfirm

	/**
	 * insert a phone number into the MySQL table
	 *
	 * @param $args \b sanitized arguments used for insertion
	 * @param $option \b the type of Rave manipulation that is being done: (false, opt_out, deferred, resend)
	 */
	private function _insert( $args, $option = false ) {
		// start the transaction in case it wasn't in the update
		// ADOdb smart transactions smartly support nesting, so if a StartTrans has already occurred, the following line will be ignored
		\PSU::db('emergency_notificationt')->StartTrans();

		$args = array(
			$args['wp_id'],
			$args['phone_type'],
			$args['source'],
			$args['phone'],
		);

		$sql = "
			INSERT INTO person_phone (
				wp_id,
				phone_type,
				source,
				phone,
				opt_notnow,
				opt_nocell 
			) VALUES ( 
				?, 
				?,
			 	?, 
				?, 
				?, 
				? 
			)
		";
		
		$opt_notnow = null;
		$opt_nocell = null;

		$now = date('Y-m-d H:i:s');

		if( $option === 'deferred' ) {
			$opt_notnow = $now;
		} elseif( $option === 'opted_out' ) {
			$opt_nocell = $now;
		} // end elseif

		$args[] = $opt_notnow;
		$args[] = $opt_nocell;

		return \PSU::db('emergency_notificationt')->Execute( $sql, $args );
	}//end _insert

	/**
	 * save the number in Rave
	 *
	 * @param $phone \b phone number
	 * @param $option \b the type of Rave manipulation that is being done: (false, opt_out, deferred, resend)
	 */
	protected function _rave_save( $phone, $option = false ) {
		$user = \PSU\Rave\User::get( $this->wp_id );

		if( ! $user ) {
			if( ! ( $user = \PSU\Rave\User::create( $this->wp_id ) ) ) {
				throw new UnexpectedValueException('Could not load user information for '.$this->wp_id);
			}//end if
		} // end if

		if( ! $phone || $option == 'deferred' || $option == 'opted_out' ) {
			unset( $user->mobileNumber1 );
			$confirm = false;
		} else {
			$user->rave_set_phone( $phone );
			$confirm = ($option != 'no_confirm');
		}

		return $user->save( $confirm );
	}//end _rave_save

}//end class \PSU\Rave\Phone
