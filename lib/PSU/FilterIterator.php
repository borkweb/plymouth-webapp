<?php

abstract class PSU_FilterIterator extends FilterIterator implements Countable {
	public function count() {
		$elements = iterator_to_array( $this );
		return count( $elements );
	}

	public function not_empty() {
		$this->rewind();
		$result = (bool)$this->current();

		return $result;
	}

	public function is_empty() {
		return ! $this->not_empty();
	}
}
