<?php

namespace PSU\TeacherCert\Student;

use PSU\TeacherCert;

class GateSystem extends TeacherCert\ActiveRecord {
	static $table = 'student_gate_systems';

	/**
	 * True if the student gate system is active.
	 */
	public function active() {
		$active = empty( $this->complete_date ) && empty( $this->exit_date );
		return $active;
	}//end active

	/**
	 * apply date timestamp
	 */
	public function apply_date_timestamp() {
		return $this->date_timestamp( 'apply_date' );
	}//end apply_date_timestamp

	/**
	 * approve date timestamp
	 */
	public function approve_date_timestamp() {
		return $this->date_timestamp( 'approve_date' );
	}//end approve_date_timestamp

	/**
	 *
	 */
	public function clinical_faculty() {
		return $this->_get_related( __FUNCTION__, '\PSU\TeacherCert\Student\ClinicalFacultys', $this->id );
	}//end clinical_faculty

	/**
	 * complete date timestamp
	 */
	public function complete_date_timestamp() {
		return $this->date_timestamp( 'complete_date' );
	}//end complete_date_timestamp

	/**
	 * exit date timestamp
	 */
	public function exit_date_timestamp() {
		return $this->date_timestamp( 'exit_date' );
	}//end exit_date_timestamp

	/**
	 * retrieve core gate_system info
	 */
	public function gate_system() {
		return $this->_get_related( __FUNCTION__, '\PSU\TeacherCert\GateSystem', $this->gate_system_id );
	}//end gate

	/**
	 * return the gatesystem's gates
	 */
	public function gates() {
		return $this->_get_related( __FUNCTION__, '\PSU\TeacherCert\Student\Gates', array( 'v_student_gates.student_gate_system_id' => $this->id ) );
	}//end gates

	/**
	 *
	 */
	public function schools() {
		return $this->_get_related( __FUNCTION__, '\PSU\TeacherCert\Student\Schools', $this->id );
	}//end schools

	/**
	 * Return the student attached to this gate system.
	 * @return PSU\TeacherCert\Student
	 */
	public function student() {
		return $this->_get_related( __FUNCTION__, '\PSU\TeacherCert\Student', $this->pidm );
	}//end student

	/**
	 * prepares arguments for DML
	 */
	protected function _prep_args() {
		// this is the data prepared for binding.
		// these fields are ordered as they are in the table
		$args = array(
			'the_id' => $this->id,
			'pidm' => $this->pidm,
			'gate_system_id' => $this->gate_system_id,
			'teaching_term_code' => $this->teaching_term_code,
			'apply_date' => $this->apply_date ? \PSU::db('banner')->BindDate( $this->apply_date_timestamp() ) : null,
			'approve_date' => $this->approve_date ? \PSU::db('banner')->BindDate( $this->approve_date_timestamp() ) : null,
			'complete_date' => $this->complete_date ? \PSU::db('banner')->BindDate( $this->complete_date_timestamp() ) : null,
			'exit_date' => $this->exit_date ? \PSU::db('banner')->BindDate( $this->exit_date_timestamp() ) : null,
		);

		return $args;
	}//end _prep_args
}//end class \PSU\TeacherCert\Student\GateSystem
