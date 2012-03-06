<?php

namespace PSU\TeacherCert;

class ConstituentSAUs extends Collection {
	static $_name = 'Constituent SAUs';
	static $child = 'PSU\\TeacherCert\\ConstituentSAU';
	static $parent_key = 'sau_id';
	static $table = 'constituent_saus';
	static $join = array(
		array(
			'type' => 'JOIN',
			'table' => 'psu_teacher_cert.constituents',
			'fields' => array(
				array( 
					'logic' => 'AND',
					'field1' => 'constituents.id',
					'field2' => 'constituent_saus.constituent_id',
				),
			),
		),
		array(
			'type' => 'JOIN',
			'table' => 'psu_teacher_cert.saus',
			'fields' => array(
				array( 
					'logic' => 'AND',
					'field1' => 'saus.id',
					'field2' => 'constituent_saus.sau_id',
				),
			),
		),
	);

	protected function _get_order() {
		return 'name';
	}
}//end class PSU\TeacherCert\ConstituentSAUs
