<?php

/**
 * Return a set of matched users for the PSU_Population object.
 */
class PSU_Population_Query_HRDuplicates extends PSU_Population_Query {
	public function query( $args = array() ) {
		$defaults = array();

		$args = PSU::params( $args, $defaults );

		$ssns = $this->ssn_duplicates();

		foreach( $ssns as $key => $value ) {
			if( $key > 0 ) {
				$where .= ",";
			}//end if

			$where .= ":".$key;
		}//end foreach

		$sql = "SELECT DISTINCT usnh_pidm,
			             ssn,
									 usnh_id,
									 last_name,
									 first_name,
									 mi
			        FROM hr_employee
						 WHERE ssn IN (".$where.")";

		$matches = PSU::db('banner')->GetAll( $sql, $ssns );
		return $matches;
	}//end query

	public function ssn_duplicates() {
		$data = array();

		$sql = "SELECT ssn,
			             count(ssn)
						  FROM spbpers
							     JOIN (
										 SELECT distinct ssn
		                   FROM hr_employee
		                  WHERE job_status = 'A'
		                    AND sysdate BETWEEN begin_date - 7 AND end_date
		               ) ON ssn = spbpers_ssn
						  GROUP BY ssn
		         HAVING count(ssn) > 1";

		$data = $this->db->GetCol( $sql );

		return $data;
	}//end ssn_duplicates
}//end PSU_Population_Query_HRDuplicates
