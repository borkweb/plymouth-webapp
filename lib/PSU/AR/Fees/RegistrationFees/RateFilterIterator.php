<?php

namespace PSU\AR\Fees\RegistrationFees;

class RateFilterIterator extends \PSU_FilterIterator {
	public $rate_code;

	public function __construct( $rate_code, $it = null ) {
		$this->rate_code = $rate_code;
		parent::__construct( $it );
	}//end constructor

	public function accept() {
		$current = $this->current();

		return $current->rate_code == $this->rate_code;
	}
}//end class
