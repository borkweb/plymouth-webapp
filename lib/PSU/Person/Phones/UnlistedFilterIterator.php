<?php

namespace PSU\Person\Phones;

class UnlistedFilterIterator extends \PSU_FilterIterator {
	public function accept() {
		$record = $this->current();

		return $record->unlist_ind == 'Y';
	}//end accept
}//end PSU\Person\Phones\UnlistedIterator
