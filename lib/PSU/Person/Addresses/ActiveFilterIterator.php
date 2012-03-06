<?php

namespace PSU\Person\Addresses;

class ActiveFilterIterator extends \PSU_FilterIterator {
	public function accept() {
		$address = $this->current();

		$now = time();

		return (
			$address->status_ind != 'I' && 
			$address->from_date_timestamp() <= $now &&
			( ! $address->to_date || $address->to_date_timestamp() >= $now )
		);
	}
}//end PSU\Person\Addresses\ActiveIterator
