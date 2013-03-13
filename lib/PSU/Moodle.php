<?php

namespace PSU;

class Moodle {

	public function __construct( $params = NULL ) {
	}//end function

	public static function user_exists( $identifier ) {
		$sql = "
			SELECT mdl_user.idnumber
			  FROM mdl_user
			  JOIN mdl_enrol_shebang_person
			    ON mdl_enrol_shebang_person.userid_logon = mdl_user.username
			 WHERE mdl_user.username = ?
					OR mdl_user.idnumber = ?
					OR mdl_user.email = ?
					OR mdl_enrol_shebang_person.source_id = ?
					OR mdl_enrol_shebang_person.userid_logon = ?
					OR mdl_enrol_shebang_person.userid_sctid = ?
					OR mdl_enrol_shebang_person.userid_email = ?
					OR mdl_enrol_shebang_person.email = ?
		ORDER BY mdl_user.timecreated DESC
			 LIMIT 1
		";
		$args = array(
			$identifier,
			$identifier,
			$identifier,
			$identifier,
			$identifier,
			$identifier,
			$identifier,
			$identifier,
		);

		return ( \PSU::db('moodle2')->GetOne( $sql, $args ) ) ?: FALSE;
	}//end function
}//end class
