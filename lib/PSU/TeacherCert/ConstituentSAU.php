<?php

namespace PSU\TeacherCert;

/**
 * 
 */
class ConstituentSAU extends ActiveRecord {
	static $table = 'constituent_saus';
	static $_name = 'Constituent SAU';

	/**
	 *
	 */
	public function constituent() {
		return $this->_get_related( __FUNCTION__, '\\PSU\\TeacherCert\\Constituent::get', $this->constituent_id );
	}//end constituent

	/**
	 *
	 */
	public function position() {
		return $this->_get_related( __FUNCTION__, '\\PSU\\TeacherCert\\Position::get', $this->position_id );
	}//end position

	/**
	 *
	 */
	public function sau() {
		return $this->_get_related( __FUNCTION__, '\\PSU\\TeacherCert\\SAU::get', $this->sau_id );
	}//end sau

	function _prep_args() {
		$args = array(
			'the_id' => $this->id,
			'constituent_id' => $this->constituent_id,
			'sau_id' => $this->sau_id,
			'position_id' => $this->position_id,
			'end_date' => $this->end_date ? \PSU::db('banner')->BindDate( $this->end_date ) : null,
		);
		return $args;
	}
}//end class ConstituentSAU
