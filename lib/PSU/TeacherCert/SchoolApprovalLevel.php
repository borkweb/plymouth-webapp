<?php

namespace PSU\TeacherCert;

class SchoolApprovalLevel extends ActiveRecord {
	static $table = 'school_approval_levels';
	static $_name = 'School Approval Level';

	/**
	 *
	 */
	public function schools() {
		$schools = new Schools;
		return $schools->get_by_school_approval_level( $this->id );
	}//end schools

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
}//end class SchoolType
