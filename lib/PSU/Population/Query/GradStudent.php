<?php

/**
 * Return a set of matched users for the PSU_Population object.
 */
class PSU_Population_Query_GradStudent extends PSU_Population_Query {
	public function query ( $args = array() ) {

		$defaults = array(
			'identifier' => 'sourced_id',
		);

		$args = PSU::params( $args, $defaults );

		$sql = "
			SELECT DISTINCT b.".$args['identifier']."
			  FROM sgbstdn
			  JOIN psu.v_student_account_active a
				ON sgbstdn_pidm = a.pidm AND sgbstdn_term_code_eff = a.term_code_eff
			  JOIN PSU_IDENTITY.person_identifiers b
				ON a.pidm = b.pid
			 WHERE sgbstdn_degc_code_1 <> '000000'
			   AND sgbstdn_levl_code = 'GR'
		";

		\PSU::db('banner')->debug = true;
		$matches = PSU::db('banner')->GetCol( $sql, $args );
		
		return $matches;
	}
}
