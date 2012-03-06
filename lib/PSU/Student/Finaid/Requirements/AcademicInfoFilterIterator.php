<?php

/**
 * A filter to exclude academic information requirements. These codes
 * expose information about academic issues, such as a student being
 * under academic probation.
 */
class PSU_Student_Finaid_Requirements_AcademicInfoFilterIterator extends PSU_FilterIterator {
	public $invalid_codes = array(
		'150%',
		'150%AP',
		'150APR',
		'150DEN',
		'FILEFR',
		'SAP',
		'SAPAP',
		'SAPAPR',
		'SAPDEN',
		'SAPDNY',
		'SAPLTR',
		'SAPMYP',
		'SEVER',
		'WDRAWN',
		'WDRWL',
	);

	public function accept() {
		$requirement = $this->current();
		$is_invalid = in_array( $requirement->rtvtreq_code, $this->invalid_codes );

		return $is_invalid ? false : true;
	}
}
