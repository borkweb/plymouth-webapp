<?php

class PSU_Student_Finaid_Application_ParentRowFilter extends FilterIterator {
	public function __construct( Iterator $it, $filter ) {
		parent::__construct( $it );
		$this->filter = $filter;
	}

	public function accept() {
		$field = $this->key();
		return 0 === strpos( $field, 'rcrapp4_' . $this->filter );
	}
}
