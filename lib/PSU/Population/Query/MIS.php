<?php

class PSU_Population_Query_MIS extends PSU_Population_Query {

	/**
	 * Return a set of matched users for the PSU_Population object.
	 */
	public function query( $args = array() ) {

		$sql = "
			SELECT DISTINCT psu_id
			  FROM v_idm_attributes
			 WHERE attribute = 'mis'
		";

		return PSU::db('banner')->GetCol( $sql, $args );
	}
}
