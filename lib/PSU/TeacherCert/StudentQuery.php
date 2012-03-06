<?php

namespace PSU\TeacherCert;

use PSU\TeacherCert\Gate,
	PSU\TeacherCert\GateSystem;

class StudentQuery implements \IteratorAggregate {
	public $args = array();

	/**
	 * Query results.
	 * @type ADOdb_RecordSet
	 */
	private $results;

	public function __construct( $args = null ) {
		$this->parse_args( $args );
	}

	public function parse_args( $args ) {
		if( null === $args ) {
			return;
		}

		$defaults = array(
			'gatesystem' => null,
			'filter' => null,
		);

		$args = \PSU::params( $args, $defaults );

		if( empty($args) ) {
			$this->args = array();
			return;
		}

		if( $args['gatesystem'] ) {
			$gatesystem = GateSystem::get( $args['gatesystem'] );

			if( $gatesystem ) {
				$this->args['gatesystem'] = $gatesystem->id;
			}
		}

		if( $args['filter'] ) {
			$this->args['filter'] = $args['filter']; 
		}
	}

	public function query( $args = null ) {
		$this->parse_args( $args );

		$where_sql = array('1=1');

		if( $this->args['filter'] ) {
			$filter = strtoupper( $this->args['filter'] );
			$filter = \PSU::db('banner')->qstr("%{$filter}%");
			$where_sql[] = sprintf( '(spriden_search_first_name LIKE %1$s OR spriden_search_last_name LIKE %1$s)', $filter );
		}

		if( $gatesystem = $this->args['gatesystem'] ) {
			if( ! ( $gatesystem instanceof GateSystem ) ) {
				$gatesystem = GateSystem::get( $this->args['gatesystem'] );
			}

			if( $gatesystem ) {
				$where_sql[] = sprintf( 'sgs.gate_system_id = %d', $gatesystem->id );
			}
		}

		$where_sql = implode( ' AND ', $where_sql );

		$sql = "
			SELECT DISTINCT pi.pid pidm, pi.first_name, pi.last_name
			FROM
				psu_teacher_cert.student_gate_systems sgs LEFT JOIN
				psu_identity.person_identifiers pi ON sgs.pidm = pi.pid LEFT JOIN
				spriden ON pi.pid = spriden.spriden_pidm
			WHERE $where_sql
			ORDER BY pi.last_name, pi.first_name
		";

		$this->results = \PSU::db('banner')->Execute( $sql );
	}

	public function getIterator() {
		// No results?
		if( false == $this->results ) {
			return new \EmptyIterator;
		}

		return new StudentQuery\Iterator( $this->results );
	}
}
