<?php

namespace TrainingTracker;

class PromotionFilterIterator extends \PSU_FilterIterator {
	public function accept() {
		$staff = $this->current();

		return 'trainee' == $staff->privileges || 'sta' == $staff->privileges || 'shift_leader' == $staff->privileges || 'supervisor' == $staff->privileges;
	}//end accept

}//end 

