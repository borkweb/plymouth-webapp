<?php

namespace PSU\TeacherCert;

class ChecklistItemAnswers extends Collection {
	static $_name = 'Checklist Item Answers';
	static $child = 'PSU\\TeacherCert\\ChecklistItemAnswer';
	static $table = 'checklist_item_answers';

	static $parent_key = 'checklist_item_id';

	public $default_answer = null;

	/**
	 *
	 */
	public function _get_order() {
		 return 'sort_order, answer';
	}//end _get_order

	/**
	 *
	 */
	public function default_answer() {
		$this->load();
		return $this->default_answer;
	}//end default_answer

	/**
	 * load rows into objects
	 */
	public function load( $rows = null ) {
		parent::load( $rows );

		if( $this->default_answer ) {
			return;
		}

		foreach( $this->children as $answer ) {
			if( $answer->default_answer() ) {
				$this->default_answer = $answer;
				break;
			}
		}//end foreach
	}//end load
}//end class PSU\TeacherCert\ChecklistItems
