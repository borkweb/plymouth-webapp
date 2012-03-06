<?php

/**
 * A container for all awards, grouped by term.
 */
class PSU_Student_Finaid_Awards_Terms {
	/**
	 * Terms we know about.
	 */
	public $terms;

	/**
	 * Cache of term descriptions, ie. 201110 == UG Fall 2010
	 */
	public $term_desc;

	public function __construct() {
	}

	public function add( PSU_Student_Finaid_Award_Term $award ) {
		if( ! isset( $this->terms[$award->term_code] ) ) {
			$this->terms[$award->term_code];
		} 

		$this->terms[$award->term_code][] = $award;
		$this->term_desc[$award->term_code] = $award->term;
	}//end add

	/**
	 * Sum all the awards in a term.
	 * @return PSU_Student_Finaid_Award_Sum
	 */
	public function sum( $term = null ) {
		$awards = $this->get( $term );
		return new PSU_Student_Finaid_Award_Sum( $awards );
	}

	/**
	 * @return Iterable
	 */
	public function filter( Iterator $awards, $term_code ) {
		$matches = array();

		foreach( $awards as $award ) {
			if( isset($award[$term_code]) ) {
				$matches[] = $award[$term_code];
			}
		}

		return new ArrayIterator( $matches );
	}

	/**
	 * Get a specific term of awards.
	 *
	 * @return Iterator
	 */
	public function get( $term = null ) {
		return new ArrayIterator( $this->terms[$term] );
	}//end get

	/**
	 * An interator for all our child terms, each with their own awards.
	 *
	 * @return Iterator
	 */
	public function terms() {
		if( empty($this->terms) ) {
			return new EmptyIterator;
		}

		return new ArrayIterator( $this->terms );
	}//end terms

	/**
	 * Termcodes we know about, with their descriptions.
	 *
	 * @return Iterator
	 */
	public function termcodes() {
		return new ArrayIterator( $this->term_desc );
	}//end termcodes
}//end class PSU_Student_Finaid_Awards_Terms
