<?php

namespace PSU\TeacherCert\Student;

use PSU\TeacherCert,
	PSU\TeacherCert\ActiveRecord,
	PSU\TeacherCert\ChecklistItemAnswer;

class ChecklistItem extends TeacherCert\ChecklistItem {
	static $table = 'student_checklist_item_answers';

	/**
	 * Return the current answer, as a string suitable for displaying
	 * to the user. Pass a value to set a new answer.
	 *
	 * @param string|int $answer The new answer ID, or a date value for special date fields.
	 * @return ChecklistItemAnswer
	 */
	public function answer( $answer = null ) {
		// Set the answer for this checklist item
		if( null !== $answer ) {
			$this->set_answer( $answer );
		}

		// We have a custom answer set; return that answer, or our local
		// date value.
		if( $this->answer_id ) {
			return $this->checklist_item_answer();
		}

		// No answer set; return the default answer value.
		if( $ci = $this->checklist_item() ) {
			if( $answer = $ci->default_answer() ) {
				return $answer;
			}
		}

		return null;
	}//end answer

	/**
	 * Underlying checklist item.
	 */
	public function checklist_item() {
		return TeacherCert\ChecklistItem::get( $this->checklist_item_id() );
	}//end checklist_item

	public function checklist_item_answer() {
		if( $this->has_answer() ) {
			return ChecklistItemAnswer::get( $this->answer_id );
		} else {
			return $this->default_answer();
		}
	}

	/**
	 * Date fields have just one answer, which is marked as both the default
	 * and "complete." Override complete() to more sanely check for a
	 * supplied date field.
	 */
	public function complete() {
		// date fields must have a value provided
		if( $this->is_date_field() || $this->is_text_field() ) {
			return (bool)$this->answer_value;
		}

		// fall back to answer's "complete" status
		if( $this->has_answer() ) {
			return $this->answer()->complete();
		}

		// By definition, an unanswered checklist item is incomplete.
		return false;
	}//end complete

	/**
	 * True if this item has the default answer. Silently falls back on the
	 * default if an answer has not been selected for this item.
	 *
	 * @sa has_answer()
	 */
	public function is_default() {
		if( $this->answer_value ) {
			return false;
		}

		if( $answer = $this->answer() ) {
			return $answer->default_answer();
		}

		return true;		
	}//end is_default

	/**
	 * True if an answer has been selected. Contrast with is_default(), which
	 * silently falls back on the default answer if no answer has yet been
	 * selected.
	 *
	 * @sa is_default()
	 */
	public function has_answer() {
		if( $this->is_date_field() || $this->is_text_field() ) {
			return (bool)$this->answer_value;
		}

		return (bool)$this->answer_id;
	}//end has_answer

	/**
	 * Checklist item name.
	 */
	public function name() {
		return $this->checklist_item()->name();
	}//end name

	/**
	 *
	 */
	public function set_answer( $answer ) {
		if( is_numeric( $answer ) ) {
			// corresponds to an answer, no custom value
			$answer_obj = ChecklistItemAnswer::get( $answer );

			$this->answer_id = $answer_obj->id;
			$this->answer_value = null;
		} elseif( $this->is_date_field() && $value = strtotime($answer) ) {
			// corresponds to a date field
			$answer_obj = $this->default_answer();
			$date = date('Y-m-d', $value);

			$this->answer_id = $answer_obj->id;
			$this->answer_value = $date;
		} elseif( $this->is_text_field() ) {
			// corresponds to a date field
			$answer_obj = $this->default_answer();
			$this->answer_id = $answer_obj->id;
			$this->answer_value = $answer;
		}//end elseif

		$this->save();
	}//end set_answer

	/**
	 * Overridable method to return the ID number for this checklist item.
	 */
	public function checklist_item_id() {
		return $this->checklist_item_id;
	}//end checklist_item_id

	/**
	 * prepares arguments for DML
	 */
	protected function _prep_args() {
		// this is the data prepared for binding.
		// these fields are ordered as they are in the table
		$args = array(
			'the_id' => $this->id ?: 0,
			'student_gate_system_id' => $this->student_gate_system_id,
			'checklist_item_id' => $this->checklist_item_id(),
			'answer_id' => $this->answer_id,
			'answer_value' => $this->answer_value,
		);

		return $args;
	}//end _prep_args

	public function __toString() {
		if( $answer = $this->answer() ) {
			if( $this->is_date_field() || $this->is_text_field() ) {
				return $this->answer_value ?: '';
			}//end if

			return (string)$answer->answer;
		}

		return '';
	}//end __toString
}//end class PSU\TeacherCert\Student\ChecklistItem
