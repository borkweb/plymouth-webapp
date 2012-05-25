<?php

namespace PSU\AR\Fees;
use \PSU\Collection;

class RegistrationFees extends Collection {
	public static $child = '\PSU\AR\Fees\RegistrationFee';
	public $term_code;

	public function __construct( $term_code ) {
		$this->term_code = $term_code;
	}//end constructor

	/**
	 * Filter by detail code
	 */
	public function detail_code( $detail_code, $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if

		return new RegistrationFees\DetailCodeFilterIterator( $detail_code, $it );
	}//end detail_code

	public function get() {
		$sql = "
			SELECT *
			  FROM sfrrgfe
			 WHERE sfrrgfe_term_code = :term_code
			   AND sfrrgfe_type = 'STUDENT'
				 AND sfrrgfe_flat_fee_amount IS NOT NULL
		";

		$args = array(
			'term_code' => $this->term_code,
		);

		$results = \PSU::db('banner')->Execute( $sql, $args );
		return $results;
	}//end get

	/**
	 * Filter by rate code
	 */
	public function rate( $rate, $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if

		return new RegistrationFees\RateFilterIterator( $rate, $it );
	}//end rate

	/**
	 * Filter by residential code
	 */
	public function residential_code( $resd_code, $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if

		return new RegistrationFees\ResidentialFilterIterator( $resd_code, $it );
	}//end residential_code

	/**
	 * Filter by student_type
	 */
	public function student_type( $code, $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if

		return new RegistrationFees\StudentTypeFilterIterator( $code, $it );
	}//end student_type
}//end class
