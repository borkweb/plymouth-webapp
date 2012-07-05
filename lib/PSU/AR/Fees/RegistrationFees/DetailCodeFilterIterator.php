<?php

namespace PSU\AR\Fees\RegistrationFees;

class DetailCodeFilterIterator extends \PSU_FilterIterator {
	public $code;

	public function __construct( $code, $it = null ) {
		$this->code = $code;
		parent::__construct( $it );
	}//end constructor

	public function accept() {
		$current = $this->current();

		return $current->detail_code == $this->code;
	}
}//end class
