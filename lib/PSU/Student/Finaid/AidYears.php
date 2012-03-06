<?php

class PSU_Student_Finaid_AidYears implements ArrayAccess, IteratorAggregate {
	public $aid_years = array();

	public function __construct( ) {}

	public function load() {
		if( $this->aid_years ) {
			return;
		}//end if

		if( $rows = $this->get_aid_years() ) {
			foreach( $rows as $row ) {
				$row = PSU::cleanKeys('robinst_', '', $row);

				$aid_year = new PSU_Student_Finaid_AidYear( $row );
				$this->aid_years[ $aid_year->aidy_code ] = $aid_year;
			}//end foreach
		}//end if
	}//end load

	public function get_aid_years() {
		$args = array();

		$sql = "
			SELECT robinst.*,
						 CASE
			         WHEN (sysdate BETWEEN robinst_aidy_start_date AND robinst_aidy_end_date) THEN 'Y'
			         ELSE 'N'
			       END is_current
		    FROM robinst
		   WHERE robinst_info_access_ind = 'Y'
		     ORDER BY robinst_aidy_start_date DESC
		";

		$rset =  PSU::db('banner')->Execute( $sql, $args );
		return $rset;
	}//end get_aid_years

	public function getIterator() {
		return new ArrayIterator( $this->aid_years );
	}//end getIterator

	/**
	 * Return the highest aid year for a person.
	 */
	public function max_aid_year( $pidm ) {
		$sql = "
			SELECT rcrapp1_aidy_code
			FROM rcrapp1
			WHERE rcrapp1_pidm = :pidm
			ORDER BY rcrapp1_aidy_code DESC
		";

		$args = array(
			'pidm' => $pidm,
		);

		$aid_year = PSU::db('banner')->GetOne( $sql, $args );

		return $aid_year ?: null;
	}//end max_aid_year

	public function offsetGet( $aidy ) {
		return $this->aid_years[$aidy];
	}

	public function offsetSet( $aidy, $value ) {
		throw new Exception('you may not manually set an aid year');
	}

	public function offsetExists( $aidy ) {
		return isset( $this->aid_years[$aidy] );
	}

	public function offsetUnset( $aidy ) {
		unset( $this->aid_years[$aidy] );
	}
}//end PSU_Student_Finaid_AidYears
