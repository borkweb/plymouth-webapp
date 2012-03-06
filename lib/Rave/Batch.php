<?php

/** 
 * Batch processing via the WebDAV connection
 *
 * this supports a lot of capability, but for now we have only
 * established the ability to get the full report of enrollments
 */

namespace Rave;

class Batch {
	private static $path = 'upload.ravewireless.com/plymouth/';
	
	/**
	 * get the full enrollment report from Rave
	 * @param which date do you want, defaults to today
	 */
	public static function getEnrollmentReport( $report_date = 'today' ) {
		$config = \PSU\Config\Factory::get_config();
		$username = $config->get( 'rave', 'webdav_user' );
		$password = $config->get( 'rave', 'webdav_passwd' );

		$report = 'enrollment_report_' . date('Ymd', strtotime( $report_date ) ) . '_plymouth.csv';
		
		$file = 'https://' . $username . ':' . $password . '@' . static::$path . $report;

		$accounts = array();
		$indexes = array();
		$i = -1; // negative one because the first row is the field names
		if( ( $handle = fopen( $file, 'rb' ) ) !== false ) {
			while( ( $data = fgetcsv( $handle ) ) !== false ) {
				if( $i == -1 ) {
					$indexes = $data;
				} // end if
				else {
					$accounts[$i] = array();
					foreach( $data as $k => $value ) {
						$accounts[ $i ][ $indexes[ $k ] ] = $value;
					} // end foreach
				} // end else
				$i++;
			} // end while
			fclose($handle);

			return $accounts;
		} // end if

		return false;
	} // end function getEnrollmentReport

	/**
	 * Get users with roles student_account_active, and employee, and update Rave
	 * removing users that are no longer in this state, and add new users.
	 */
	public static function syncActiveUsers() {
		
	}//end syncActiveUsers

} // end class
