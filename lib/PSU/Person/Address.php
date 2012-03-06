<?php

namespace PSU\Person;

class Address extends \PSU\Address {
	public $aliases = array(
		'street_line1' => 'street1',
		'street_line2' => 'street2',
		'street_line3' => 'street3',
		'street_line4' => 'street4',
		'stat_code' => 'state_abbr',
		'zip' => 'postal_code',
	);

	public function __construct( $data = null ) {
		$data = \PSU::cleanKeys('spraddr_', '', $data);

		parent::__construct( $data );
	}//end constructor
	
	/**
	 * Determine if an active address exists for the given pidm and type.
	 * @param $pidm \b int
	 * @param $type \b string OF, CA, etc.
	 */
	public static function exists($pidm, $type) {
		$sql = "DECLARE v_exists VARCHAR2(1); BEGIN :v_exists := gb_address.f_exists_active(:p_pidm, :p_type, sysdate, sysdate); END;";

		$stmt = \PSU::db('banner')->PrepareSP($sql);
		\PSU::db('banner')->OutParameter($stmt, $exists, 'v_exists');
		\PSU::db('banner')->InParameter($stmt, $pidm, 'p_pidm');
		\PSU::db('banner')->InParameter($stmt, $type, 'p_type');
		\PSU::db('banner')->Execute($stmt);

		return $exists == 'Y';
	}//end exists

	/**
	 * delete an address using the Banner API
	 */
	public function delete() {
		$sql = "BEGIN gb_address.p_delete(p_pidm => :p_pidm, p_atyp_code => :p_type, p_seqno => :p_seqno, p_rowid => :p_rowid); END;";

		$stmt = \PSU::db('banner')->PrepareSP($sql);
		\PSU::db('banner')->InParameter($stmt, $this->pidm, 'p_pidm');
		\PSU::db('banner')->InParameter($stmt, $this->atyp_code, 'p_type');
		\PSU::db('banner')->InParameter($stmt, $this->rowid, 'p_rowid');
		return \PSU::db('banner')->Execute($stmt);
	}//end delete

	/**
	 * get address descriptions
	 */
	public function description() {
		// if we load these once, we don't want to do it all the time. STATIC-IFICATION!
		static $descriptions = array();

		// if descriptions haven't been loaded, let's get them
		if( empty( $descriptions ) ) {
			// ...but, let's attempt to get them from cache
			$types = \PSU::db('banner')->CacheGetAll("SELECT stvatyp_code, stvatyp_desc FROM stvatyp");

			// set these bastards up
			foreach($types as $type) {
				$descriptions[$type['stvatyp_code']] = $type['stvatyp_desc'];
			}//end foreach
		}//end if

		return $descriptions[ $this->atyp_code ];
	}//end description

	/**
	 * return the from_date as a timestamp
	 */
	public function from_date_timestamp() {
		return $this->from_date ? strtotime( $this->from_date ) : null;
	}//end from_date_timestamp

	/**
	 * Return a new PSUAddress, fetched by rowid.
	 * @param $rowid \b string the row id
	 */
	public static function get_by_rowid( $rowid ) {
		$sql = "BEGIN :c_cursor := gb_address.f_query_by_rowid(:rowid); END;";

		$cursor = \PSU::db('banner')->ExecuteCursor($sql, 'c_cursor', compact('rowid'));
		foreach($cursor as $row) {
			unset($row['rowid']);
			return new self($row);
		}
	}//end get_by_rowid

	/**
	 * inactivates an address
	 *
	 * @param $to_date \b effective date of inactivation
	 */
	public function inactivate( $to_date = null ) {
		$this->status_ind = 'I';
		$this->to_date = $to_date ?: date('Y-m-d H:i:s');
	}//end inactivate

	/**
	 * Returns true if a user can edit a specific address type, otherwise false.
	 * @param $pidm
	 * @param $atyp_code
	 */
	public static function is_editable( $pidm, $atyp_code ) {
		return self::_goradrl_priv_check( $pidm, $atyp_code, 'U' );
	}//end is_editable

	/**
	 * saves an address
	 *
	 * @param $inactivate \b if TRUE while creating a new address, any active addresses of the same type will be inactivated
	 */
	public function save( $inactivate = false ) {
		\PSU::db('banner')->StartTrans();

		// if a rowid exists, we're updating, otherwise creating
		$action = $this->rowid ? '_update' : '_insert';
		$return = $this->$action( $inactivate );

		\PSU::db('banner')->CompleteTrans();
		return $return;
	}//end save

