<?php

namespace PSU\TeacherCert;

/**
 * 
 */
class ConstituentSchool extends ActiveRecord {
	static $table = 'constituent_schools';
	static $_name = 'Constituent School';

	public function constituent() {
		return $this->_get_related( __FUNCTION__, '\PSU\TeacherCert\Constituent::get', $this->constituent_id );
	}

	function _prep_args() {
		$args = array(
			'the_id' => $this->id,
			'constituent_id' => $this->constituent_id,
			'school_id' => $this->school_id,
			'position_id' => $this->position_id,
			'end_date' => $this->end_date ? \PSU::db('banner')->BindDate( $this->end_date ) : null,
		);
		return $args;
	}
}//end class ConstituentSchool
