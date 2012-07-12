<?php

namespace PSU\AR\Fees;

class RegistrationFee extends \PSU_DataObject {
	public $aliases = array(
		'detl_code' => 'detail_code',
	);

	/**
	 * constructor
	 *
	 * @param $row array Array of row elements
	 */
	public function __construct( $row = null ) {
		if( $row ) {
			// get rid of table name from field names
			$row = \PSU::cleanKeys('sfrrgfe_', '', $row);
		}//end if

		parent::__construct( $row );
	}//end constructor

	/**
	 * returns the activity date's timestamp
	 */
	public function activity_date_timestamp() {
		return strtotime( $this->activity_date );
	}//end activity_date_timestamp

	/**
	 * returns the from_add date's timestamp
	 */
	public function from_add_date_timestamp() {
		return strtotime( $this->from_add_date );
	}//end from_add_date_timestamp

	/**
	 * returns the to_add date's timestamp
	 */
	public function to_add_date_timestamp() {
		return strtotime( $this->to_add_date );
	}//end to_add_date_timestamp
}//end class
