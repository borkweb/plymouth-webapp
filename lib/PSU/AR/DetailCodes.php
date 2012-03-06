<?php

class PSU_AR_DetailCodes implements IteratorAggregate {
	public $data;

	public $pidm;

	public function __construct() {
	}//end __construct

	public function load( $rows = null ) {
		if( $rows === null ) {
			$rows = $this->get();
		}//end if

		$this->data = array();

		foreach( $rows as $row ) {
			$data = new PSU_AR_DetailCode( $row );
			$this->data[ $data->detail_code ] = $data;
		}//end foreach
	}//end load

	/**
	 * retrieve deposits for a person
	 */
	public function get() {

		$sql = "SELECT * FROM tbbdetc";
		$rset = PSU::db('banner')->Execute($sql);

		return $rset ? $rset : array();
	}//end get

	public function getIterator() {
		return new ArrayIterator( $this->deposits );
	}//end getIterator
}//end class PSU_AR_DetailCodes
