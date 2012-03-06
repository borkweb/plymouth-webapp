<?php

class PSU_Student_Finaid_Requirements_DuplicateFilterIterator extends PSU_FilterIterator {
	/**
	 * Track requirements we have already seen.
	 */
	public $seen = array();

	public function accept() {
		$requirement = parent::current();

		if( $this->has_seen($requirement) ) {
			return false;
		}

		$this->just_saw( $requirement );

		return true;
	}

	public function rewind() {
		$this->seen = array();
		parent::rewind();
	}

	public function has_seen( $requirement ) {
		return isset($this->seen[ $this->seen_key($requirement)]);
	}

	public function just_saw( $requirement ) {
		$this->seen[ $this->seen_key($requirement) ] = true;
	}

	public function seen_key( $requirement ) {
		return sprintf('%s:%s', $requirement->rtvtreq_code, $requirement->rtvtrst_code);
	}
}
