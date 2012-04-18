<?php

class FinaidAPI {
	/**
	 * Accepts an existing parent, plus fields to check. Returns true if there is a match.
	 *
	 * @param $parent PSU_Student_Finaid_Application_Parent
	 * @param $last4 string last 4 of parent ssn
	 * @param $birthdate string birthdate in YYYY-MM-DD (iso 8601) format
	 */
	public static function checkParent( $parent, $last4, $birthdate ) {
		if( substr($parent->ssn, -4) != $last4 ) {
			return false;
		}

		if( $parent->birthdate_iso8601() != $birthdate ) {
			return false;
		}

		return true;
	}//end checkParent

	/**
	 * Convert a year, month and day from the input form into YYYY-MM-DD style.
	 */
	public static function fields2birthdate( $year, $month, $day ) {
		$year = (int)$year;
		$month = (int)$month;
		$day = (int)$day;

		return sprintf("%04d-%02d-%02d", $year, $month, $day);
	}//end fields2birthdate

	/**
	 * Return the authorization record id and sequence number that this grantee is
	 * authorized to view. Returns false if user is not authorized.
	 */
	public static function getAuthorization( $student_wpid, $grantee_wpid, $aid_year ) {
		$args = compact( 'student_wpid', 'grantee_wpid', 'aid_year' );
		$sql = "
			SELECT id, form_seq_no
			FROM grantee_finaid_verification
			WHERE student_wpid = :student_wpid AND grantee_wpid = :grantee_wpid AND aid_year = :aid_year
		";

		$row = PSU::db('banner')->GetRow( $sql, $args );

		if( $row ) {
			return (object)array( 'id' => $row['id'], 'seqno' => $row['form_seq_no'] );
		}

		return false;
	}//end getAuthorization

	/**
	 * initialize the person object and term_code
	 */
	public static function initPerson( &$identifier, &$target, &$user )
	{
		$params = new FinaidParams;

		/*********** [ BEGIN Person Initialization ] ***************/
		$identifier = $identifier ? $identifier : $_SESSION['pidm'];

		try{
			$target = new PSUPerson($identifier);
		} catch(Exception $e){
			$identifier = $_SESSION['pidm'];
			$target = new PSUPerson($identifier);
			$params['id'] = $target->login_name;
			$_SESSION['errors'][] = 'You entered an invalid user.  The following financial aid information on this page is from <strong>your</strong> account.';
		}//end catch

		// admins don't need any of the following auth checks
		if( $params['admin'] ) {
			return;
		}

		// for non-admins, force the user to look at himself if he doesn't have
		// permission to view the target
		if( $target !== $user ) {
			if( !($user->myrelationships->{$target->wpid} && $user->myrelationships->{$target->wpid}->grants('finaid')) ){
				$target = $user;
				$params['id'] = $target->login_name;
			}//end if
		}

		/*********** [ END Person Initialization ] ***************/
	}//end initPerson

	/**
	 * Returns true if the current user can view the target's financial information.
	 *
	 * @param $student PSUPerson the student we are looking at
	 * @param $grantee PSUPerson the user trying to view the data
	 * @param $aid_year the aid year we are looking at
	 *
	 * @return bool
	 */
	public static function isAuthorized( $target, $user, $aid_year ) {
		$result = self::getAuthorization( $target->wpid, $user->wpid, $aid_year );

		if( ! $result ) {
			return false;
		}

		//
		// There was a previous authorization between these two users, for this
		// aid year.
		//

		$application = $target->student->finaid->application;

		// current application 
		if( $application->seqno == $result->seqno ) {
			return true;
		}

		//
		// The authorization was for a different application sequence number.
		//

		$old_application = new PSU_Student_Finaid_Application( $target->pidm, $aid_year, $result->seqno );
		$old_application->load();

		if( $application->parents_match( $old_application ) ) {
			// update auth with new seqno
			self::updateAuthorization( $result->id, $application->seqno );
			return true;
		}

		return false;
	}//end isAuthorized

	public static function logAuthorization( $student_wpid, $grantee_wpid, $form_seq_no, $aid_year ) {
		$args = compact( 'student_wpid', 'grantee_wpid', 'form_seq_no', 'aid_year' );

		if( $auth = self::getAuthorization( $student_wpid, $grantee_wpid, $aid_year ) ) {
			return self::updateAuthorization( $auth->id, $form_seq_no );
		}

		$sql = "
			INSERT INTO grantee_finaid_verification (student_wpid, grantee_wpid, aid_year, form_seq_no, verified_on)
			VALUES (:student_wpid, :grantee_wpid, :aid_year, :form_seq_no, SYSDATE)
		";

		PSU::db('banner')->Execute( $sql, $args );

		return true;
	}//end logAuthorization

	public static function updateAuthorization( $id, $form_seq_no ) {
		$args = compact( 'form_seq_no', 'id' );

		$sql = "
			UPDATE
				grantee_finaid_verification
			SET
				form_seq_no = :form_seq_no
			WHERE
				id = :id
		";

		PSU::db('banner')->Execute( $sql, $args );

		return true;
	}//end updateAuthorization

	/**
	 *
	 * @param $student PSUPerson the student attached to the application for financial aid
	 * @param $parent_ssn_last4 string the last four digits of the parent's ssn
	 * @param $parent_birthdate the parent's birthdate, in the format YYYY-MM-DD
	 *
	 * @return mixed matched Application object, or false if there was no match
	 */
	public static function verifyIdentity( $target, $parent_ssn_last4, $parent_birthdate ) {
		$application = $target->student->finaid->application;

		$result = self::checkParent( $application->father, $parent_ssn_last4, $parent_birthdate ) ||
			self::checkParent( $application->mother, $parent_ssn_last4, $parent_birthdate );

		return $result ? $application : false;
	}//end verifyIdentity

}//end class FinaidAPI
