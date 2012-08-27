<?php

namespace TrainingTracker;

class MenteeFilterIterator extends \PSU_FilterIterator {
	public function accept() {
		$mentee = $this->current();

		return 'trainee' == $mentee->privileges || 'sta' == $mentee->privileges;
	}//end accept
}//end 


