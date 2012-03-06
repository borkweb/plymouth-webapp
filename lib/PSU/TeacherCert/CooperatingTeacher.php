<?php

namespace PSU\TeacherCert;

/**
 * Constituents attached to schools.
 */
class CooperatingTeacher extends ActiveRecord {
	static $table = 'constituent_schools';

	/**
	 *
	 */
	public function constituent() {
		return $this->_get_related( __FUNCTION__, '\\PSU\\TeacherCert\\Constituent::get', $this->constituent_id );
	}//end constituent

	public function constituent_name() {
		$constituent = $this->constituent();
		return "{$constituent->last_name}, {$constituent->first_name}";
	}

	/**
	 *
	 */
	public function position() {
		return $this->_get_related( __FUNCTION__, '\\PSU\\TeacherCert\\Position::get', $this->position_id );
	}//end position

	/**
	 *
	 */
	public function school() {
		return $this->_get_related( __FUNCTION__, '\\PSU\\TeacherCert\\School::get', $this->school_id );
	}//end constituent

	protected function _prep_args() {
		$args = array(
			'the_id' => $this->id ?: 0,
			'constituent_id' => $this->constituent_id,
			'school_id' => $this->school_id,
			'position_id' => $this->position_id,
			'end_date' => $this->end_date,
		);

		return $args;
	}//end _prep_args
}//end class CooperatingTeacher
