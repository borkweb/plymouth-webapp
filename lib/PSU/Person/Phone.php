<?php

namespace PSU\Person;

class Phone extends \PSU\Phone {
	public $aliases = array(
		'phone_area' => 'area',
		'phone_number' => 'number',
		'phone_ext' => 'extension',
	);

	public function __construct( $data = null ) {
		$data = \PSU::cleanKeys('sprtele_', '', $data);

		parent::__construct( $data );
	}//end constructor
	
	/**
	 * Determine if an active phone exists for the given pidm and type.
	 * @param $pidm \b int
	 * @param $type \b Banner TELE Code: string OF, MA, etc.
	 */
	public static function exists($pidm, $type) {
		$sql = "SELECT 1 FROM sprtele WHERE sprtele_pidm = :p_pidm AND sprtele_tele_code = :p_tele_code AND sprtele_status_ind IS NULL";

		$args = array(
			'p_pidm' => filter_var( $this->pidm, FILTER_SANITIZE_NUMBER_INT ),
			'p_tele_code' => filter_var( $this->tele_code, FILTER_SANITIZE_STRING ),
		);

		return (bool) \PSU::db('banner')->Execute($sql, $args);
	}//end exists

	/**
	 * delete a phone using the Banner API
	 */
	public function delete() {
		$sql = "BEGIN gb_telephone.p_delete(p_pidm => :p_pidm, p_tele_code => :p_type, p_seqno => :p_seqno); END;";

		$stmt = \PSU::db('banner')->PrepareSP($sql);
		\PSU::db('banner')->InParameter($stmt, $this->pidm, 'p_pidm');
		\PSU::db('banner')->InParameter($stmt, $this->tele_code, 'p_type');
		\PSU::db('banner')->InParameter($stmt, $this->seqno, 'p_seqno');
		return \PSU::db('banner')->Execute($stmt);
	}//end delete

	public function delete_duplicates() {
		foreach( $this->duplicates() as $phone ) {
			$phone->delete();
		}//end foreach
	}//end delete_duplicates

	/**
	 * get phone descriptions
	 */
	public function description() {
		// if we load these once, we don't want to do it all the time. STATIC-IFICATION!
		static $descriptions = array();

		// if descriptions haven't been loaded, let's get them
		if( empty( $descriptions ) ) {
			// ...but, let's attempt to get them from cache
			$types = \PSU::db('banner')->CacheGetAll("SELECT stvtele_code, stvtele_desc FROM stvtele");

			// set these bastards up
			foreach($types as $type) {
				$descriptions[$type['stvtele_code']] = $type['stvtele_desc'];
			}//end foreach
		}//end if

		return $descriptions[ $this->tele_code ];
	}//end description

	/**
	 * find duplicates
	 */
	public function duplicates() {
		$phones = new Phones( $this->pidm );
		$phones->load();

		return $phones->duplicates( $this );
	}//end duplicates

	/**
	 * Return a new PSU\Person\Phone, fetched by rowid.
	 * @param $rowid \b string the row id
	 */
	public static function get_by_rowid( $rowid ) {
		$sql = "BEGIN :c_cursor := gb_telephone.f_query_by_rowid(:rowid); END;";

		$cursor = \PSU::db('banner')->ExecuteCursor($sql, 'c_cursor', compact('rowid'));
		foreach($cursor as $row) {
			unset($row['rowid']);
			return new self($row);
		}
	}//end get_by_rowid

	/**
	 * Returns true if the supplied number matches this phone number, with or
	 * without area code.
	 */
	public function match( $number ) {
		$result = ($number === $this->area . $this->number) || ($number === $this->number);
		return $result;
	}//end match

	/**
	 * returns whether or not the phone is primary
	 */
	public function primary() {
		return $this->primary_ind == 'Y';
	}//end primary

	/**
	 * saves a phone
	 *
	 * @param $inactivate \b if TRUE while creating a new phone, any active phones of the same type will be inactivated
	 */
	public function save( $inactivate = false ) {
		\PSU::db('banner')->StartTrans();

		// if a rowid exists, we're updating, otherwise creating
		$action = $this->rowid ? '_update' : '_insert';
		$return = $this->$action( $inactivate );

		// delete duplicates
		$this->delete_duplicates();

		// if the phone has been set as primary, make sure other numbers of
		// the same tele code are not primary
		if( $this->primary_ind == 'Y' ) {
			$this->_unset_other_primary();
		}//end if

		\PSU::db('banner')->CompleteTrans();
		return $return;
	}//end save

