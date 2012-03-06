<?php

namespace PSU\TeacherCert\Student;

/**
 * 
 */
class CooperatingTeacher extends \PSU\TeacherCert\ActiveRecord {
	static $table = 'student_school_constit';

	/**
	 * Add a voucher to the cooperating teacher.
	 */
	public function add_voucher() {
		$this->voucher = 1;
		$this->voucher_date = time();
	}//end add_voucher

	/**
	 *
	 */
	public function constituent_school() {
		return $this->_get_related( __FUNCTION__, '\\PSU\\TeacherCert\\ConstituentSchool::get', $this->constituent_school_id );
	}//end constituent_school

	/**
	 *
	 */
	public function constituent() {
		if( $obj = $this->constituent_school() ) {
			return $this->_get_related( __FUNCTION__, '\\PSU\\TeacherCert\\Constituent::get', $obj->constituent_id );
		}
	}//end constituent_school

	/**
	 * Remove this teacher's voucher.
	 */
	public function remove_voucher() {
		$this->voucher = null;
		$this->voucher_date = null;
	}//end remove_voucher

	public function _prep_args() {
		$args = array(
			'the_id' => $this->id ?: 0,
			'student_gate_system_id' => $this->student_gate_system_id,
			'constituent_school_id' => $this->constituent_school_id,
			'association_attribute' => $this->association_attribute,
			'voucher' => $this->voucher,
			'voucher_date' => $this->voucher_date ? \PSU::db('banner')->BindDate($this->voucher_date) : null,
		);

		return $args;
	}
}//end class CooperatingTeacher
