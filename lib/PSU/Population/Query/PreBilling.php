<?php

namespace PSU\Population\Query;

class PreBilling extends \PSU_Population_Query {
	public $full_part;
	public $residential_code;
	public $rate_code;

	public function __construct( $full_part, $residential_code, $rate_code ) {
		$this->full_part = $full_part;
		$this->residential_code = $residential_code;
		$this->rate_code = $rate_code;
	}//end constructor

	public function query( $args = array() ) {
		$defaults = array(
			'full_part' => $this->full_part,
			'residential_code' => $this->residential_code,
			'rate_code' => $this->rate_code,
		);

		$args = \PSU::params( $args, $defaults );

		$sql = "
			SELECT pidm
			  FROM v_prebilling_candidates
			 WHERE full_part_ind = :full_part
				 AND resd_code = :residential_code
				 AND rate_code = :rate_code
		";

		$results = \PSU::db('banner')->GetCol( $sql, $args );
		return $results;
	}
}
