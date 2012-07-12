<?php

namespace PSU\AR\Fees\RegistrationFees;

class StudentTypeFilterIterator extends \PSU_FilterIterator {
	public $code;

	public function __construct( $code, $it = null ) {
		$this->code = $code;
		parent::__construct( $it );
	}//end constructor

	public function accept() {
		$current = $this->current();

		return $current->styp_code == $this->code || ! $current->styp_code;
	}
}//end class

