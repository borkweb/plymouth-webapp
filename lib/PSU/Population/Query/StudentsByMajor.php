<?php

/**
 * Return a set of matched users for the PSU_Population object.
 */
class PSU_Population_Query_StudentsByMajor extends PSU_Population_Query {
	public function query( $args = array() ) {

		$defaults = array(
			'identifier' => 'sourced_id',
			'levl_code' => 'UG',
			'majr_code' => 'NONE',
			'termcode' => \PSUStudent::getCurrentTerm('UG'),
		);

		$args = PSU::params( $args, $defaults );

		if( is_array( $args['majr_code'] ) ) {
			$majr_where = "IN (";

			foreach( $args['majr_code'] as $majr ) {
				$majr_where .= "'".$majr."',";
			}//end foreach

			$majr_where = rtrim( $majr_where, ',' );
			$majr_where .= ")";
		} else {
			$majr_where = "= '".$args['majr_code']."'";
		}//end if/else

		$sql = "
			SELECT DISTINCT ".$args['identifier']."
			  FROM v_curriculum_learner
			  JOIN psu_identity.person_identifiers
				ON pidm = pid
			 WHERE levl_code = :levl_code
			   AND majr_code ".$majr_where." 
			   AND lfst_code = 'MAJOR'
			   AND catalog_term >= :termcode
		";

		$matches = PSU::db('banner')->GetCol( $sql, $args );

		return $matches;
	}
}
