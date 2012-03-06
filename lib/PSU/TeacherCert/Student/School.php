<?php

namespace PSU\TeacherCert\Student;

use PSU\TeacherCert\School as TeacherCertSchool,
	PSU\TeacherCert\ActiveRecord,
	PSU\TeacherCert;

class School extends ActiveRecord {
	static $table = 'student_schools';

	/**
	 * Return the parent school.
	 */
	public function parent() {
		return $this->_get_related( __FUNCTION__, '\PSU\TeacherCert\School', $this->school_id );
	}//end school

	/**
	 *
	 */
	public function cooperating_teachers() {
		// First, find all cooperating teachers for this school
		$teachers = $this->parent()->cooperating_teachers();

		// Pull out all their constituent school ids
		$ids = array();
		foreach( $teachers as $teacher ) {
			$ids[] = $teacher->id;
		}

		if( 0 === count($ids) ) {
			return new \PSU\EmptyIterator;
		}

		// return all those cooperating teachers in this gate system
		return $this->_get_related( __FUNCTION__, '\PSU\TeacherCert\Student\CooperatingTeachers', array( 'constituent_school_id' => $ids, 'student_gate_system_id' => $this->student_gate_system_id ) );
	}//end faculty

	public function _prep_args() {
		$args = array(
			'the_id' => $this->id ?: 0,
			'student_gate_system_id' => $this->student_gate_system_id,
			'school_id' => $this->school_id,
			'grade' => $this->grade,
			'interview_ind' => $this->interview_ind,
			'placement' => $this->placement,
			'notes' => $this->notes,
		);

		return $args;
	}//end _prep_args
}//end School
