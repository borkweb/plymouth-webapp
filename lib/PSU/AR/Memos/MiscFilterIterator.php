<?php

class PSU_AR_Memos_MiscFilterIterator extends PSU_FilterIterator {
	public function accept() {
		$memo = $this->current();

		return $memo->billing_ind != 'Y';
	}
}//end PSU_AR_Memos_MiscFilterIterator
