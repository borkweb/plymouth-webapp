<?php

class PSU_Student_Finaid_Requirements_SatisfiedFilterIterator extends PSU_FilterIterator {
	public function accept() {
		$requirement = $this->current();
		return $requirement->rtvtrst_sat_ind === 'Y';
	}
}
