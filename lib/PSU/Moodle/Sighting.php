<?php

namespace PSU\Moodle;

class Sighting {

	/**
	 * get_active_users
	 *
	 * function called to retrieve a list of users that have been active 
	 * since the passed in timestamp.
	 *
	 * @param array $args (Optional) args for population selection
	 */
	public static function get_active_users( $args = NULL) {

		$defaults = array(
			'timestamp' => time(),
			'termcode' => \PSU\Student::getCurrentTerm('UG'),
		);

		$args = \PSU::params( $args, $defaults );
		$args['termcode'] = '%' . $args['termcode'] . '%';

		$sql = "
			SELECT distinct(u.idnumber) 
			  FROM mdl_user u 
			  JOIN mdl_user_lastaccess l
			    ON l.userid = u.id 
			  JOIN mdl_course c
			    ON l.courseid = c.id
			 WHERE l.timeaccess > ?
			   AND u.idnumber > '' 
			   AND l.courseid > ''
			   AND c.shortname LIKE ?
		";

			return \PSU::db('moodle2')->GetCol( $sql, array( $args['timestamp'], $args['termcode'] ) );
	}//end function

	/**
	 * sight
	 *
	 * Function called to loop over an array of users and mark them as 
	 * sighted in moodle in Banner.
	 *
	 * @param array $args (Optional) args for population selection
	 */
	public static function sight( $args = NULL ) {

		$defaults = array(
			'timestamp' => time(),
			'termcode' => \PSU\Student::getCurrentTerm('UG'),
		);

		$args = \PSU::params( $args, $defaults );

		$BannerStudent = new \BannerStudent( \PSU::db('banner') );
		$successes = array();
		foreach( (array)self::get_active_users( $args ) as $idnumber ) {
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
