<?php

namespace PSU\Person;

class Email extends \PSU\Email {
	public $aliases = array(
		'email_address' => 'address',
	);

	public function __construct( $data = null ) {
		$data = \PSU::cleanKeys('goremal_', '', $data);

		parent::__construct( $data );
	}//end constructor

	/**
	 * Determine if an active email exists for the given pidm and type.
	 * @param $pidm \b int
	 * @param $type \b Banner EMAL Code: string CA, PE, etc.
	 */
	public static function exists($pidm, $type) {
		$sql = "SELECT 1 FROM goremal WHERE goremal_pidm = :p_pidm AND goremal_emal_code = :p_emal_code AND goremal_status_ind = 'A'";

		$args = array(
			'p_pidm' => filter_var( $this->pidm, FILTER_SANITIZE_NUMBER_INT ),
			'p_emal_code' => filter_var( $this->emal_code, FILTER_SANITIZE_STRING ),
		);

		return (bool) \PSU::db('banner')->Execute($sql, $args);
	}//end exists

	/**
	 * delete a phone using the Banner API
	 */
	public function delete() {
		$sql = "BEGIN gb_email.p_delete(p_pidm => :p_pidm, p_emal_code => :p_type, p_email_address => :p_email_address); END;";

		$stmt = \PSU::db('banner')->PrepareSP($sql);
		\PSU::db('banner')->InParameter($stmt, $this->pidm, 'p_pidm');
		\PSU::db('banner')->InParameter($stmt, $this->emal_code, 'p_type');
		\PSU::db('banner')->InParameter($stmt, $this->email_address, 'p_email_address');
		$return = \PSU::db('banner')->Execute($stmt);

		$this->_ensure_preferred();

		return $return;
	}//end delete

	public function description() {
		static $descriptions = array();

		if( empty( $descriptions ) ) {
			$types = \PSU::db('banner')->CacheGetAll("SELECT gtvemal_code, gtvemal_desc FROM gtvemal");
			foreach($types as $type) {
				$descriptions[$type['gtvemal_code']] = $type['gtvemal_desc'];
			}//end foreach
		}//end if

		return $descriptions[ $this->emal_code ];
	}//end description

	/**
	 * Return a new PSU\Person\Email, fetched by rowid.
	 * @param $rowid \b string the row id
	 */
	public static function get_by_rowid( $rowid ) {
		$sql = "BEGIN :c_cursor := gb_email.f_query_by_rowid(:rowid); END;";

		$cursor = \PSU::db('banner')->ExecuteCursor($sql, 'c_cursor', compact('rowid'));
		foreach($cursor as $row) {
			unset($row['rowid']);
			return new self($row);
		}
	}//end get_by_rowid

	/**
	 * returns whether or not the email is preferred
	 */
	public function preferred() {
		return $this->preferred_ind == 'Y';
	}//end preferred

	/**
	 * saves an email
	 *
	 * @param $inactivate \b if TRUE while creating a new email, any active emails of the same type will be inactivated
	 */
	public function save( $inactivate = false, $force_preferred = true ) {
		\PSU::db('banner')->StartTrans();

		// if the phone has been set as primary, make sure other numbers of
		// the same tele code are not primary
		if( !$this->rowid && $this->preferred_ind == 'Y' ) {
			$this->_unset_other_preferred();
		}//end if

		// if a rowid exists, we're updating, otherwise creating
		$action = $this->rowid ? '_update' : '_insert';
		$return = $this->$action( $inactivate );

		if( $force_preferred ) {
			$this->_ensure_preferred();
		}//end if

		\PSU::db('banner')->CompleteTrans();
		return $return;
	}//end save

	/**
	 * sanitize the object properties so we don't get h4xx0r3d
	 */
	public function sanitize() {
		// fields are ordered the same as in gb_email package
		$fields = array(
			'pidm' => FILTER_SANITIZE_NUMBER_INT,
			'emal_code' => FILTER_SANITIZE_STRING,
			'email_address' => FILTER_SANITIZE_STRING,
			'status_ind' => FILTER_SANITIZE_STRING,
			'preferred_ind' => FILTER_SANITIZE_STRING,
			'user_id' => FILTER_SANITIZE_STRING,
			'comment' => FILTER_SANITIZE_STRING,
			'disp_web_ind' => FILTER_SANITIZE_STRING,
			'data_origin' => FILTER_SANITIZE_STRING,
		);

		$data = array();

		// build the data array for inserts/updates
		foreach( $fields as $field => $filter ) {
			$data[ $field ] = filter_var( $this->$field, $filter ) ?: null;
		}//end foreach

		// attempt to use the session username as the user that updated the email
		//   failover to the user defined in the object
		//   failover to hostname
		//   failover to script
		$data['user_id'] = strtoupper( \PSU::nvl( $_SESSION['username'], $data['user_id'], $_SERVER['REMOTE_HOST'], 'script' ) );

		return $data;
	}//end sanitize

