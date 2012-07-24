<?php
namespace PSU\AR\Memo;

class Term extends \PSU_DataObject {
	public $aliases = array(
		'tran_number' => 'transaction_number',
		'billing_ind' => 'billing_indicator',
		'desc' => 'description',
		'srce_code' => 'source_code',
		'crossref_pidm' => 'cross_reference_pidm',
		'crossref_number' => 'cross_reference_number',
		'crossref_detail_code' => 'cross_reference_detail_code',
		'crossref_srce_code' => 'cross_reference_source_code',
	);

	/**
	 * constructor
	 *
	 * @param $row array Array of row elements
	 */
	public function __construct( $row = null ) {
		if( $row ) {
			// get rid of table name from field names
			$row = \PSU::cleanKeys('tbrmemo_', '', $row);
		}//end if

		parent::__construct( $row );
	}//end constructor

	/**
	 * returns the effective date's timestamp
	 */
	public function effective_date_timestamp() {
		return strtotime( $this->effective_date );
	}//end effective_date_timestamp

	/**
	 * returns the entry date's timestamp
	 */
	public function entry_date_timestamp() {
		return strtotime( $this->entry_date );
	}//end entry_date_timestamp

	/**
	 * returns the expiration date's timestamp
	 */
	public function expiration_date_timestamp() {
		return strtotime( $this->expiration_date );
	}//end expiration_date_timestamp
}//end class
