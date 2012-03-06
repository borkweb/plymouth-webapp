<?php

class PSU_Student_Aidyear_AttendanceCost implements IteratorAggregate {
	/**
	 * Cost components (line items).
	 */
	public $components = null;

	public $pidm;
	public $aid_year;

	public function __construct( $pidm, $aid_year = null ) {
		$this->pidm = $pidm;
		$this->aid_year = $aid_year ? $aid_year : \PSU\Student::getAidYear();
	}//end __construct

	public function load( $components_rows = null ) {
		$this->components = array();

		if( $components_rows === null ) {
			$components_rows = $this->_get_components();
		}

		foreach( $components_rows as $component_row ) {
			$component = new PSU_Student_Aidyear_AttendanceCost_Component( $component_row );
			$this->components[] = $component;
		}
	}//end load

	private function _get_components() {
		$args = array(
			'pidm' => $this->pidm,
			'aidy' => $this->aid_year,
			'code' => null,
		);

		$sql = "
			SELECT RTVCOMP_DESC,
				   NVL(RBRACMP_AMT, 0) RBRACMP_AMT
			  FROM RBRACMP, RTVCOMP
			 WHERE RBRACMP_AIDY_CODE = :aidy
			   AND RBRACMP_PIDM      = :pidm
			   AND RBRACMP_COMP_CODE = RTVCOMP_CODE
			   -- 080500-5
			   AND ((  :code IS NULL
				   AND NOT EXISTS
					  (SELECT 'X'
						 FROM RTVBTYP
						WHERE RTVBTYP_PELL_IND  = 'Y'
						  AND RBRACMP_BTYP_CODE = RTVBTYP_CODE))
					OR
					(  :code = 'PELL'
				   AND EXISTS
					  (SELECT 'X'
						 FROM RTVBTYP
						WHERE RTVBTYP_PELL_IND  = 'Y'
						  AND RBRACMP_BTYP_CODE = RTVBTYP_CODE)))
			ORDER BY RTVCOMP_PRINT_SEQ_NO
		";

		$rset = PSU::db('banner')->Execute( $sql, $args );

		return $rset;
	}//end _get_components

	public function not_empty() {
		return $this->component_count() > 0;
	}//end is_empty

	public function is_empty() {
		return ! $this->not_empty();
	}

	public function component_count() {
		return count( $this->components );
	}

	public function getIterator() {
		return new ArrayIterator( $this->components );
	}//end getIterator

	public function total() {
		$total = 0;

		foreach( $this as $component ) {
			$total += $component->amount;
		}

		return $total;
	}//end total

	public function total_formatted() {
		return PSU_MoneyFormatter::create()->format( $this->total() );
	}
}//end class PSU_Student_Aidyear_AttendanceCost
