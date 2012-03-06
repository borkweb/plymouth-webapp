<?php

/**
 * Hold award data for multiple terms.
 */
class PSU_Student_Finaid_Awards_Terms implements ArrayAccess {
	public $awards = array();

	public function add( $award ) {
		$term_code = $award->term_code;
		$this->awards[$term_code] = $award;
	}//end add

	public function offsetGet( $key ) {
		return isset( $this->awards[$key] ) ? $this->awards[$key] : null;
	}

	public function offsetExists( $key ) {
		return isset( $this->awards[$key] );
	}

	public function offsetUnset( $key ) {
		unset( $this->awards[$key] );
	}

	public function offsetSet( $key, $value ) {
		if( is_null($key) ) {
			$this->awards[] = $value;
		} else {
			$this->awards[$key] = $value;
		}
	}

	public function 
}//end PSU_Student_Finaid_Awards_Term
