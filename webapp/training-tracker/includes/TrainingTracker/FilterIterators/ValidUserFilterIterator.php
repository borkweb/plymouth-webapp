<?php

namespace TrainingTracker;

class ValidUserFilterIterator extends \PSU_FilterIterator {
	public function accept() {
		$staff = $this->current();

		return 'trainee' == $staff->privileges || 'sta' == $staff->privileges || 'shift_leader' == $staff->privileges || 'manager' == $staff->privileges || 'supervisor' == $staff->privileges || 'webguru' == $staff->privileges;
	}//end accept
}//end 


