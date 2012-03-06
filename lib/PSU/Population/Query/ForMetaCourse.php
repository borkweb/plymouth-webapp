<?php

/**
 * Return a set of matched users for the PSU_Population object.
 */
class PSU_Population_Query_ForMetaCourse extends PSU_Population_Query {
	public function query( $args = array() ) {

		$defaults = array(
			'term_code' => null,
			'subj_code' => 'UG',
			'level_code' => 'UG',
			'rsts_code' => 'RE'
		);

		$args = PSU::params( $args, $defaults );

		if( ! isset($args['term_code']) ) {
			$args['term_code'] = \PSU\Student::getCurrentTerm( $args['level_code'] );
		}

		foreach( (array)$args['subj_code'] as $subj_code ) {
			$subj_where .= "ssbsect_subj_code = '".$subj_code."' OR ";
		}
		$subj_where = substr( $subj_where, 0, -4 );
		
		foreach( (array)$args['rsts_code'] as $rsts_code ) {
			$rsts_where .= "sfrstcr_rsts_code = '".$rsts_code."' OR ";
		}
		$rsts_where = substr( $rsts_where, 0, -4 );
		
		$sql = "SELECT DISTINCT gobsrid_sourced_id
				FROM sfrstcr, ssbsect, gobsrid 
				WHERE sfrstcr_pidm = gobsrid_pidm
				AND sfrstcr_crn = ssbsect_crn
				AND sfrstcr_term_code = ssbsect_term_code
				AND (".$subj_where.") 
				AND (".$rsts_where.") 
				AND sfrstcr_term_code >= '".$args['term_code']."' 
		";

		$matches = PSU::db('banner')->GetCol( $sql, $args );

		return $matches;
	}
}
