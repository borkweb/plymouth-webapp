<?php

class PSU_Student_Finaid_Awards_AuthorizedFilterIterator extends PSU_FilterIterator {
	public function accept() {
		$award = $this->current();

		return !! $award->authorized;
	}
}
