<?php

namespace PSU\TeacherCert;

class ChecklistItemAnswer extends ActiveRecord {
	static $table = 'checklist_item_answers';
	static $_name = 'Checklist Item Answer';

	protected $answers = null;

	/**
	 * returns whether or not the answer is a complete one
	 */
	public function complete() {
		return strtolower( $this->is_complete ) == 'y' ? TRUE : FALSE;
	}//end complete

	/**
	 * returns whether or not the answer is a default answer
	 */
	public function default_answer() {
		return strtolower( $this->is_default ) == 'y' ? TRUE : FALSE;
	}//end default

	/**
	 * prepares arguments for DML
	 */
	protected function _prep_args() {
		// this is the data prepared for binding.
		// these fields are ordered as they are in the table
		$args = array(
			'the_id' => $this->id,
			'checklist_item_id' => $this->checklist_item_id,
			'type' => $this->type,
			'answer' => $this->answer,
			'is_complete' => $this->is_complete,
			'is_default' => $this->is_default,
			'sort_order' => $this->sort_order,
		);

		return $args;
	}//end _prep_args

	public function __toString() {
		return $this->answer;
	}
}//end class ChecklistItemAnswer
