<?php

/**
 * Return a set of matched users for the PSU_Population object.
 */
class PSU_Population_Query_PaidEmployee extends PSU_Population_Query {
	public function query ( $args = array() ) {
		$defaults = array(
			'identifier' => 'sourced_id',
		);

		$args = PSU::params( $args, $defaults );

		$sql = "
			SELECT DISTINCT idnt." . $args['identifier'] . " 
			FROM psu_identity.person_identifiers idnt,
				 psu.v_hr_psu_employee_active act 
			WHERE idnt.pid = act.pidm
		";

		$matches = PSU::db('banner')->GetCol( $sql );
		return $matches;
	}
}
