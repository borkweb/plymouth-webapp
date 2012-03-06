<?php

namespace PSU\TeacherCert\Template;

use PSU\TeacherCert;

/**
 * Return the URL for an object.
 */
class Resolver {
	protected $config;

	public function __construct( $config ) {
		$this->config = $config;
	}//end __construct

	public function __invoke( $obj, $action = 'edit' ) {
		return $this->resolve( $obj, $action );
	}

	public function resolve( $obj, $action = 'edit' ) {
		$base_url = $this->config->get( 'teacher-cert', 'base_url' );

		switch( get_class( $obj ) ) {
			case 'PSU\TeacherCert\GateSystem':
				return sprintf( '%s/gate-system/%s', $base_url, $obj->slug );
			case 'PSU\TeacherCert\Student\GateSystem':
				return sprintf( '%s/gate-system/%s/%d', $base_url, $obj->gate_system()->slug, $obj->id );
			case 'PSU\TeacherCert\Student\School':
				return sprintf( '%s/student-school/%d/%s/%d', $base_url, $obj->student_gate_system_id, $action, $obj->id );
		}
	}
}//end class ClassName
