<?php

/**
 * Return a set of matched users for the PSU_Population object.
 */
class PSU_Population_Query_UndeclaredStudent extends PSU_Population_Query {
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
			SELECT DISTINCT gobsrid_sourced_id
			FROM gobsrid, baninst1.as_student_enrollment_summary 
			WHERE gobsrid_pidm = pidm_key 
			AND majr_code1 = '0000' 
			AND ests_code = 'EL' 
			AND stst_code = 'AS' 
			AND term_code_key = :term_code 
			AND styp_code = 'N' 
			AND levl_code = :level_code
		";

		return PSU::db('banner')->GetCol( $sql, $args );
	}
}
