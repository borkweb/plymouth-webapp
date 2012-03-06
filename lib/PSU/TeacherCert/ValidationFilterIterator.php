<?php

namespace PSU\TeacherCert;

class ValidationFilterIterator extends \PSU_FilterIterator {
	public $id = null;
	public $field = null;
	public $class_name = null;
	public $inverse = null;

	public function __construct( $class_name, $field, $item, $it, $inverse = false ) {
		$this->class_name = $class_name;
		$this->field = $field;
		$this->inverse = $inverse;

		if( is_object( $item ) ) {
			$this->id = $item->$field;
		} elseif( is_numeric( $item ) ) {
			$this->id = $item;
		} else {
			$sau = $class_name::get( $item );
			$this->id = $item;
		}//end else

		parent::__construct( $it );
	}//end constructor

	public function accept() {
		$item = $this->current();

		if( $this->inverse ) {
			return $this->id != $item->{$this->field};
		}//end if

		return $this->id == $item->{$this->field};
	}
}//end FilterIterator
