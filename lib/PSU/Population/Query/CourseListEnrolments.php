<?php

/**
 * Return a set of matched users for the PSU_Population object.
 */
class PSU_Population_Query_CourseListEnrolments extends PSU_Population_Query {
	/**
	 * Looking for courses array to be in the following format:
	 * array(
	 *	'BU.4600',
	 *	'AG.4900',
	 *	'EPL.3960',
	 * );
	 * This way we won't be limited by CRN, but can grab multiple sections 
	 * and enrolments.
	 */
	public function query( $args = array() ) {

		$defaults = array(
			'identifier' => 'sourced_id',
			'term_code' => \PSU\Student::getCurrentTerm( $args['level_code'] ),
			'courses' => NULL
		);

		$args = PSU::params( $args, $defaults );

		foreach( (array)$args['courses'] as $course ) {
			$course = explode( '.', $course );
			$where .= "(ssbsect_subj_code='" . $course[0] . "' AND ssbsect_crse_numb='" . $course[1] . "') OR ";
		}//end foreach

		$where = rtrim( $where, ' OR ' );

		$sql = "
			SELECT DISTINCT ".$args['identifier']."
		      FROM sfrstcr 
			  JOIN psu_identity.person_identifiers
			    ON sfrstcr_pidm = pid
			  JOIN ssbsect
			    ON (sfrstcr_crn = ssbsect_crn AND 
				    sfrstcr_term_code = ssbsect_term_code)
			  WHERE " . $where . " 
				AND sfrstcr_term_code >= :term_code
				AND sfrstcr_rsts_code IN ('RE', 'RW')
		";

		unset( $args['courses'] );
		unset( $args['identifier'] );

		$matches = PSU::db('banner')->GetCol( $sql, $args );

		return $matches;
	}//end function
}//end class 
