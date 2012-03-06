<?php

class PSU_Population_Iterator extends \ArrayIterator implements \Countable {
	public $userfactory;

	public function count() {
		$elements = iterator_to_array( $this );
		return count( $elements );
	}


	public function offsetGet( $index ) {
		$value = parent::offsetGet( $index );
		return $this->create( $value );
	}

	public function current() {
		$current = parent::current();
		return $this->create( $current );
	}

	public function create( $user ) {
		return $this->userfactory->create( $user );
	}
}
