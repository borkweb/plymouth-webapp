<?php

namespace PSU\AR\Fees\RegistrationFees;

class ResidentialFilterIterator extends \PSU_FilterIterator {
	public $code;

	public function __construct( $code, $it = null ) {
		$this->code = $code;
		parent::__construct( $it );
	}//end constructor

	public function accept() {
		$current = $this->current();

		return $current->resd_code == $this->code || ! $current->resd_code;
	}
}//end class
