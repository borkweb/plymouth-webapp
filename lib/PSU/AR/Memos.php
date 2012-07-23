<?php
namespace PSU\AR;

class Memos extends TermAggregate {
	public function __construct( $pidm, $term_code = null ) {
		parent::__construct( __CLASS__, $pidm, $term_code );
	}//end __construct

	/**
	 * all receivables that are active memos
	 */
	public function active_memos( $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if

		return new \PSU\AR\Memos\ActiveFilterIterator( $it );
	}//end active_memos

	/**
	 * all active memo receivables from the current term
	 */
	public function active_memos_current_term( $term_code ) {
		return $this->active_memos( $this->current_term( $term_code ) );
	}//end active_memos_current_term

	/**
	 * all active memo receivables from future terms
	 */
	public function active_memos_future_terms( $term_code ) {
		return $this->active_memos( $this->future_terms( $term_code ) );
	}//end active_memos_future_terms

	/**
	 * all active memo receivables from previous terms
	 */
	public function active_memos_previous_terms( $term_code ) {
		return $this->active_memos( $this->previous_terms( $term_code ) );
	}//end active_memos_previous_terms

	/**
	 * all receivables that are bill memos
	 */
	public function bill_memos( $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if

		return new \PSU\AR\Memos\BillFilterIterator( $this->active_memos( $it ) );
	}//end bill_memos

	/**
	 * all bill memo receivables from the current term
	 */
	public function bill_memos_current_term( $term_code ) {
		return $this->bill_memos( $this->current_term( $term_code ) );
	}//end bill_memos_current_term

	/**
	 * all bill memo receivables from future terms
	 */
	public function bill_memos_future_terms( $term_code ) {
		return $this->bill_memos( $this->future_terms( $term_code ) );
	}//end bill_memos_future_terms

	/**
	 * all bill memo receivables from previous terms
	 */
	public function bill_memos_previous_terms( $term_code ) {
		return $this->bill_memos( $this->previous_terms( $term_code ) );
	}//end bill_memos_previous_terms

	/**
	 * retrieve memos for a person
	 */
	public function get() {
		$args = array(
			'pidm' => $this->pidm,
		);

		$sql = "SELECT * FROM tbrmemo WHERE tbrmemo_pidm = :pidm";
		$rset = \PSU::db('banner')->Execute($sql, $args);

		return $rset ? $rset : array();
	}//end get

	/**
	 * retrieves the maximum tran_number
	 *
	 * Always retrieve from the database in the event that a memo was inserted
	 * between to executions of this method.
	 */
	public static function max_tran_number( $pidm ) {
		$sql = "SELECT max(tbrmemo_tran_number) FROM tbrmemo WHERE tbrmemo_pidm = :pidm";
		return \PSU::db('banner')->GetOne( $sql, array( 'pidm' => $pidm ) ) ?: 0;
	}//end max_tran_number

	/**
	 * all receivables that are misc memos
	 */
	public function misc_memos( $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if

		return new \PSU\AR\Memos\MiscFilterIterator( $this->active_memos( $it ) );
	}//end misc_memos

	/**
	 * all misc memo receivables from the current term
	 */
	public function misc_memos_current_term( $term_code ) {
		return $this->misc_memos( $this->current_term( $term_code ) );
	}//end misc_memos_current_term

	/**
	 * all misc memo receivables from future terms
	 */
	public function misc_memos_future_terms( $term_code ) {
		return $this->misc_memos( $this->future_terms( $term_code ) );
	}//end misc_memos_future_terms

	/**
	 * all misc memo receivables from previous terms
	 */
	public function misc_memos_previous_terms( $term_code ) {
		return $this->misc_memos( $this->previous_terms( $term_code ) );
	}//end misc_memos_previous_terms
}//end class
