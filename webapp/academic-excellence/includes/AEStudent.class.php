<?php

/**
 * Interface for working with student data specific to the AE app.
 * @ingroup acadexcel
 */
class AEStudent
{
	/**
	 * Create new record for this student in the database.
	 */
	public static function createStudent($pidm, $term)
	{
		list($address) = $GLOBALS['BannerStudent']->getAddress($pidm, 'MA');

		if(count($address) > 0)
		{
			$addr1 = $address['r_street_line1'];
			$addr2 = $address['r_street_line2'];
			$city = $address['r_city'];
			$state = $address['r_stat_code'];
			$zip = $address['r_zip'];
		}
		else
		{
			// no main address, use blanks
			$addr1 = '';
			$addr2 = '';
			$city = '';
			$state = '';
			$zip = '';
		}
		unset($address);

		$data = array(
			'pidm' => $pidm,
			'term' => $term,
			'confirmed' => -1,
			'confirmed_cert' => -1,
			'name' => $_SESSION['student']['full_name'],
			'name_first' => $_SESSION['student']['first_name'],
			'name_middle' => $_SESSION['student']['middle_name'],
			'name_last' => $_SESSION['student']['last_name'],
			'addr1' => $addr1,
			'addr1' => $addr1,
			'city' => $city,
			'state' => $state,
			'zip' => $zip
		);
		
		$t = 'academic_excellence';
		$sql = PSU::db('myplymouth')->GetInsertSQL($t, $data);
		$result = PSU::db('myplymouth')->Execute($sql);
		if($result === false)
		{
			return false;
		}

		return AEStudent::getStudentData($pidm, $term);
	}

	/**
	 * Saves the supplied student data into the database.
	 */
	public static function saveConfirmation($pidm, $term, $student)
	{
		$data = array(
			'confirmed' => $student['confirmed'],
			'ceremony_needs' => $student['ceremony_needs'],
			'confirmed_cert' => $student['confirmed_cert'],
			'guest_count' => $student['guest_count'],
			'name' => $student['name'],
			'addr1' => $student['addr1'],
			'addr2' => $student['addr2'],
			'city' => $student['city'],
			'state' => $student['state'],
			'zip' => $student['zip']
		);
		$where = sprintf('pidm = %d AND term = %d', $pidm, $term);
		return $GLOBALS['DBUTIL']->update(PSU::db('myplymouth'), 'academic_excellence', $data, $where, false);
	}

	/**
	 * Saves rejection data into the database.
	 */
	public static function saveRejection($pidm, $term)
	{
		$_SESSION['student']['confirmed'] = 0;
		$data = array('confirmed' => 0);
		$where = sprintf('pidm = %d AND term = %d', $pidm, $term);
		return $GLOBALS['DBUTIL']->update(PSU::db('myplymouth'), 'academic_excellence', $data, $where);
	}

	/**
	 * Removes confirmation data from the database.
	 */
	public static function removeConfirmation($pidm, $term)
	{
		$_SESSION['student']['confirmed'] = -1;
		$_SESSION['student']['confirmed_cert'] = -1;
		$data = array('confirmed' => -1, 'confirmed_cert' => -1);
		$where = sprintf('pidm = %d AND term = %d', $pidm, $term);
		return $GLOBALS['DBUTIL']->update(PSU::db('myplymouth'), 'academic_excellence', $data, $where);
	}

	/**
	 * Gets existing student data from the database, creating record if necessary.
	 */
	public static function getStudentData($pidm, $term)
	{
		$pidm = (int)$pidm;
		$term = (int)$term;

		$sql = sprintf('SELECT * FROM academic_excellence WHERE pidm = %d AND term = %d',
			$pidm, $term);

		$student = PSU::db('myplymouth')->GetRow($sql);

		if(count($student) == 0)
		{
			return AEStudent::createStudent($pidm, $term);
		}

		return $student;
	}
}
