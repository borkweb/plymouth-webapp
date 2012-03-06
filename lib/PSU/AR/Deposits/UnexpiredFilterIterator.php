<?php

class PSU_AR_Deposits_UnexpiredFilterIterator extends PSU_FilterIterator {
	public function accept() {
		$deposit = $this->current();

		return $deposit->expiration_date_timestamp() > mktime(0, 0, 0);
	}
}//end PSU_AR_Deposits_UnexpiredFilterIterator
