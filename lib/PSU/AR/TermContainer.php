<?php
namespace PSU\AR;

/**
 * A container for all items, grouped by term.
 */
class TermContainer {
	/**
	 * Terms we know about.
	 */
	public $terms;

	/**
	 * Cache of term descriptions, ie. 201110 == UG Fall 2010
	 */
	public $term_desc;

	public function __construct( $sum_type ) {
		$this->sum_type = $sum_type;
	}

	public function add( $item ) {
		if( ! isset( $this->terms[$item->term_code] ) ) {
			$this->terms[$item->term_code];
		} 

		$this->terms[$item->term_code][] = $item;
		$this->term_desc[$item->term_code] = $item->term;
	}//end add

	/**
	 * Sum all the items in a term.
	 * @return \PSU\AR\{item}\Sum
	 */
	public function sum( $term = null ) {
		$items = $this->get( $term );
		$sum_type = $this->sum_type;
		return new $sum_type( $items );
	}

	/**
	 * @return Iterable
	 */
	public function filter( \Iterator $items, $term_code ) {
		$matches = array();

		foreach( $items as $item ) {
			if( isset($item[$term_code]) ) {
				$matches[] = $item[$term_code];
			}
		}

		return new \ArrayIterator( $matches );
	}

	/**
	 * Get a specific term of items.
	 *
	 * @return Iterator
	 */
	public function get( $term = null ) {
		return new \ArrayIterator( $this->terms[$term] );
	}//end get

	/**
	 * An interator for all our child terms, each with their own items.
	 *
	 * @return Iterator
	 */
	public function terms() {
		if( empty($this->terms) ) {
			return new \EmptyIterator;
		}

		return new \ArrayIterator( $this->terms );
	}//end terms

	/**
	 * Termcodes we know about, with their descriptions.
	 *
	 * @return Iterator
	 */
	public function termcodes() {
		if( empty($this->terms) ) {
			return new \EmptyIterator;
		}

		return new \ArrayIterator( $this->term_desc );
	}//end termcodes
}//end class
