<?php

namespace PSU\SubmissionApproval;

class Submissions extends \PSU\Collection {
	public static $child = '\PSU\SubmissionApproval\Submission';

	public function get() {
		$sql = "
			SELECT *
				FROM gobtpac
			 WHERE gobtpac_external_user IN ('mtbatchelder', 'max', 'zbtirrell', 'djbramer', 'pdmanseau')
		";
		
		return \PSU::db('banner')->Execute( $sql );
	}//end get
}//end class \PSU\SubmissionApproval\Submissions
