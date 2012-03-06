<?php

namespace PSU\AR\Transaction;

use PSU;
use PSU\AR\Transaction;

class Receivable extends Transaction {
	public $type = 'Receivable';

	/**
	 * ye olde magical constructor
	 *
	 * @param $person Person object
	 * @param $amount amount to split and apply
	 * @param $multiplier 
	 */
	public function __construct( $person, $amount, $multiplier = 1 ) {
		$this->multiplier = $multiplier;

		parent::__construct( $person, $amount );
	}//end constructor

	/**
	 * create an entry object for the transaction's entries
	 */
	protected function generate_entry( $payment ) {
		$payment['receipt_number'] = \PSU_AR::generateReceiptNumber();
		$payment['balance'] = ($payment['amount'] * -1) * $this->multiplier;

		$entry = new \PSU_AR_Receivable( $payment );

		return $entry;
	}//end generate_entry

	/**
	 * generates the next transaction number for the given user
	 */
	public static function next_tran_number( $pidm ) {
		$sql = "SELECT NVL(max(tbraccd_tran_number)+1,1) FROM tbraccd WHERE tbraccd_pidm = :pidm";
		return PSU::db('banner')->GetOne( $sql, array('pidm' => $pidm) );
	}//end next_tran_number

	/**
	 * Set memo-specific entry values
	 */
	protected function prep_save( $entry ) {
		if( !$entry->tran_number ) {
			$entry->tran_number = $this->next_tran_number( $entry->pidm );
		}//end if

		return $entry;
	}//end prep_save
}//end class \PSU\AR\Transaction\Receivable
