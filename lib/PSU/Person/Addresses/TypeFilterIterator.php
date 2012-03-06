<?php

namespace PSU\Person\Addresses;

class TypeFilterIterator extends \PSU_FilterIterator {
	public $type;

	public function __construct( $type, $it ) {
		parent::__construct( $it );

		$this->type = $type;
	}//end constructor

	public function accept() {
		$address = $this->current();

		return strtoupper( $address->atyp_code ) == strtoupper( $this->type );
	}//end accept
}//end PSU\Person\Addresses\TypeIterator
