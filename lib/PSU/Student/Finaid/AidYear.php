<?php

class PSU_Student_Finaid_AidYear extends PSU_DataObject {
	public $aliases = array(
		'aidy_start_year' => 'start_year',
		'aidy_end_year' => 'end_year',
	);

	/**
	 * Return aid year end date as a unix timestamp.
	 */
	public function end_date_ts() {
		return strtotime( $this->aidy_end_date );
	}//end end_date_ts

	/**
	 * Return aid year start date as a unix timestamp.
	 */
	public function start_date_ts() {
		return strtotime( $this->aidy_start_date );
	}//end start_date_ts

	/**
	 * Return years in the format 2011-2012.
	 */
	public function year_range() {
		return sprintf( "%s-%s", $this->start_year, $this->end_year );
	}//end year_range
}//end class PSU_Student_Finaid_AidYear
