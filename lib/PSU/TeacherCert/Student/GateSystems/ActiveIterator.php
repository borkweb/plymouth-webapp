<?php

namespace PSU\TeacherCert\Student\GateSystems;

class ActiveIterator extends \FilterIterator {
	public function __construct( \Iterator $iterator ) {
		parent::__construct( $iterator );
	}

	public function accept() {
		$gate_system = $this->getInnerIterator()->current();
		return $gate_system->active();
	}
}
