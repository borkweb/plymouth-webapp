<?php

namespace PSU\Person\Phones;

class ActiveFilterIterator extends \PSU_FilterIterator {
	public function accept() {
		$record = $this->current();

		return $record->status_ind != 'I';
	}
}//end PSU\Person\Phones\ActiveIterator
