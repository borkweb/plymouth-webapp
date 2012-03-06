<?php

namespace PSU\TeacherCert;

class Gate extends ActiveRecord {
	static $table = 'gates';
	static $_name = 'Gate';

	protected $checklist_items = null;

	/**
	 * load a Gate's checklist items
	 */
	public function checklist_items() {
		return $this->_get_related( __FUNCTION__, 'PSU\TeacherCert\ChecklistItems', $this->id );
	}//end checklist_items

	/**
	 * return the gate's students
	 */
	public function students() {
		$query = new Population\GateStudents( $this->id );
		$factory = new Population\StudentFactory;
		$population = new \PSU_Population( $query, $factory );
		$population->query();

		return $population;
	}//end students

	/**
	 * @todo Implement! Allow filtering by gate_system_id.
	 */
	public function student_count( $args = '' ) {
		$count = $this->students()->count();
		return $count;
	}//end student_count

	/**
	 * prepares arguments for DML
	 */
	protected function _prep_args() {
		// this is the data prepared for binding.
		// these fields are ordered as they are in the table
		$args = array(
			'the_id' => $this->id,
			'gate_system_id' => $this->gate_system_id,
			'name' => $this->name,
			'slug' => $this->slug ?: \PSU::createSlug( $this->name ),
			'sort_order' => $this->sort_order,
		);

		return $args;
	}//end _prep_args

}//end class Gate
