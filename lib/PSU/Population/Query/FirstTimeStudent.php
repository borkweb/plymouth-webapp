<?php

/**
 * Return a set of matched users for the PSU_Population object.
 */
class PSU_Population_Query_FirstTimeStudent extends PSU_Population_Query {
	public function query( $args = array() ) {
		$defaults = array(
			'term_code' => null,
			'level_code' => 'UG',
		);

		$args = PSU::params( $args, $defaults );

		if( ! isset($args['term_code']) ) {
			$args['term_code'] = \PSU\Student::getCurrentTerm( $args['level_code'] );
		}

		$sql = "
			SELECT sgbstdn_pidm FROM sgbstdn WHERE
			sgbstdn_stst_code = 'AS' AND
			sgbstdn_styp_code IN ('I', 'N', 'T') AND
			sgbstdn_term_code_admit = :term_code AND
			sgbstdn_levl_code = :level_code
		";

		$matches = PSU::db('banner')->GetCol( $sql, $args );
		return $matches;
	}
}
