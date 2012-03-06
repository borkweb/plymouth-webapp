<?php

namespace PSU\TeacherCert;

class ChecklistItem extends ActiveRecord {
	static $table = 'checklist_items';
	static $_name = 'Checklist Item';

	protected $answers = null;

	protected $is_date_field = null;
	protected $is_text_field = null;

	/**
	 * Populate possible answers for this checklist item.
	 */
	public function answers() {
		$collection = $this->_get_related( __FUNCTION__, 'PSU\\TeacherCert\\ChecklistItemAnswers::cached_get', $this->checklist_item_id() );

		// foreach so we get the iterator, but only run once, since date
		// fields only have one checklist item
		if( null === $this->is_date_field || null === $this->is_text_field ) {
			foreach( $collection as $answer ) {
				$this->is_date_field = 'date' == $answer->type;
				$this->is_text_field = 'text' == $answer->type;
				break;
			}
		}

		return $collection;
	}//end answers

	/**
	 * Overridable method to return the ID number for this checklist item.
	 */
	public function checklist_item_id() {
		return $this->id;
	}//end checklist_item_id

	/**
	 *
	 */
	public function default_answer() {
		if( $this->answers() && $this->answers()->default_answer() ) {
			return $this->answers()->default_answer();
		}

		return null;
	}//end default_answer

	/**
	 * True if this checklist item is meant to accept a date field.
	 */
	public function is_date_field() {
		$this->answers(); // preload
		return $this->is_date_field;
	}//end is_date_field

	/**
	 * True if this checklist item is meant to accept free-form text
	 */
	public function is_text_field() {
		$this->answers(); // preload
		return $this->is_text_field;
	}//end is_text_field

	/**
	 * Checklist item name.
	 */
	public function name() {
		return $this->name;
	}//end name

	/**
	 * prepares arguments for DML
	 */
	protected function _prep_args() {
		// this is the data prepared for binding.
		// these fields are ordered as they are in the table
		$args = array(
			'the_id' => $this->id,
			'gate_id' => $this->gate_id,
			'name' => $this->name,
			'slug' => $this->slug ?: \PSU::createSlug( $this->name ),
			'legacy_code' => $this->legacy_code,
		);

		return $args;
	}//end _prep_args

}//end class ChecklistItem
