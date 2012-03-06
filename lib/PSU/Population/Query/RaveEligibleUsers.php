<?php
namespace PSU\Population\Query;
/**
 * Return a set of matched users for the PSU_Population object.
 */
class RaveEligibleUsers  extends \PSU_Population_Query {
	public function query( $args = array() ) {

		$defaults = array(
			'non_pidm' => false,
		);
	
		$matches = array();

		$args = \PSU::params( $args, $defaults );

		$active_sql = "
			SELECT wp_id 
			FROM v_rave_eligible 
			";

		$active_users = \PSU::db('banner')->GetAll( $active_sql );

		foreach( $active_users as $user ) {
			$matches[ $user['wp_id'] ] = $user;
		}//end foreach

		if( $args['non_pidm'] ) {
			//Get the non-pidm users like family and such out of MySQL
			$non_pidm = \PSU::db('connect')->GetCol("SELECT user_login FROM v_users_without_pidms");

			foreach( $non_pidm as $user ) {
				$matches[ $user ] = array( 
					'wp_id' => $user
				);
			}//end foreach
		}//end else

		return $matches;
	}//end query
}//end RaveEligibleUsers
