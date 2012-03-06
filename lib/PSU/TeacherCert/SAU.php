<?php

namespace PSU\TeacherCert;

class SAU extends ActiveRecord {
	static $table = 'saus';
	static $_name = 'SAU';

	/**
	 *
	 */
	public function constituents() {
		return $this->_get_related( __FUNCTION__, '\\PSU\\TeacherCert\\ConstituentSAUs', $this->id );
	}//end constituents

	/**
	 *
	 */
	public function schools() {
		return $this->_get_related( __FUNCTION__, '\\PSU\\TeacherCert\\Schools', array( 'sau_id' => $this->id ) );
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
}//end class SAU
