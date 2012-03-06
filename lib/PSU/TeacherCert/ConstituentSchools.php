<?php

namespace PSU\TeacherCert;

/**
 * 
 */
class ConstituentSchools extends \PSU\TeacherCert\Collection {
	static $_name = 'Constituent Schools';
	static $table = 'constituent_schools';
	static $child = '\\PSU\\TeacherCert\\ConstituentSchool';
	static $join = array(
		array(
			'type' => 'JOIN',
			'table' => 'psu_teacher_cert.constituents',
			'fields' => array(
				array(
					'logic' => 'AND',
					'field1' => 'constituents.id',
					'field2' => 'constituent_schools.constituent_id',
				)
			),
		),
	);

	protected function _get_order() {
		return 'constituents.last_name, constituents.first_name, constituents.mi';
	}
	
}//end class ConstituentSchools
