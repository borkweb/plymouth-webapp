<?php

/**
 * Filter for accepted requests.
 */
class PSU_PASS_Filter_Accepted extends FilterIterator {
	public function accept() {
		$request = $this->getInnerIterator()->current();

		return $request->accepted();
	}//end accept
}//end class PSU_PASS_Filter_Accepted
