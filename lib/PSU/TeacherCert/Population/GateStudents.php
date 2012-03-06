<?php

namespace PSU\TeacherCert\Population;

use PSU\TeacherCert;

class GateStudents extends \PSU_Population_Query {
	public $gate_id;

	public function __construct( $gate_id ) {
		$this->gate_id = $gate_id;
	}//end construct

	public function query( $args = array() ) {
		$args = \PSU::params( $args, $defaults );

		$where_sql = array('1=1');

		$where_sql[] = 'gate_id = :gate_id';

		$where_sql = implode( ' AND ', $where_sql );

		if( ! key_exists( 'gate_id', $args ) ) {
			$args['gate_id'] = $this->gate_id;
		}//end if

		$sql = "
			SELECT DISTINCT pi.pid pidm, pi.first_name, pi.last_name, sgc.*
			FROM
				psu_teacher_cert.v_student_gate_systems sgc
				JOIN gorirol g
					ON g.gorirol_pidm = sgc.pidm
				 AND g.gorirol_role_group = 'INTCOMP'
				 AND g.gorirol_role = 'STUDENT_ACCOUNT_ACTIVE'
				JOIN psu_identity.person_identifiers pi 
					ON pi.pid = sgc.pidm
				JOIN spriden 
					ON spriden.spriden_pidm = pi.pid
			WHERE $where_sql
			ORDER BY upper(pi.last_name), upper(pi.first_name)
		";

		return \PSU::db('banner')->GetAll( $sql, $args );
	}
}//end PSU\TeacherCert\Population\GateStudents
