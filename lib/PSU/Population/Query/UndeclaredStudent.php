<?php

/**
 * Return a set of matched users for the PSU_Population object.
 */
class PSU_Population_Query_UndeclaredStudent extends PSU_Population_Query {
	public function query( $args = array() ) {

		$defaults = array(
			'term_code' => null,
			'level_code' => 'UG',
			'incoming' => false,
		);

		$args = PSU::params( $args, $defaults );

		if( ! isset($args['term_code']) ) {
			$args['term_code'] = \PSU\Student::getCurrentTerm( $args['level_code'] );
		}

		/**
		 * The SQL below is for if this is fully adopted. It is a more acurate represenatation and always applies to current term
		 *
		 *	
		$sql = "SELECT DISTINCT a.pidm
				FROM psu.mv_curriculum a,
					 psu.v_student_active b
				WHERE a.pidm = b.pidm
				AND a.majr_code = '0000' 
				AND b.levl_code = ':level_code' 
		";
		

		if( $args['incoming'] ) {
			$sql .= " AND b.styp_code = 'N'";	
		}

		return PSU::db('banner')->GetCol( $sql, $args );
		 */

		$sql = "
			SELECT DISTINCT gobsrid_sourced_id
			FROM gobsrid, baninst1.as_student_enrollment_summary 
			WHERE gobsrid_pidm = pidm_key 
			AND majr_code1 = '0000' 
			AND ests_code = 'EL' 
			AND stst_code = 'AS' 
			AND term_code_key = '201210' 
			AND styp_code = 'N' 
			AND levl_code = 'UG'
		";

		return PSU::db('banner')->GetCol( $sql, $args );
	}
}
