<?php

class PSU_Student_Finaid_Award implements ArrayAccess {
	/**
	 * Awards by term.
	 */
	public $terms = array();

	public $fund;
	public $fund_code;

	public $fund_messages;

	public function __construct( $data, $fund_messages ) {
		foreach( $data as $key => $value ) {
			if( in_array( $key, array('fund', 'fund_code', 'amount', 'status') ) ) {
				$this->$key = $value;
			}
		}
		
		$this->fund_messages = $fund_messages;
	}

	public function add( $award ) {
		$term_code = $award->term_code;
		$this->terms[$term_code] = $award;
	}//end add

	/**
	 * returns the receivable's detail description
	 */
	public function detail_desc() {
		return \PSU\AR::detail_code( $this->detail_code )->desc;
	}//end detail_desc

	public function has_message() {
		return $this->fund_messages->has_message( $this->fund_code );
	}

	public function message() {
		if( $this->has_message() ) {
			return $this->fund_messages->message( $this->fund_code );
		}

		return null;
	}

	public function offsetGet( $key ) {
		return isset( $this->terms[$key] ) ? $this->terms[$key] : null;
	}

	public function offsetExists( $key ) {
		return isset( $this->terms[$key] );
	}

	public function offsetUnset( $key ) {
		unset( $this->terms[$key] );
	}

	public function offsetSet( $key, $value ) {
		if( is_null($key) ) {
			$this->terms[] = $value;
		} else {
			$this->terms[$key] = $value;
		}
	}

	public function terms() {
		$terms = array();

		foreach( $this->terms as $term_code => $award_term ) {
			$terms[] = array($term_code, $award_term->term);
		}

		return $terms;
	}//end terms

	public function termIterator() {
		return new ArrayIterator( $this->terms );
	}

	/**
	 * Return a summation object that is able to total awards listed in an iterator.
	 */
	public function sum() {
		return new PSU_Student_Finaid_Award_Sum( $this->termIterator() );
	}

	/**
	 * Shortcut to return the total 
	 */
	public function total() {
		return $this->sum()->total();
	}
	/**
	 * returns the receivable's type indicator
	 */
	public function type_ind() {
		return \PSU\AR::detail_code( $this->detail_code )->type_ind;
	}//end type_ind
}//end class PSU_Student_Finaid_Award
