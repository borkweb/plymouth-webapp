<?php

class PSU_Student_Finaid_Requirements implements IteratorAggregate {
	public $requirements;

	public $pidm;
	public $aid_year;

	public function __construct( $pidm, $aid_year = null ) {
		$this->pidm = $pidm;
		$this->aid_year = $aid_year ? $aid_year : \PSU\Student::getAidYear();
	}

	public function load( $requirement_rows = null ) {
		if( $requirement_rows === null ) {
			$requirement_rows = $this->get_requirements();
		}

		$this->requirements = array();

		foreach( $requirement_rows as $requirement_row ) {
			$requirement = new PSU_Student_Finaid_Requirement( $requirement_row );
			$this->requirements[] = $requirement;
		}

		usort( $this->requirements, array($this, 'sort_requirements') );
	}//end load

	public function sort_requirements( $a, $b ) {
		return strnatcasecmp( $a->longdesc_clean(), $b->longdesc_clean() );
	}

	/**
	 * Return requirements, skipping "academic information" requirements
	 * that could expose private data such as academic probation status.
	 *
	 * @return Iterator
	 */
	public function non_academic_info() {
		return new PSU_Student_Finaid_Requirements_AcademicInfoFilterIterator( $this->deduplicated_requirements() );
	}

	/**
	 * @return Iterator
	 */
	public function deduplicated_requirements() {
		return new PSU_Student_Finaid_Requirements_DuplicateFilterIterator( $this->getIterator() );
	}

	public function satisfied() {
		return new PSU_Student_Finaid_Requirements_SatisfiedFilterIterator( $this->deduplicated_requirements() );
	}

	public function unsatisfied() {
		return new PSU_Student_Finaid_Requirements_UnsatisfiedFilterIterator( $this->deduplicated_requirements() );
	}

	public function satisfied_non_academic() {
		return new PSU_Student_Finaid_Requirements_SatisfiedFilterIterator( $this->non_academic_info() );
	}

	public function unsatisfied_non_academic() {
		return new PSU_Student_Finaid_Requirements_UnsatisfiedFilterIterator( $this->non_academic_info() );
	}

	/**
	 *
	 */
	public function get_requirements() {
		$args = array(
			'pidm' => $this->pidm,
			'aidy' => $this->aid_year,
		);

		// baninst1.bwrkrhst: CURSOR web_requirement_c
		$sql = "
				 SELECT RRRAREQ_STAT_DATE,
						RTVTRST_CODE, RTVTRST_DESC, RTVTRST_SAT_IND,
						RTVTREQ_CODE, RTVTREQ_SHORT_DESC, RTVTREQ_LONG_DESC, RTVTREQ_INSTRUCTIONS, RTVTREQ_URL
				   FROM RRRAREQ, RTVTREQ, RTVTRST
				  WHERE RRRAREQ_AIDY_CODE = :aidy
					AND RRRAREQ_PIDM      = :pidm
					AND RTVTRST_INFO_ACCESS_IND = 'Y'
					AND RTVTREQ_INFO_ACCESS_IND = 'Y'
					AND RRRAREQ_INFO_ACCESS_IND = 'Y'
					AND RRRAREQ_TREQ_CODE = RTVTREQ_CODE
					AND RTVTREQ_ACTIVE_IND = 'Y'
					AND RRRAREQ_TRST_CODE = RTVTRST_CODE
					AND (   RRRAREQ_FUND_CODE IS NULL
						 OR (    RRRAREQ_FUND_CODE IS NOT NULL
							 AND NOT EXISTS (
									 SELECT 'X'
									   FROM RFRBASE
									  WHERE RFRBASE_FUND_CODE   = RRRAREQ_FUND_CODE
										AND RFRBASE_FED_FUND_ID = 'PELL')))
		";

		$rset = PSU::db('banner')->Execute( $sql, $args );
		return $rset;
	}

	public function getIterator() {
		return new ArrayIterator( $this->requirements );
	}
}//end class PSU_Student_Finaid_Requirements
