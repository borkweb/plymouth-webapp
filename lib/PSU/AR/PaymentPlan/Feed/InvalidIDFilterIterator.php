<?php

namespace PSU\AR\PaymentPlan\Feed;

class InvalidIDFilterIterator extends \PSU_FilterIterator {
	public function __construct( $it = null ) {
		parent::__construct( $it );
	}//end constructor

	public function accept() {
		$current = $this->current();

		return ! $current->pidm;
	}//end accept
}//end class
