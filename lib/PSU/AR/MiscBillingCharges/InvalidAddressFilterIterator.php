<?php

namespace PSU\AR\MiscBillingCharges;

class InvalidAddressFilterIterator extends \PSU_FilterIterator {
	public function accept() {
		$el = $this->current();

		return ! $el->valid_addresses();
	}
}//end PSU\AR\MiscBillingCharges\InvalidAddressFilterIterator
