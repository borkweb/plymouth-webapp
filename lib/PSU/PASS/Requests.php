<?php

/**
 *
 */
class PSU_PASS_Requests implements IteratorAggregate {
	/**
	 * Single pidm we are focusing in on. Optional.
	 */
	public $pidm = null;

	/**
	 * Single term code to focus on. Optional.
	 */
	public $termcode = null;

	/**
	 * Child request objects.
	 */
	public $requests = null;

	public function __construct( $pidm = null, $termcode = null ) {
		/// Assign pidm and termcode into object.
		$this->pidm = $pidm;
		$this->termcode = $termcode;
	}

	/**
	 * Accept in some raw, iterable requests data and populate $this->requests
	 * with objects.
	 */
	public function load( $requests_rows = null ) {
		if( $requests_rows === null ) {
			$requests_rows = $this->requests( $this->pidm, $this->termcode );
		}
		$this->requests = array();
		if (count($requests_rows) > 0 ) {
			foreach( $requests_rows as $request_row ) {
				$request = new PSU_PASS_Request( $request_row );
				$this->requests[$request_row['id']] = $request;
			}
		}
	}//end load

	/**
	 * Returns all tutor request information given a pidm and term code
	 */
	public function requests( $pidm, $termcode ) {
		$rows = array();
		// Setup SQL $args
		$params = array (
			'pidm' => $pidm,
			'term_code' => $termcode
		);
		// Setup $sql query. 
		$sql = "SELECT * 
		 				  FROM psu.pass_tutor_request 
						 WHERE term_code = :term_code
						   AND student_pidm = :pidm
						";
		// Execute and return results
		if ($results = PSU::db('banner')->Execute($sql,$params)) { 
			foreach($results as $row) {
				$rows[] = $row;
			}
			return $rows;
		}
	}//end requests

	/**
	 * Our requests iterator.
	 */
	public function getIterator() {
		return new ArrayIterator( $this->requests );
	}//end getIterator

}//end class PSU_PASS_Requests
