<?php

namespace PSU\TeacherCert\Student;

use PSU\TeacherCert\Student\GateSystem as StudentGateSystem,
	PSU\TeacherCert\ActiveRecord;

class Gate extends ActiveRecord {
	static $table = 'student_gates';

	/**
	 * load a Gate's checklist items
	 */
	public function checklist_items() {
		return $this->_get_related( __FUNCTION__, 'PSU\\TeacherCert\\Student\\ChecklistItems', array( 'v_student_checklist_answers.student_gate_system_id' => $this->student_gate_system_id, 'v_student_checklist_answers.gate_id' => $this->gate_id ) );
	}//end checklist_items

	/**
	 * complete date timestamp
	 */
	public function complete_date_timestamp() {
		return $this->date_timestamp( 'complete_date' );
	}//end complete_date_timestamp

	/**
	 * retrieve core gate info
	 */
	public function gate() {
		return $this->_get_related( __FUNCTION__, 'PSU\\TeacherCert\\Gate', $this->gate_id );
	}//end gate

	public function is_complete( $observe_date = TRUE ) {
		if( $observe_date && $this->complete_date_timestamp() ) {
			return TRUE;
		}//end if

		foreach( $this->checklist_items() as $item ) {
			if( ! $item->checklist_item_answer() ) {
				return FALSE;
			}//end if
			
			if( ! $item->checklist_item_answer()->complete() ) {
				return FALSE;
			}//end if
		}//end foreach

		return TRUE;
	}//end is_complete

	/**
	 * Accept answers for child checklist items.
	 * @param array $answers Key/value pairs where the key is the checklist item id (global, not student) and value is the answer ID, or date value (if applicable for that checklist item)
	 */
	public function set_answers( $answers ) {
		foreach( $this->checklist_items() as $checklist_item ) {
			$id = $checklist_item->checklist_item_id();

			if( ! isset( $answers[$id] ) ) {
				// TODO: exception, or something?
				continue;
			}

			$checklist_item->answer( $answers[$id] );
		}

		if( ! $this->complete_date_timestamp() && $this->is_complete() ) {
			$this->complete_date = time();
			$this->save();
		} elseif( $this->complete_date_timestamp() && ! $this->is_complete( FALSE ) ) {
			$this->complete_date = null;
			$this->save();
		}//end if
	}//end set_answers

	/**
	 *
	 */
	public function student_gate_system() {
		return $this->_get_related( __FUNCTION__, '\\PSU\\TeacherCert\\Student\\GateSystem', $this->student_gate_system_id );
	}//end student_gate_system

	/**
	 * prepares arguments for DML
	 */
	protected function _prep_args() {
		// this is the data prepared for binding.
		// these fields are ordered as they are in the table
		$args = array(
			'the_id' => $this->id,
			'student_gate_system_id' => $this->student_gate_system_id,
			'gate_id' => $this->gate_id,
			'complete_date' => $this->complete_date ? \PSU::db('banner')->BindDate( $this->complete_date_timestamp() ): null,
		);

		return $args;
	}//end _prep_args

	/**
	 * magic
	 */
	public function __get( $var ) {
		if( isset( $this->gate()->$var ) ) {
			$this->failover[ $var ] = TRUE;
			return $this->gate()->$var;
		}//end if
	}//end __get
}//end class \PSU\TeacherCert\Student\Gate
