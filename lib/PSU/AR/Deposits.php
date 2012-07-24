<?php
namespace PSU\AR;

class Deposits extends \PSU\AR\TermAggregate {
	public function __construct( $pidm, $term_code = null ) {
		parent::__construct( __CLASS__, $pidm, $term_code );
	}//end __construct

	/**
	 * retrieve deposits for a person
	 */
	public function get() {
		$args = array(
			'pidm' => $this->pidm,
		);

		$sql = "BEGIN :c_cursor := tb_deposit.f_query_all(:pidm); END;";
		$rset = \PSU::db('banner')->ExecuteCursor($sql, 'c_cursor', $args);

		return $rset ? $rset : array();
	}//end get_deposits

	/**
	 * all receivables that are misc memos
	 */
	public function released( $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if

		return new \PSU\AR\Deposits\ReleasedFilterIterator( $it );
	}//end released

	/**
	 * all misc memo receivables from the current term
	 */
	public function released_current_term( $term_code ) {
		return $this->released( $this->current_term( $term_code ) );
	}//end released_current_term

	/**
	 * all misc memo receivables from future terms
	 */
	public function released_future_terms( $term_code ) {
		return $this->released( $this->future_terms( $term_code ) );
	}//end released_future_terms

	/**
	 * all misc memo receivables from previous terms
	 */
	public function released_previous_terms( $term_code ) {
		return $this->released( $this->previous_terms( $term_code ) );
	}//end released_previous_terms

	/**
	 * all receivables that are misc memos
	 */
	public function unexpired( $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if

		return new \PSU\AR\Deposits\UnexpiredFilterIterator( $it );
	}//end unexpired
}//end class
