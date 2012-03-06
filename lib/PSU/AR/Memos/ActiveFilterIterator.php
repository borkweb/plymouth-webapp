<?php

class PSU_AR_Memos_ActiveFilterIterator extends PSU_FilterIterator {
	public function accept() {
		$memo = $this->current();

		return $memo->expiration_date_timestamp() >= mktime(0, 0, 0) || date('Y', $memo->expiration_date_timestamp()) > 2038;
	}
}//end PSU_AR_Memos_ActiveFilterIterator
