<?php

namespace PSU\Person\Phones;

class PrimaryFilterIterator extends \PSU_FilterIterator {
	public function accept() {
		$record = $this->current();

		return $record->primary_ind == 'Y';
	}//end accept
}//end PSU\Person\Phones\PrimaryIterator
