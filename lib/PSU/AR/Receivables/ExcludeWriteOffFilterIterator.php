<?php

class PSU_AR_Receivables_ExcludeWriteOffFilterIterator extends PSU_FilterIterator {
	public function accept() {
		$memo = $this->current();

		return $memo->detail_code != 'IKWO';
	}
}//end PSU_AR_Receivables_ExcludeWriteOffFilterIterator
