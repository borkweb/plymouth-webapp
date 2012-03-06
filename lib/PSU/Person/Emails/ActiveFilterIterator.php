<?php

namespace PSU\Person\Emails;

class ActiveFilterIterator extends \PSU_FilterIterator {
	public function accept() {
		$record = $this->current();

		return $record->status_ind != 'I';
	}
}//end PSU\Person\Emails\ActiveFilterIterator
