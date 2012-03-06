<?php

namespace PSU\Person\Emails;

class PreferredFilterIterator extends \PSU_FilterIterator {
	public function accept() {
		$record = $this->current();

		return $record->preferred_ind == 'Y';
	}//end accept
}//end PSU\Person\Emails\PreferredFilterIterator
