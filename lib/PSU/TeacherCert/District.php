<?php

namespace PSU\TeacherCert;

class District extends ActiveRecord {
	static $table = 'districts';
	static $_name = 'District';

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

	public function delete( $delete_id = null ) {
		parent::delete( $delete_id );

		$this->schools()->delete( $this->delete_id );
	}

	public function schools() {
		return $this->_get_related( __FUNCTION__, '\\PSU\\TeacherCert\\Schools', array( 'district_id' => $this->id ) );
	}
}//end class District