	/**
	 * inactivates an email
	 */
	public function set_inactive() {
		$this->status_ind = 'I';
	}//end set_inactive

	/**
	 * set this email as preferred and unset other emails of this type as preferred
	 */
	public function set_preferred() {
		$this->preferred_ind = 'Y';

		if( $this->rowid ) {
			$this->_unset_other_preferred();

			// we're setting the preferred indicator...the second false prevents 
			// the save method from overwriting this update
			$this->save( false, false );
		}//end if
	}//end set_preferred

	/**
	 * ensures the preferred indicator is set on at least one email owned by this user with the same 
	 * email type
	 */
	private function _ensure_preferred() {
		$emails = new Emails( $this->pidm );
		$emails->load();

		$preferred	= $emails->preferred( $emails->active_by_type( $this->emal_code ) );

		if( $preferred->is_empty() ) {
			$actives	= $emails->active_by_type( $this->emal_code );
			$active = iterator_to_array( $actives );
			$active = array_shift( $active );

			$active->set_preferred();
		}//end if
	}//end _ensure_preferred

	/**
	 * unsets the preferred indicator on other emails owned by this user with the same 
	 * email type
	 */
	private function _unset_other_preferred() {
		$emails = new Emails( $this->pidm );
		$emails->load();

		$active	= $emails->preferred( $emails->active_by_type( $this->emal_code ) );
		foreach( $active as $email ) {
			if( $email->email_address != $this->email_address ) {
				$email->preferred_ind = 'N';

				// we're removing the preferred indicator...the second false prevents 
				// the save method from overwriting this update in the event there is
				// only one email of the given type in the table.  If we are inserting
				// a primary email, we need to make sure there aren't any other
				// primary email addresses or the API insert will bomb.
				$email->save( false, false );
			}//end if
		}//end foreach
	}//end _unset_other_preferred

	/**
	 * executes an insert of the email.  
	 *
	 * @param $inactivate \b if TRUE, any active phones of the same type will be inactivated
	 */
	private function _insert( $inactivate = FALSE ) {
		$data = $this->sanitize();

		// Here's our API call
		$sql = "BEGIN gb_email.p_create(%s); END;";

		// inject the fields in the API call
		$inner = "";
		foreach( $data as $key => $value ) {
			if( $value !== null ) {
				$inner .= 'p_' . $key . ' => :p_' . $key . ', ';
			}//end if
		}//end foreach

		$inner .= 'p_rowid_out => :p_rowid_out ';

		$stmt = \PSU::db('banner')->PrepareSP( sprintf( $sql, $inner ) );

		// bind our variables
		foreach( $data as $key => $value ) {
			if( $value !== null ) {
				// we can't bind on $key alone...InParameter requires the actual array reference
				if( $key == 'pidm' ) {
					// force an int bind
					\PSU::db('banner')->InParameter( $stmt, $data[ $key ], 'p_' . $key, 4000, OCI_B_INT );
				} else {
					\PSU::db('banner')->InParameter( $stmt, $data[ $key ], 'p_' . $key );
				}//end else
			}//end if
		}//end foreach

		\PSU::db('banner')->OutParameter( $stmt, $this->rowid, 'p_rowid_out' );

		return \PSU::db('banner')->Execute($stmt);
	}//end _insert

	/**
	 * executes an update of the email
	 *
	 * @param $inactivate \b if TRUE, sets the email to inactive
	 */
	private function _update( $inactivate = FALSE ) {

		// if this is an inactivating update, inactivate the email
		// note: this can typically be done in a cleaner fashion via
		//        $email->inactivate();
		//        $email->save()
		if( $inactivate ) {
			$this->set_inactive();
		}//end if

		$data = $this->sanitize();

		// begin our UPDATE statment.  We aren't using an API call here.
		$sql = "UPDATE goremal SET ";

		$inner = "";
		$bind = array();
		foreach( $data as $key => $value ) {
			$inner .= 'goremal_' . $key . ' = :p_' . $key . ', ';
			$bind[ 'p_'.$key ] = $value;
		}//end foreach

		$sql .= substr( $inner, 0, -2 );

		$sql .= " WHERE goremal_pidm = :p_pidm AND goremal_emal_code = :p_emal_code AND goremal_email_address = :p_email_address";

		return \PSU::db('banner')->Execute( $sql, $bind );
	}//end _update

}//end class \PSU\Person\Email
