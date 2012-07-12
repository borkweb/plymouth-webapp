<?php
/**
 * API for ELS application.
 */
class ELS extends BannerObject {
	static $student_sortby = 'last_name';

	/**
	 * Get all accounts with the els_student role.
	 */
	public static function getStudents() {
		$idm = PSU::get('idmobject');

		$search = array(
			array('pa.attribute' => 'els_student'),
			array('pa.type_id' => '2')
		);

		$return = 'i.pid,i.psu_id,i.username,i.first_name,i.last_name,l.start_date,l.end_date';

		$students = $idm->getUsersByAttribute( $search, 'AND', $return );

		array_walk( $students, array('ELS', 'dates2timestamp') );
		array_walk( $students, array('ELS', 'load_psuperson') );
		
		usort( $students, array('ELS', 'student_sort') );

		return $students;
	}//end getStudents

	/**
	 * Return data about the uploaded roster file.
	 */
	public static function get_roster_file() {
		$dh = opendir( $GLOBALS['UPLOAD_DIR'] );

		$roster_file_path = null;

		while( ( $name = readdir($dh) ) !== false ) {
			$path = $GLOBALS['UPLOAD_DIR'] . '/' . $name;
			if( is_file( $path ) ) {
				$roster_file_path = $path;
				break;
			}
		}

		if( ! $roster_file_path ) {
			return false;
		}

		$info = array(
			'name' => $name, // still set from loop
			'size' => filesize($roster_file_path)
		);

		$info['uploader'] = new PSUPerson( PSUMeta::get($GLOBALS['META_WEBAPP'], 'roster_uploader')->value );
		$info['uploaded'] = PSUMeta::get($GLOBALS['META_WEBAPP'], 'roster_uploaded')->value;

		return $info;
	}//end get_roster_file

	/**
	 * Convert human-readable dates to unix timestamps.
	 */
	public static function dates2timestamp( &$v, $k ) {
		$v['start_date'] = strtotime($v['start_date']);
		$v['end_date'] = strtotime($v['end_date']);
	}//end dates2timestamp

	/**
	 * Load in PSUPerson data for the list.
	 */
	public static function load_psuperson( &$v, $k ) {
		$p = new PSUPerson( $v['pid'] );

		$v['certification_number'] = $p->certification_number;
		$v['idcard'] = $p->idcard();
		$v['account_creation_date'] = $p->account_creation_date;
		
		unset($p);
	}//end load_psuperson

	/**
	 * Sort student array by a user-defined column.
	 */
	public static function student_sort( $a, $b ) {
		return strcmp($a[self::$student_sortby], $b[self::$student_sortby]);
	}//end student_sort
}//end ELS
