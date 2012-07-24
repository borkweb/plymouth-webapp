<?php
namespace PSU\AR\Memos;

class MiscFilterIterator extends \PSU_FilterIterator {
	public function accept() {
		$memo = $this->current();

		return $memo->billing_ind != 'Y';
	}
}//end class
