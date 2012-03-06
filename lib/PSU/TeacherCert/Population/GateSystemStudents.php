<?php

namespace PSU\TeacherCert\Population;

use PSU\TeacherCert;

class GateSystemStudents extends \PSU_Population_Query {
	public $gate_system_id;

	public function __construct( $gate_system_id ) {
		$this->gate_system_id = $gate_system_id;
	}//end construct

	public function query ( $args = array() ) {
		$args = \PSU::params( $args, $defaults );

		$where_sql = array('1=1');

		$where_sql[] = 'gate_system_id = :gate_system_id';

		if( $args['filter'] ) {
			$filter = "%{$args[filter]}%";
			$filter = strtoupper( \PSU::db('banner')->qstr($filter) );
			$name_sql  = "(spriden_search_last_name  LIKE {$filter} OR ";
			$name_sql .= "spriden_search_first_name LIKE {$filter})";
			$where_sql[] = $name_sql;

			unset($args['filter']);
		}

		$where_sql = implode( ' AND ', $where_sql );

		if( ! key_exists( 'gate_system_id', $args ) ) {
			$args['gate_system_id'] = $this->gate_system_id;
		}//end if

		$sql = "
			SELECT DISTINCT pi.pid pidm, pi.first_name, pi.last_name, sgc.*
			FROM
				psu_teacher_cert.v_student_gate_systems sgc
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
