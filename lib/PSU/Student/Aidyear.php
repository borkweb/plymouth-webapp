<?php

class PSU_Student_Aidyear extends BannerObject {
	public $pidm;
	public $aid_year;

	public function __construct( $pidm, $aid_year ) {
		$this->pidm = $pidm;
		$this->aid_year = $aid_year;
	}

	public function _load_attendancecost() {
		$this->data['attendancecost'] = new PSU_Student_Aidyear_AttendanceCost( $this->pidm, $this->aid_year );
		$this->data['attendancecost']->load();
	}
}
