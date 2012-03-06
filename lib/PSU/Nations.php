<?php

namespace PSU;

abstract class Nations implements \IteratorAggregate {
	/**
	 * Class for our nations.
	 */
	public $_nationClass = '\PSU\Nation';

	public abstract function get_by_code( $code );
	public abstract function get_by_name( $name );

	public function getIterator() {
		return new EmptyIterator;
	}//end getIterator

	public abstract function sort_by_name();
	public abstract function sort_by_code();

	public function sorted_by_name() {
		$this->sort_by_name();
		return $this->getIterator();
	}//end sorted_by_name

	public function sorted_by_code() {
		$this->sort_by_code();
		return $this->getIterator();
	}//end sorted_by_name
}//end abstract class \PSU\Nations
