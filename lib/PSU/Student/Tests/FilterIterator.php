<?php

namespace PSU\Student\Tests;

class FilterIterator extends \FilterIterator {
	public $codes;

	/**
	 * Set the acceptable codes.
	 **/
	public function codes( $codes ) {
		if( ! is_array($codes) ) {
			$codes = array($codes);
		}

		$this->codes = $codes;
	}//end codes

	/**
	 *
	 */
	public function accept() {
		$test = $this->getInnerIterator()->current();
		return in_array( $test->code(), $this->codes );
	}//end accept
}//end \PSU\Student\Tests\FilterIterator
