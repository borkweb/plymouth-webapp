<?php

abstract class PSU_AR_TermAggregate implements IteratorAggregate {
	public $data;
	public $pidm;
	public $terms;

	/**
	 * force children to define get
	 */
	abstract function get();

	/**
	 * constructor
	 */
	public function __construct( $container, $pidm, $term_code = null ) {
		$this->pidm = $pidm;
		$this->term_code = $term_code;
		$this->container = $container;
		$this->contains = substr( $container, 0, -1 );
	}//end __construct

	/**
	 * count records
	 */
	public function count() {
		return count( $this->data );
	}//end count

	/**
	 * all receivables from the current term
	 */
	public function current_term( $term_code, $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if

		return new PSU_AR_TermAggregate_CurrentTermFilterIterator( $it, $term_code );
	}//end current_term

	/**
	 * all receivables not from the givent term
	 */
	public function exclude_term( $term_code, $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if

		return new PSU_AR_TermAggregate_ExcludeTermFilterIterator( $it, $term_code );
	}//end exclude_term


	/**
	 * all receivables from future terms
	 */
	public function future_terms( $term_code, $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if
		
		return new PSU_AR_TermAggregate_FutureTermsFilterIterator( $it, $term_code );
	}//end future_terms

	/**
	 * load rows into term objects
	 */
	public function load( $rows = null ) {
		if( $this->data ) {
			return;
		}

		$class = $this->container.'_Terms';
		$this->terms = new $class;

		if( $rows === null ) {
			$rows = $this->get();
		}//end if

		$this->data = array();

		$class = $this->contains;
		foreach( $rows as $row ) {
			$this->data[] = $data = new $class( $row );

			$this->terms->add( $data );
		}//end foreach
	}//end load

	public function getIterator() {
		return new ArrayIterator( $this->data );
	}//end getIterator

	/**
	 * all receivables from the previous term
	 */
	public function previous_terms( $term_code, $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if
		
		return new PSU_AR_TermAggregate_PreviousTermsFilterIterator( $it, $term_code );
	}//end previous_terms

	public function sum( $it = null ) {
		if( $it == null ) {
			$it = $this->getIterator();
		}//end if

		$class = $this->contains.'_Sum';
		return call_user_func( array( $class, 'create' ), $it );
	}//end sum

	/**
	 * Return an iterator for all known terms.
	 * @return Iterator
	 */
	public function terms() {
		return $this->terms->terms();
	}//end terms

	/**
	 * Return an iterator for all known term codes.
	 * @return Iterator
	 */
	public function termcodes() {
		return $this->terms->termcodes();
	}//end termcodes
}//end class PSU_AR_TermAggregate
