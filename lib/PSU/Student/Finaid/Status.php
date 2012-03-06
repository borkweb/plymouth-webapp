<?php

class PSU_Student_Finaid_Status extends PSU_DataObject {
	public $aliases = array(
		'rtvmesg_mesg_desc' => 'message',
		'rtvmesg_code' => 'code',
	);

	public function budget_message_clean() {
		$message = $this->message;
		$message = trim( $message, '- ' );
		return $message;
	}

	public static function fetch( $pidm, $aid_year ) {
		$statuses = array();

		$rset = self::get_status( $pidm, $aid_year );

		foreach( $rset as $row ) {
			$status = new self;
			$status->populate( $row );
			$statuses[] = $status;
		}

		return $statuses;
	}

	public static function get_status( $pidm, $aid_year ) {
		$args = array(
			'pidm' => $pidm,
			'aidy' => $aid_year,
		);

		$sql = "
			SELECT rtvmesg_code, rtvmesg_mesg_desc
			FROM
				rorstat LEFT JOIN
				rbrgmsg ON rbrgmsg_aidy_code = rorstat_aidy_code AND rbrgmsg_bgrp_code = rorstat_bgrp_code LEFT JOIN
				rtvmesg ON rtvmesg_code = rbrgmsg_mesg_code AND rtvmesg_info_access_ind = 'Y'
			WHERE
				rorstat_aidy_code = :aidy AND
				rorstat_pidm = :pidm
		";

		$rset = PSU::db('banner')->Execute( $sql, $args );

		if( PSU::db('banner')->ErrorNo() > 0 ) {
			trigger_error( sprintf( "%s: %s", __FILE__, PSU::db('banner')->ErrorMsg() ), E_USER_WARNING );
		}

		return $rset;
	}//end get_status
}//end class PSU_Student_Finaid_Status
