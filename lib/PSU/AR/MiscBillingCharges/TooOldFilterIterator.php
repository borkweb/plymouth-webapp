<?php

namespace PSU\AR\MiscBillingCharges;

class TooOldFilterIterator extends \PSU_FilterIterator {
	public function accept() {
		$el = $this->current();

		return $el->too_old();
	}
}//end class
