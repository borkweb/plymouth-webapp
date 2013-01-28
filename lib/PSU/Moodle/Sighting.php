<?php

namespace PSU\Moodle;

class Sighting {

	/**
	 * get_active_users
	 *
	 * function called to retrieve a list of users that have been active 
	 * since the passed in timestamp.
	 *
	 * @param string $timestamp (Optional) timestamp for active window
	 */
	public static function get_active_users( $timestamp = NULL ) {

		if( !$timestamp ) {
			$timstamp = time();
		}//end if

		$sql = "
			SELECT idnumber 
			FROM mdl_user 
			WHERE last_access >= ? 
			AND idnumber > ''
		";

			return \PSU::db('moodle2')->GetCol( $sql, array( $timestamp ) );
	}//end function

	/**
	 * sight
	 *
	 * Function called to loop over an array of users and mark them as 
	 * sighted in moodle in Banner.
	 *
	 * @param string $timestamp (Optional) Time for activity window.
	 */
	public static function sight( $timestamp = NULL ) {

		if( !$timestamp ) {
			$timstamp = time();
		}//end if

		$BannerStudent = new \BannerStudent( \PSU::db('banner') );
		$successes = array();
		foreach( (array)self::get_active_users( $timestamp ) as $idnumber ) {
			$pidm = \PSU::get('idmobject')->getIdentifier( $idnumber, 'psu_id', 'pid' );
			if( \PSU::db('psc1')->GetOne("SELECT 1 FROM v_student_active WHERE pidm = :pidm", array('pidm' => $pidm)) ) {
				if( $BannerStudent->sightStudent( $pidm, 'MC' ) ) {
					$successes[] = $idnumber;
				}//end if
			}//end if
		}//end foreach

		return $successes;
	}//end function

}//end class
