<?php
namespace PSU\AR;

class DirectDeposit extends \PSU_Banner_DataObject {
	public $aliases = array(
		'bank_acct_num' => 'bank_account_number',
		'bank_rout_num' => 'bank_routing_number',
		'acct_type' => 'account_type',
	);

	/**
	 * constructor
	 *
	 * @param $row array Array of row elements
	 */
	public function __construct( $row = null ) {
		if( $row ) {
			// get rid of table name from field names
			$row = \PSU::cleanKeys('gxrdird_', '', $row);
		}//end if

		parent::__construct( $row );

		if( !isset( $this->activity_date ) ) {
			$this->activity_date = date('Y-m-d H:i:s');
		}//end if
	}//end constructor

	/**
	 * returns the activity date's timestamp
	 */
	public function activity_date_timestamp() {
		return strtotime( $this->activity_date );
	}//end activity_date_timestamp

	/**
	 * deletes the gxrdird record
	 *
	 * minimum required fields are:
	 * 		pidm
	 */
	public function delete() {
		$args = $this->_prep_args();

		// limit the args down to what is needed for the
		// delete statement
		$args = array(
			'pidm' => $args['pidm'],
		);

		$sql = "
			DELETE FROM gxrdird
		   WHERE gxrdird_pidm = :pidm
		";

		return \PSU::db('banner')->Execute( $sql, $args );
	}//end delete

	/**
	 * load by pidm
	 */
	public static function get_by_pidm( $pidm, $db = 'banner' ) {
		$sql = "SELECT * FROM gxrdird WHERE gxrdird_pidm = :pidm";

		if( $data = \PSU::db( $db )->GetRow( $sql, array('pidm' => $pidm) ) ) {
			return new self( $data );
		}//end if

		return null;
	}//end get_by_pidm

	/**
	 * saves the DirectDeposit record
	 */
	public function save( $method = 'merge' ) {
		$this->validate( 'gxrdird' );

		$args = $this->_prep_args();

		$fields = $this->_prep_fields( 'gxrdird', $args );

		$sql_method = '_' . $method . '_sql';
		$sql = $this->$sql_method( 'gxrdird', $fields );

		return \PSU::db('banner')->Execute( $sql, $args );
	}//end save

	/**
	 * validates fields
	 */
	public function validate() {
		$table = new \PSU_Oracle_Table( 'gxrdird' );
		$table->validate( get_object_vars( $this ), 'gxrdird_' );

		$alpha_numeric_filter = array(
			'options' => array(
				'regexp' => '/^[a-zA-Z0-9]+$/',
			),
		);

		$extended_alpha_numeric_filter = array(
			'options' => array(
				'regexp' => '/^[a-zA-Z0-9.-_]+$/',
			),
		);

		$alpha_numeric = array(
			'status',
			'doc_type',
			'ap_ind',
			'hr_ind',
			'ach_iat_ind',
			'acct_type',
			'atyp_code',
			'scod_code_iso',
			'acht_code',
			'atyp_code_iat',
		);

		$extended_alpha_numeric = array(
			'user_id',
			'bank_acct_num',
			'bank_rout_num',
		);

		// enforce alpha numeric
		foreach( $alpha_numeric as $field ) {
			if( isset( $this->$field ) && filter_var( $this->$field, FILTER_VALIDATE_REGEXP, $alpha_numeric_filter ) === false ) {
				throw new \Exception('Direct Deposit '.$field.' field must be alpha numeric');
			}//end if
		}//end foreach

		// enforce alpha numeric PLUS .-_
		foreach( $extended_alpha_numeric as $field ) {
			if( isset( $this->$field ) && filter_var( $this->$field, FILTER_VALIDATE_REGEXP, $extended_alpha_numeric_filter ) === false ) {
				throw new \Exception('Direct Deposit '.$field.' field must be a valid username');
			}//end if
		}//end foreach

		return true;
	}//end validate

	/**
	 * merge record SQL
	 */
	protected function _merge_sql( $table, $fields ) {
		$on = array(
			'pidm',
		);

		return parent::_merge_sql( $table, $fields, $on );
	}//end _merge_sql

	/**
	 * prepares arguments for DML
	 */
	protected function _prep_args() {
		// this is the data prepared for binding
		$args = array(
			'pidm' => $this->pidm,
			'status' => strtoupper($this->status),
			'doc_type' => strtoupper($this->doc_type),
			'priority' => $this->priority,
			'ap_ind' => strtoupper($this->ap_ind),
			'hr_ind' => strtoupper($this->hr_ind),
			'user_id' => $this->user_id,
			'bank_acct_num' => $this->bank_acct_num,
			'bank_rout_num' => $this->bank_rout_num,
			'amount' => $this->amount,
			'percent' => $this->percent,
			'acct_type' => strtoupper($this->acct_type),
			'atyp_code' => strtoupper($this->atyp_code),
			'addr_seqno' => $this->addr_seqno,
			'ach_iat_ind' => strtoupper($this->ach_iat_ind),
			'scod_code_iso' => strtoupper($this->scod_code_iso),
			'acht_code' => strtoupper($this->acht_code),
			'atyp_code_iat' => strtoupper($this->atyp_code_iat),
			'addr_seqno_iat' => $this->addr_seqno_iat,
		);

		return $args;
	}//end _prep_args
}//end class
