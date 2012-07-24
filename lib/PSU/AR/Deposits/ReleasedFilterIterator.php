<?php
namespace PSU\AR\Deposits;

class ReleasedFilterIterator extends \PSU_FilterIterator {
	public function accept() {
		$deposit = $this->current();

		return $deposit->release_date_timestamp() > mktime(0, 0, 0);
	}
}//end class
