<?php

/**
 * Base container for all awards.
 */
class PSU_Student_Finaid_Awards implements IteratorAggregate {
	private $awards = array();

	/**
	 * PSU_Student_Finaid_Awards_Messages
	 */
	private $fund_messages;

	public $pidm;
	public $aid_year;

	/**
	 * PSU_Student_Finaid_Awards_Terms
	 */
	public $terms;

	/**
	 * @param $pidm
	 * @param $aid_year string i.e. '1011'
	 * @param $fund_messages stdClass an object for reading fund messages
	 */
	public function __construct( $pidm, $aid_year = null, $fund_messages = null ) {
		$this->pidm = $pidm;
		$this->aid_year = $aid_year ? $aid_year : \PSU\Student::getAidYear();

		$this->fund_messages = $fund_messages ? $fund_messages : new PSU_Student_Finaid_Awards_Messages( $pidm, $this->aid_year );
	}

	public function has_awards() {
		return $this->award_count() > 0;
	}

	/**
	 * All awards that have authorized amounts
	 */
	public function authorized() {
		return new PSU_Student_Finaid_Awards_AuthorizedFilterIterator( $this->getIterator() );
	}

	/**
	 * All awards that have authorized amounts
	 */
	public function authorized_current_term( $term_code ) {
		return $this->current_term( $this->authorized( $this->getIterator() ), $term_code );
	}

	/**
	 * All awards that have authorized amounts
	 */
	public function authorized_future_terms( $term_code ) {
		return $this->future_terms( $this->authorized( $this->getIterator() ), $term_code );
	}

	/**
	 * All awards that have authorized amounts
	 */
	public function authorized_previous_terms( $term_code ) {
		return $this->previous_terms( $this->authorized( $this->getIterator() ), $term_code );
	}

	public function award_count() {
		return count( $this->awards );
	}

	/**
	 * all receivables from the current term
	 */
	public function current_term( $term_code ) {
		return new \PSU\AR\TermAggregate\CurrentTermFilterIterator( $this->getIterator(), $term_code );
	}//end current_term

	/**
	 * all receivables from future terms
	 */
	public function future_terms( $term_code ) {
		return new \PSU\AR\TermAggregate\FutureTermsFilterIterator( $this->getIterator(), $term_code );
	}//end future_terms

	public function load( $award_rows = null ) {
		if( $this->awards ) {
			return;
		}

		$this->terms = new PSU_Student_Finaid_Awards_Terms;

		if( !isset($award_rows) ) {
			$award_rows = $this->get_awards();
		}

		foreach( $award_rows as $award_row ) {
			$award_term = new PSU_Student_Finaid_Award_Term( $award_row );
			$fund_code = $award_term->fund_code;

			if( isset($this->awards[$fund_code]) ) {
				$award = $this->awards[$fund_code];
			} else {
				$award = new PSU_Student_Finaid_Award( $award_term, $this->fund_messages );
				$this->awards[$fund_code] = $award;
			}

			$award->add( $award_term );
			$this->terms->add( $award_term );
		}
	}//end load

	/**
	 * All awards that do not have a status of "Offered"
	 */
	public function exclude_offered() {
		return new PSU_Student_Finaid_Awards_ExcludeOfferedFilterIterator( $this->getIterator() );
	}

	/**
	 * All awards that do not have a status of "Offered"
	 */
	public function offered() {
		return new PSU_Student_Finaid_Awards_OfferedFilterIterator( $this->getIterator() );
	}

	/**
	 * all receivables from the previous term
	 */
	public function previous_terms( $term_code ) {
		return new \PSU\AR\TermAggregate\PreviousTermsFilterIterator( $this->getIterator(), $term_code );
	}//end previous_terms

	public function sum( $it = null ) {
		if( $it == null ) {
			$it = $this->getIterator();
		}

		return PSU_Student_Finaid_Award_Sum::create( $it );
	}

