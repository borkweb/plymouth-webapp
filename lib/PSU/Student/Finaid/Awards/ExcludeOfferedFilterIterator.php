<?php

class PSU_Student_Finaid_Awards_ExcludeOfferedFilterIterator extends PSU_FilterIterator {
	public function accept() {
		$award = $this->current();

		return $award->status != 'Offered';
	}
}
