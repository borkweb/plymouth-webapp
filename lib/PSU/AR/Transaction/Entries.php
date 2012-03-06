<?php

namespace PSU\AR\Transaction;

abstract class Entries implements \IteratorAggregate {
	public $data;

	abstract protected function record( $row );
	abstract protected function sum_factory( $it );

	/**
	 * add an item to the collection
	 *
	 * @param $item \b PSU_AR_Receivable record to add to the entry collection
	 */
	public function add( $item ) {
		$this->data[] = $item;
	}//end add

	/**
	 * load receivables in the data collection
	 *
	 * @param $rows \b rows to load as receivables
	 */
	public function load( $rows = null ) {
		if( $rows === null ) {
			$rows = $this->get();
		}//end if

		$this->data = array();

		foreach( $rows as $row ) {
			$data = $this->record( $row );
			$this->data[] = $data;
		}//end foreach
	}//end load

	/**
	 * retrieve entries for a person
	 */
	public function get() {
		return array();
	}//end get

	/**
	 * return the iterator
	 */
	public function getIterator() {
		if( ! $this->data ) {
			return new \PSU\EmptyIterator;
		}//end if

		return new \ArrayIterator( $this->data );
	}//end getIterator

	/**
	 * retrieves entries from the specified term
	 *
	 * @param $term_code \b Term code to filter on
	 * @param $it \b Iterator to execute the filter against
	 */
	public function term( $term_code, $it = null ) {
		if( ! $it ) {
			$it = $this->getIterator();
		}//end if

		return new Entries\TermFilterIterator( $it, $term_code );
	}//end term

	/**
	 * returns a Receivable sum object given an iterator
	 *
	 * @param $it \b Iterator to sum
	 */
	public function sum( $it = null ) {
		if( ! $it ) {
			$it = $this->getIterator();
		}//end if

		return $this->sum_factory( $it );
	}//end sum
}//end class PSU\AR\Transaction\Entries
