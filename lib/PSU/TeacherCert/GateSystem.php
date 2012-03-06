<?php

namespace PSU\TeacherCert;

class GateSystem extends ActiveRecord {
	static $table = 'gate_systems';
	static $_name = 'Gate System';

	/**
	 * return the gatesystem's gates
	 */
	public function gates() {
		return $this->_get_related( __FUNCTION__, '\PSU\TeacherCert\Gates', $this->id );
	}//end gates

	/**
	 * prepares arguments for DML
	 */
	protected function _prep_args() {
		// this is the data prepared for binding.
		// these fields are ordered as they are in the table
		$args = array(
			'the_id' => $this->id,
			'name' => $this->name,
			'level_code' => $this->level_code,
			'slug' => $this->slug ?: \PSU::createSlug( $this->name ),
		);

		return $args;
	}//end _prep_args
}//end class GateSystem
