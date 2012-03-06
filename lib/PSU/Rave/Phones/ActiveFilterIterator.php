<?php

namespace PSU\Rave\Phones;

class ActiveFilterIterator extends \PSU_FilterIterator {
	public function accept() {
		$record = $this->current();

		return ! $record->end_date;
	}
}//end PSU\Rave\Phones\ActiveIterator
