<?php

class PSU_AR_Deposit extends PSU_DataObject {
	public $aliases = array(
		'acct_feed_ind' => 'account_feed_indicator',
		'auto_release_ind' => 'auto_release_indicator',
		'tran_number' => 'transaction_number',
		'desc' => 'description',
		'cshr_end_date' => 'cashier_end_date',
		'trans_date' => 'transaction_date',
	);

	/**
	 * constructor
	 *
	 * @param $row array Array of row elements
	 */
	public function __construct( $row = null ) {
		if( $row ) {
			// get rid of table name from field names
			$row = PSU::cleanKeys('tbrdepo_', '', $row);
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
	 * returns the cashier_end date's timestamp
	 */
	public function cashier_end_date_timestamp() {
		return strtotime( $this->cashier_end_date );
	}//end cashier_end_date_timestamp

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

	/**
	 * returns the feed date's timestamp
	 */
	public function feed_date_timestamp() {
		return strtotime( $this->feed_date );
	}//end feed_date_timestamp

	/**
	 * returns the release date's timestamp
	 */
	public function release_date_timestamp() {
		return strtotime( $this->release_date );
	}//end release_date_timestamp

	/**
	 * returns the transaction date's timestamp
	 */
	public function transaction_date_timestamp() {
		return strtotime( $this->transaction_date );
	}//end transaction_date_timestamp
}//end class PSU_AR_Deposit
