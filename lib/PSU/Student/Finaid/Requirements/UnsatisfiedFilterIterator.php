<?php

class PSU_Student_Finaid_Requirements_UnsatisfiedFilterIterator extends PSU_FilterIterator {
	public function accept() {
		$requirement = $this->current();
		$accept = $requirement->rtvtrst_sat_ind === 'N';
		return $accept;
	}
}
