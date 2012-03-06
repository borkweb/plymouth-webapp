<?php

namespace PSU\AR\MiscBillingCharges;

class UnprocessedFilterIterator extends \PSU_FilterIterator {
	public function accept() {
		$el = $this->current();

		return ! $el->processed();
	}
}//end PSU\AR\MiscBillingCharges\UnprocessedFilterIterator
