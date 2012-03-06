<?php

namespace PSU\TeacherCert;

class Position extends ActiveRecord {
	static $table = 'constituent_positions';
	static $_name = 'Position';

	/**
	 * prepares arguments for DML
	 */
	protected function _prep_args() {
		// this is the data prepared for binding.
		// these fields are ordered as they are in the table
		$args = array(
			'the_id' => $this->id,
			'name' => $this->name,
			'slug' => $this->slug ?: \PSU::createSlug( $this->name ),
		);

		return $args;
	}//end _prep_args
}//end class Position
