<?php

namespace PSU\TeacherCert;

class Student extends \PSU_DataObject {
	static $_name = 'Student';

	/**
	 * person identifier
	 */
	public $pidm;

	private $gate_systems;

	/**
	 * constructor
	 */
	public function __construct( $pidm ) {
		if( ! is_array( $pidm ) ) {
			$pidm = array( 'pidm' => $pidm );
		}//end else

		parent::__construct( $pidm );
	}//end constructor

	/**
	 * returns the gate systems the user is a member of
	 * @param     string|int|GateSystem     $gate_system       A specific gate system to return
	 */
	public function gate_systems( $gate_system = null ) {
		if( ! $this->gate_systems ) {
			$this->gate_systems = new Student\GateSystems( $this->pidm );
		}//end if

		// Caller requested a specific gate system
		if( $gate_system ) {
			// Force to GateSystem object
			if( ! is_object( $gate_system ) ) {
				$gate_system = GateSystem::get( $gate_system );
			}

			foreach( $this->gate_systems as $sgs ) {
				if( $gate_system == $sgs->gate_system() ) {
					return $sgs;
				}
			}

			return null;
		}

		return $this->gate_systems;
	}//end gate_systems

	/**
	 * returns the gates associated with the user
	 */
	public function gates( $gate_system_id ) {
		if( ! $this->gate_systems ) {
			$this->gate_systems();
		}//end if

		if( $this->gate_systems[ $gate_system_id ] ) {
			return $this->gate_systems[ $gate_system_id ]->gates();
		}//end if

		return null;
	}//end gates

	public static function get( $id ) {
		return new self( $id );
	}//end get

	public static function get_by_student_gate_system( $student_gate_system_id ) {
		$sql = "
			SELECT pidm
				FROM psu_teacher_cert.student_gate_systems
			 WHERE id = :student_gate_system_id
		";

		if( $pidm = \PSU::db('banner')->GetOne( $sql, array( 'student_gate_system_id' => $student_gate_system_id ) ) ) {
			return new self( $pidm );
		}//end if

		return null;
	}//end get_by_gate_system

	/**
	 * gets the student's person object
	 */
	public function person() {
		return \PSUPerson::get( $this->pidm );
	}//end person
}//end class PSU\TeacherCert\Student
