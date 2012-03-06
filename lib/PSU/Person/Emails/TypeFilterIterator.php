<?php

namespace PSU\Person\Emails;

class TypeFilterIterator extends \PSU_FilterIterator {
	public $type;

	public function __construct( $type, $it ) {
		parent::__construct( $it );

		$this->type = $type;
	}//end constructor

	public function accept() {
		$record = $this->current();

		return strtoupper( $record->emal_code ) == strtoupper( $this->type );
	}//end accept
}//end PSU\Person\Emails\TypeFilterIterator
