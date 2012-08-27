<?php

namespace TrainingTracker;

class AdminFilterIterator extends \PSU_FilterIterator {
	public function accept() {
		$staff = $this->current();

		return 'manager' == $staff->privileges || 'supervisor' == $staff->privileges || 'webguru' == $staff->privileges;
	}//end accept
}//end 


