<?php

class PSU_Population_UserFactory_PSUPerson extends PSU_Population_UserFactory {
	/**
	 * Column that holds the user's identifier.
	 */
	public $column;

	public function __construct( $column = null ) {
		$this->column = $column;
	}//end __construct

	public function create( $row ) {
		if( isset($this->column) ) {
			return new PSUPerson( $row[ $this->column ] );
		} else {
			if( is_scalar($row) ) {
				return new PSUPerson( $row );
			}
			
			throw new Exception('please specify an identifying column in the query result row');
		} 
	}//end create
}
