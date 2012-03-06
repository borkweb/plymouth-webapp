<?php
namespace PSU\Population\Query;
/**
 * Return a set of matched users for the PSU_Population object.
 */
class RaveEligibleUsersWithMobile  extends \PSU_Population_Query {
	public function query( $args = array() ) {

		$defaults = array(
		);

		$matches = array();
	
		$args = \PSU::params( $args, $defaults );

		$active_sql = "SELECT * FROM v_rave_eligible_phones";

		$active_users = \PSU::db('banner')->GetAll( $active_sql );

		foreach( $active_users as $user ) {
			$matches[ $user['wp_id'] ] = $user;
		}//end foreach

		return $matches;
	}//end query
}//end RaveEligibleUsersWithMobile
