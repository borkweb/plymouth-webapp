<?php

namespace PSU\AR\Transaction;

use PSU;
use PSU\AR\Transaction;

class Memo extends Transaction {
	public $type = 'Memo';
	private $billable = 'Y';

	/**
	 * ye olde magical constructor
	 *
	 * @param mixed $person Person object
	 * @param float $amount amount to split and apply
	 * @param boolean $billable Set TBRMEMO billable_ind = 'Y'?
	 */
	public function __construct( $person, $amount, $billable = TRUE ) {
		$this->billable( (bool) $billable );

		parent::__construct( $person, $amount );
	}//end constructor

	/**
	 * set whether or not a memo is billable
	 * @param boolean $is_billable
	 */
	public function billable( $is_billable ) {
		$this->billable = $is_billable ? 'Y' : 'N';
	}//end billable

	/**
	 * create an entry object for the transaction's entries
	 */
	protected function generate_entry( $payment ) {
		$payment['billing_ind'] = $this->billable;

		$entry = new \PSU\AR\Memo( $payment );

		return $entry;
	}//end generate_entry

	/**
	 * generates the next transaction number for the given user
	 */
	public static function next_tran_number( $pidm ) {
		$sql = "SELECT NVL(max(tbraccd_tran_number)+1,1) FROM tbraccd WHERE tbraccd_pidm = :pidm";
		return \PSU::db('banner')->GetOne( $sql, array('pidm' => $pidm) );
	}//end next_tran_number

	/**
	 * Set memo-specific entry values
	 */
	protected function prep_save( $entry ) {
		$max_number = \PSU\AR\Memos::max_tran_number( $entry->pidm );
		$entry->tran_number = $max_number + 1;
		return $entry;
	}//end prep_save
}//end class