	/**
	 * sanitize the object properties so we don't get h4xx0r3d
	 */
	public function sanitize() {
		// fields are ordered the same as in gb_address package
		$fields = array(
			'pidm' => FILTER_SANITIZE_NUMBER_INT,
			'atyp_code' => FILTER_SANITIZE_STRING,
			'seqno' => FILTER_SANITIZE_NUMBER_INT,
			'from_date' => FILTER_SANITIZE_STRING,
			'to_date' => FILTER_SANITIZE_STRING,
			'street_line1' => FILTER_SANITIZE_STRING,
			'street_line2' => FILTER_SANITIZE_STRING,
			'street_line3' => FILTER_SANITIZE_STRING,
			'city' => FILTER_SANITIZE_STRING,
			'stat_code' => FILTER_SANITIZE_STRING,
			'zip' => FILTER_SANITIZE_STRING,
			'cnty_code' => FILTER_SANITIZE_STRING,
			'natn_code' => FILTER_SANITIZE_STRING,
			'status_ind' => FILTER_SANITIZE_STRING,
			'user' => FILTER_SANITIZE_STRING,
			'asrc_code' => FILTER_SANITIZE_STRING,
			'delivery_point' => FILTER_SANITIZE_NUMBER_INT,
			'correction_digit' => FILTER_SANITIZE_NUMBER_INT,
			'carrier_route' => FILTER_SANITIZE_STRING,
			'gst_tax_id' => FILTER_SANITIZE_STRING,
			'reviewed_ind' => FILTER_SANITIZE_STRING,
			'reviewed_user' => FILTER_SANITIZE_STRING,
			'data_origin' => FILTER_SANITIZE_STRING,
			'ctry_code_phone' => FILTER_SANITIZE_STRING,
			'house_number' => FILTER_SANITIZE_STRING,
			'street_line4' => FILTER_SANITIZE_STRING,
		);

		$data = array();

		// build the data array for inserts/updates
		foreach( $fields as $field => $filter ) {
			if( $field == 'to_date' || $field == 'from_date' ) {
				$function = $field . '_timestamp';
				$data[ $field ] = $this->$function() ? \PSU::db('banner')->BindDate( $this->$function() ) : null;
			} else {
				$data[ $field ] = filter_var( $this->$field, $filter ) ?: null;
			}//end else
		}//end foreach

		// attempt to use the session username as the user that updated the address
		//   failover to the user defined in the object
		//   failover to hostname
		//   failover to script
		$data['user'] = strtoupper( \PSU::nvl( $_SESSION['username'], $data['user'], $_SERVER['REMOTE_HOST'], 'script' ) );

		return $data;
	}//end sanitize

	/**
	 * return the to_date as a timestamp
	 */
	public function to_date_timestamp() {
		return $this->to_date ? strtotime( $this->to_date ) : null;
	}//end to_date_timestamp

	/**
	 * Backend function to check a person's privilege indicator for an address type.
	 * @param $pidm
	 * @param $atyp_code
	 * @param $priv_ind
	 */
	private static function _goradrl_priv_check( $pidm, $atyp_code, $priv_ind ) {
		$sql = "
			SELECT 1
			FROM gorirol LEFT JOIN goradrl ON gorirol_role = goradrl_role
			WHERE gorirol_pidm = :pidm AND goradrl_atyp_code = UPPER(:atyp_code) AND goradrl_priv_ind = :priv_ind
		";

		return (bool) \PSU::db('banner')->GetOne($sql, compact('pidm', 'atyp_code', 'priv_ind'));
	}//end goradrl_priv_check

	/**
	 * inactivates other addresses owned by this user with the same 
	 * address type
	 */
	private function _inactivate_other_active() {
		$addresses = new Addresses( $this->pidm );
		$addresses->load();

		$active	= $addresses->active_by_type( $this->atyp_code );
		foreach( $active as $address ) {
			$address->inactivate();
			$address->save();
		}//end foreach
	}//end _inactivate_other_active

	/**
	 * executes an insert of the address.  If there already exists an active address
	 * in the address' date range, it will inactivate that record
	 *
	 * @param $inactivate \b if TRUE, any active addresses of the same type will be inactivated
	 */
	private function _insert( $inactivate = FALSE ) {
		$data = $this->sanitize();

		// if we are allowed to inactivate active addresses of the same type
		// and they exist...inactivate them
		if( $inactivate && self::exists( $this->pidm, $this->atyp_code ) ) {
			$this->_inactivate_other_active();
		}//end if

		// Here's our API call
		$sql = "BEGIN gb_address.p_create(%s); END;";

		// set a from date (overiding any set as a property)
		$data['from_date'] = date('Y-m-d H:i:s');

		// toss out the values we can't deal with
		unset( $data['seqno'], $this->seqno );

		// inject the fields in the API call
		$inner = "";
		foreach( $data as $key => $value ) {
			if( $value !== null ) {
				$inner .= 'p_' . $key . ' => :p_' . $key . ', ';
			}//end if
		}//end foreach

		$inner .= 'p_seqno_inout => :p_seqno_inout, ';
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

		\PSU::db('banner')->OutParameter( $stmt, $this->seqno, 'p_seqno_inout' );
		\PSU::db('banner')->OutParameter( $stmt, $this->rowid, 'p_rowid_out' );

		return \PSU::db('banner')->Execute($stmt);
	}//end _insert

	/**
	 * executes an update of the address
	 *
	 * @param $inactivate \b if TRUE, sets the address to inactive
	 */
	private function _update( $inactivate = FALSE ) {

		// if this is an inactivating update, inactivate the address
		// note: this can typically be done in a cleaner fashion via
		//        $address->inactivate();
		//        $address->save()
		if( $inactivate ) {
			$this->inactivate();
		}//end if

		$data = $this->sanitize();

		// begin our UPDATE statment.  We aren't using an API call here.
		$sql = "UPDATE spraddr SET ";

		$inner = "";
		$bind = array();
		foreach( $data as $key => $value ) {
			$inner .= 'spraddr_' . $key . ' = :p_' . $key . ', ';
			$bind[ 'p_'.$key ] = $value;
		}//end foreach

		$sql .= trim( $inner, ', ' );

		$sql .= " WHERE spraddr_pidm = :p_pidm AND spraddr_atyp_code = :p_atyp_code AND spraddr_seqno = :p_seqno";

		return \PSU::db('banner')->Execute( $sql, $bind );
	}//end _update
}//end class \PSU\Person\Address
