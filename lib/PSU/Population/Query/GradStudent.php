<?php

/**
 * Return a set of matched users for the PSU_Population object.
 */
class PSU_Population_Query_GradStudent extends PSU_Population_Query {
	public function query ( $args = array() ) {
		$defaults = array();

		$args = PSU::params( $args, $defaults );

		$sql = "
			SELECT gobsrid_sourced_id FROM gobsrid, datamart.ps_as_student_demographics WHERE 
			program NOT LIKE '%TRACK%' AND 
			gobsrid_pidm=pidm AND
			ug_gr='GR'
		";

		$matches = PSU::db('banner')->GetCol( $sql );
		return $matches;
	}
}
