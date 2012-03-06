<?php

namespace PSU\TeacherCert;

class School extends ActiveRecord {
	static $table = 'schools';
	static $_name = 'School';

	/**
	 * returns the school's approval level
	 *
	 * @param $what \b what to return. name/id
	 */
	public function approval_level( $what = 'name' ) {
		return $this->validation( 'school_approval_levels', 'school_approval_level_id', $what );
	}//end approval_level

	/**
	 *
	 */
	public function constituent_schools() {
		return $this->_get_related( __FUNCTION__, '\\PSU\\TeacherCert\\ConstituentSchools', array( 'school_id' => $this->id ) );
	}//end constituents

	/**
	 *
	 */
	public function cooperating_teachers() {
		return $this->_get_related( __FUNCTION__, '\\PSU\\TeacherCert\\CooperatingTeachers', $this->id );
	}//end cooperating_teachers

	/**
	 * Cascade deletion to children.
	 */
	public function delete( $delete_id = null ) {
		parent::delete( $delete_id );

		$this->constituent_schools()->delete( $this->delete_id );
	}

	/**
	 * get district object
	 */
	public function district() {
		if( null == $this->district_id ) {
			return false;
		}

		return $this->_get_related( __FUNCTION__, '\\PSU\\TeacherCert\\District::get', $this->district_id );
	}//end district

	/**
	 * get sau object
	 */
	public function sau() {
		if( null == $this->sau_id ) {
			return false;
		}

		return $this->_get_related( __FUNCTION__, '\\PSU\\TeacherCert\\SAU::get', $this->sau_id );
	}//end sau

	/**
	 * returns the school's type
	 *
	 * @param $what \b what to return. name/id
	 */
	public function school_type() {
		if( null == $this->school_type_id ) {
			return false;
		}

		return $this->_get_related( __FUNCTION__, '\\PSU\\TeacherCert\\SchoolType::get', $this->school_type_id );
	}//end school_type

	/**
	 * prepares arguments for DML
	 */
	protected function _prep_args() {
		// this is the data prepared for binding.
		// these fields are ordered as they are in the table
		$args = array(
			'the_id' => $this->id,
			'sau_id' => $this->sau_id,
			'district_id' => $this->district_id,
			'school_type_id' => $this->school_type_id,
			'school_approval_level_id' => $this->school_approval_level_id,
			'name' => $this->name,
			'slug' => $this->slug ?: \PSU::createSlug( $this->name ),
			'grade_span' => $this->grade_span,
			'enrollment' => $this->enrollment,
			'street_line1' => $this->street_line1,
			'street_line2' => $this->street_line2,
			'city' => $this->city,
			'state' => $this->state,
			'zip' => $this->zip,
			'phone' => $this->phone,
			'fax' => $this->fax,
			'legacy_code' => $this->legacy_code,
		);

		return $args;
	}//end _prep_args
}//end class School
