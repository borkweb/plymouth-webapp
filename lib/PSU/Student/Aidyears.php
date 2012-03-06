<?php

class PSU_Student_Aidyears implements ArrayAccess, IteratorAggregate {
	public $pidm;
	public $years = array();

	public function __construct( $pidm ) {
		$this->pidm = $pidm;
	}

	public function offsetGet( $aidy ) {
		if( ! isset( $this->years[$aidy] ) ) {
			$this->years[$aidy] = new PSU_Student_Aidyear( $this->pidm, $aidy );
		}

		return $this->years[$aidy];
	}

	public function offsetSet( $aidy, $value ) {
		throw new Exception('you may not manually set an aid year');
	}

	public function offsetExists( $aidy ) {
		return isset( $this->years[$aidy] );
	}

	public function offsetUnset( $aidy ) {
		unset( $this->years[$aidy] );
	}

	public function next_year( $aid_year ) {
		$year = substr( $aid_year, 0, 2 );
		return sprintf( "%02d%02d", $year + 1, $year + 2 );
	}

	public function getIterator() {
		return new ArrayIterator( $this->years );
	}
}