	/**
	 * sanitize the object properties so we don't get h4xx0r3d
	 */
	public function sanitize() {
		// fields are ordered the same as in gb_telephone package
		$fields = array(
			'pidm' => FILTER_SANITIZE_NUMBER_INT,
			'seqno' => FILTER_SANITIZE_NUMBER_INT,
			'tele_code' => FILTER_SANITIZE_STRING,
			'phone_area' => FILTER_SANITIZE_STRING,
			'phone_number' => FILTER_SANITIZE_STRING,
			'phone_ext' => FILTER_SANITIZE_STRING,
			'status_ind' => FILTER_SANITIZE_STRING,
			'atyp_code' => FILTER_SANITIZE_STRING,
			'addr_seqno' => FILTER_SANITIZE_NUMBER_INT,
			'primary_ind' => FILTER_SANITIZE_STRING,
			'unlist_ind' => FILTER_SANITIZE_STRING,
			'comment' => FILTER_SANITIZE_STRING,
			'intl_access' => FILTER_SANITIZE_STRING,
			'data_origin' => FILTER_SANITIZE_STRING,
			'user_id' => FILTER_SANITIZE_STRING,
			'ctry_code_phone' => FILTER_SANITIZE_STRING,
		);

		$data = array();

		// build the data array for inserts/updates
		foreach( $fields as $field => $filter ) {
			$data[ $field ] = filter_var( $this->$field, $filter ) ?: null;
		}//end foreach

		// attempt to use the session username as the user that updated the phone
		//   failover to the user defined in the object
		//   failover to hostname
		//   failover to script
		$data['user_id'] = strtoupper( \PSU::nvl( $_SESSION['username'], $data['user_id'], $_SERVER['REMOTE_HOST'], 'script' ) );

		return $data;
	}//end sanitize

	/**
	 * inactivates a phone
	 */
	public function set_inactive() {
		$this->status_ind = 'I';
	}//end set_inactive

	/**
	 * set this phone as primary and unset other phones of this type as primary
	 */
	public function set_primary() {
		$this->primary_ind = 'Y';

		if( $this->rowid ) {
			$this->_unset_other_primary();

			$this->save();
		}//end if
	}//end set_primary

	/**
	 * unsets the primary indicator on other phones owned by this user with the same 
	 * phone type
	 */
	private function _unset_other_primary() {
		$phones = new Phones( $this->pidm );
		$phones->load();

		$active	= $phones->primary( $phones->active_by_type( $this->tele_code ) );
		foreach( $active as $phone ) {
			if( $phone->seqno != $this->seqno ) {
				$phone->primary_ind = null;
				$phone->save();
			}//end if
		}//end foreach
	}//end _unset_other_primary

	/**
	 * inactivates other phonees owned by this user with the same 
	 * phone type
	 */
	private function _inactivate_all_actives() {
		$phones = new Phones( $this->pidm );
		$phones->load();

		$active	= $phones->active_by_type( $this->tele_code );
		foreach( $active as $phone ) {
			$phone->set_inactive();
			$phone->save();
		}//end foreach
	}//end _inactivate_all_actives

	/**
	 * executes an insert of the phone.  If there already exists an active phone
	 * in the phones' date range, it will inactivate that record
	 *
	 * @param $inactivate \b if TRUE, any active phones of the same type will be inactivated
	 */
	private function _insert( $inactivate = FALSE ) {
		$data = $this->sanitize();

		// if we are allowed to inactivate active phones of the same type
		// and they exist...inactivate them
		if( $inactivate && self::exists( $this->pidm, $this->tele_code ) ) {
			$this->_inactivate_all_actives();
		}//end if

		// Here's our API call
		$sql = "BEGIN gb_telephone.p_create(%s); END;";

		// toss out the values we can't deal with
		unset( $data['seqno'], $this->seqno );

		// inject the fields in the API call
		$inner = "";
		foreach( $data as $key => $value ) {
			if( $value !== null ) {
				$inner .= 'p_' . $key . ' => :p_' . $key . ', ';
			}//end if
		}//end foreach

		$inner .= 'p_seqno_out => :p_seqno_out, ';
		$inner .= 'p_rowid_out => :p_rowid_out ';

		$stmt = \PSU::db('banner')->PrepareSP( sprintf( $sql, $inner ) );

		// bind our variables
		foreach( $data as $key => $value ) {
			if( $value !== null ) {
				// we can't bind on $key alone...InParameter requires the actual array reference
				if( $key == 'seqno' || $key == 'pidm' ) {
					// force an int bind
					\PSU::db('banner')->InParameter( $stmt, $data[ $key ], 'p_' . $key, 4000, OCI_B_INT );
				} else {
					\PSU::db('banner')->InParameter( $stmt, $data[ $key ], 'p_' . $key );
				}//end else
			}//end if
		}//end foreach

		\PSU::db('banner')->OutParameter( $stmt, $this->seqno, 'p_seqno_out' );
		\PSU::db('banner')->OutParameter( $stmt, $this->rowid, 'p_rowid_out' );

		return \PSU::db('banner')->Execute($stmt);
	}//end _insert

	/**
	 * executes an update of the phone
	 *
	 * @param $inactivate \b if TRUE, sets the phone to inactive
	 */
	private function _update( $inactivate = FALSE ) {

		// if this is an inactivating update, inactivate the phone
		// note: this can typically be done in a cleaner fashion via
		//        $phone->set_inactive();
		//        $phone->save()
		if( $inactivate ) {
			$this->set_inactive();
		}//end if

		$data = $this->sanitize();

		// begin our UPDATE statment.  We aren't using an API call here.
		$sql = "UPDATE sprtele SET ";

		$inner = "";
		$bind = array();
		foreach( $data as $key => $value ) {
			$inner .= 'sprtele_' . $key . ' = :p_' . $key . ', ';
			$bind[ 'p_'.$key ] = $value;
		}//end foreach

		$sql .= substr( $inner, 0, -2 );

		$sql .= " WHERE sprtele_pidm = :p_pidm AND sprtele_tele_code = :p_tele_code AND sprtele_seqno = :p_seqno";

		return \PSU::db('banner')->Execute( $sql, $bind );
	}//end _update
}//end class \PSU\Person\Phone
