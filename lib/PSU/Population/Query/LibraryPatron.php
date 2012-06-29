<?php

namespace PSU\Population\Query;

/**
 * Return a set of matched users for the PSU_Population object.
 */
class LibraryPatron extends \PSU_Population_Query {

	public function query( $args = array() ) {

		$args = \PSU::params( $args, $defaults );

		$sql = "
			SELECT DISTINCT pidm
			  FROM v_account
		";

		$results = \PSU::db('banner')->GetCol( $sql, $args );
		return $results;
	}
}
