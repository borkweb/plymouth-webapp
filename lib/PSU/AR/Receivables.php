<?php
namespace PSU\AR;

class Receivables extends \PSU\AR\TermAggregate {
	public function __construct( $pidm, $term_code = null ) {
		parent::__construct( __CLASS__, $pidm, $term_code );
	}//end __construct

	/**
	 * retrieve receivables for a person
	 */
	public function get() {
		$args = array(
			'pidm' => $this->pidm,
		);

		$sql = "SELECT * FROM tbraccd WHERE tbraccd_pidm = :pidm";
		$rset = \PSU::db('banner')->Execute($sql, $args);

		return $rset ? $rset : array();
	}//end get

	/**
	 * all receivables that aren't uncollectable parking fines 
	 */
	public function exclude_uncollectable_parking_fines( $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if

		return new \PSU\AR\Receivables\ExcludeUncollectableParkingFineFilterIterator( $it );
	}//end exclude_uncollectable_parking_fines_memos

	/**
	 * all receivables that aren't write off memos
	 */
	public function exclude_write_offs( $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if

		return new \PSU\AR\Receivables\ExcludeWriteOffFilterIterator( $it );
	}//end exclude_write_off_memos

	public function exclude_non_bill_entries( $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if

		return $this->exclude_uncollectable_parking_fines( $this->exclude_write_offs( $it ) );
	}//end exclude_non_bill_entries

	/**
	 * all receivables by misc billing data
	 */
	public function misc_billing_charges( $detail_code, $payment_id = null, $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if

		return new \PSU\AR\Receivables\MiscBillingRecordIterator( $it, $detail_code, $payment_id );
	}//end misc_billing_charges

	/**
	 * all receivables that are misc charges
	 */
	public function misc_charges( $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if

		return new \PSU\AR\Receivables\MiscChargesFilterIterator( $it );
	}//end misc_charges

	/**
	 * all misc charge receivables from the current term
	 */
	public function misc_charges_current_term( $term_code ) {
		return $this->misc_charges( $this->current_term( $term_code ) );
	}//end misc_charges_current_term

	/**
	 * all misc charge receivables from future terms
	 */
	public function misc_charges_future_terms( $term_code ) {
		return $this->misc_charges( $this->future_terms( $term_code ) );
	}//end misc_charges_future_terms

	/**
	 * all misc charge receivables from previous terms
	 */
	public function misc_charges_previous_terms( $term_code ) {
		return $this->misc_charges( $this->previous_terms( $term_code ) );
	}//end misc_charges_previous_terms
}//end class
