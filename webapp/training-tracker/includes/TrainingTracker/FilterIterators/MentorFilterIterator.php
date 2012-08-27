<?php

namespace TrainingTracker;

class MentorFilterIterator extends \PSU_FilterIterator {
	public function accept() {
		$mentor = $this->current();

		return 'shift_leader' == $mentor->privileges || 'supervisor' == $mentor->privileges;
	}//end accept
}//end class

