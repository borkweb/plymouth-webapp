<?php
namespace PSU\AR\Receivables;

class ExcludeWriteOffFilterIterator extends \PSU_FilterIterator {
	public function accept() {
		$memo = $this->current();

		return $memo->detail_code != 'IKWO';
	}
}//end class