	public function get_awards() {
		$args = array(
			'pidm' => $this->pidm,
			'aidy' => $this->aid_year,
		);

		$sql = "
		   SELECT RPRATRM_PERIOD,
				  ROBPRDS_DESC,
				  RPRAWRD_FUND_CODE,
				  RFRBASE_FUND_TITLE,
				  RFRBASE_FUND_TITLE_LONG,
				  RTVAWST_DESC,
				  RPRATRM_OFFER_AMT,
				  RPRATRM_ACCEPT_AMT,
				  RPRATRM_DECLINE_AMT,
				  RPRATRM_CANCEL_AMT,
				  rprawrd_authorize_amt,
				  rprawrd_authorize_date,
				  rprawrd_memo_amt,
				  rprawrd_memo_date,
				  tbbdetc_detail_code,
				  tbbdetc_desc,
				  tbbdetc_type_ind,
				  ROBPRDS_SEQ_NO
			 FROM RPRAWRD,
				  RPRATRM,
				  RTVAWST,
				  ROBPRDS,
				  RORSTAT,
				  RORWEBR,
				  RFRBASE
				  LEFT OUTER JOIN tbbdetc
				  	ON tbbdetc_detail_code = rfrbase_detail_code
			WHERE RPRAWRD_PIDM      = :pidm
			  AND RPRAWRD_AWST_CODE = RTVAWST_CODE
			  AND NVL(RPRAWRD_INFO_ACCESS_IND, 'Y') = 'Y'
			  AND RFRBASE_INFO_ACCESS_IND = 'Y'
			  AND RPRAWRD_FUND_CODE = RFRBASE_FUND_CODE
			  AND RTVAWST_INFO_ACCESS_IND = 'Y'
			  AND RPRAWRD_AIDY_CODE = RPRATRM_AIDY_CODE
			  AND RPRAWRD_PIDM      = RPRATRM_PIDM
			  AND RPRAWRD_FUND_CODE = RPRATRM_FUND_CODE
			  AND ROBPRDS_PERIOD    = RPRATRM_PERIOD
			  AND RORSTAT_PIDM      = RPRAWRD_PIDM
			  AND RORSTAT_AIDY_CODE = RPRAWRD_AIDY_CODE
			  AND NVL(RORSTAT_INFO_ACCESS_IND, RORWEBR_NULL_INFOACCESS_IND) = 'Y'
			  AND (  (   NVL(RFRBASE_FED_FUND_ID, '*')  = 'PELL'
					 AND bwrkolib.F_CheckPellCrossover(
							:aidy,
							:pidm,
							RPRAWRD_AIDY_CODE,
							RPRATRM_PERIOD) = 'Y'
					 )
				  OR (   NVL(RFRBASE_FED_FUND_ID, '*') <> 'PELL'
					 AND RPRAWRD_AIDY_CODE  = :aidy
					 )
				  )
		  UNION ALL
		   SELECT '~',
				  'Unscheduled',
				  RPRAWRD_FUND_CODE,
				  RFRBASE_FUND_TITLE,
				  RFRBASE_FUND_TITLE_LONG,
				  RTVAWST_DESC,
				  RPRAWRD_ORIG_OFFER_AMT,
				  RPRAWRD_ACCEPT_AMT,
				  RPRAWRD_DECLINE_AMT,
				  RPRAWRD_CANCEL_AMT,
				  rprawrd_authorize_amt,
				  rprawrd_authorize_date,
				  rprawrd_memo_amt,
				  rprawrd_memo_date,
				  tbbdetc_detail_code,
				  tbbdetc_desc,
				  tbbdetc_type_ind,
				  99999999 ROBPRDS_SEQ_NO
			 FROM RPRAWRD,
				  RTVAWST,
				  RFRBASE
				  LEFT OUTER JOIN tbbdetc
				  	ON tbbdetc_detail_code = rfrbase_detail_code
			WHERE RPRAWRD_AIDY_CODE = :aidy
			  AND RPRAWRD_PIDM      = :pidm
			  AND RPRAWRD_AWST_CODE = RTVAWST_CODE
			  AND NVL(RPRAWRD_INFO_ACCESS_IND, 'Y') = 'Y'
			  AND RFRBASE_INFO_ACCESS_IND = 'Y'
			  AND RPRAWRD_FUND_CODE = RFRBASE_FUND_CODE
			  AND RTVAWST_INFO_ACCESS_IND = 'Y'
			  AND NOT EXISTS
				  (SELECT 'X'
					 FROM RPRATRM
					WHERE RPRAWRD_AIDY_CODE = RPRATRM_AIDY_CODE
					  AND RPRAWRD_PIDM      = RPRATRM_PIDM
					  AND RPRAWRD_FUND_CODE = RPRATRM_FUND_CODE)
			ORDER BY RTVAWST_DESC, ROBPRDS_SEQ_NO, RPRAWRD_FUND_CODE
		";

		$rset =  PSU::db('banner')->Execute( $sql, $args );
		return $rset;
	}//end get_awards

	/**
	 * Return an iterator for all known terms.
	 * @return Iterator
	 */
	public function terms() {
		return $this->terms->terms();
	}

	/**
	 * Return an iterator for all known term codes.
	 * @return Iterator
	 */
	public function termcodes() {
		return $this->terms->termcodes();
	}

	public function getIterator() {
		return new ArrayIterator( $this->awards );
	}//end getIterator
}//end PSU_Student_Finaid_Awards
