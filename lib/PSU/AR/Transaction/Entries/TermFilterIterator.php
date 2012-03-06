<?php

namespace PSU\AR\Transaction\Entries;

class TermFilterIterator extends \PSU_FilterIterator {
	public $term_code;

	public function __construct( $it, $term_code ) {
		parent::__construct( $it );

		$this->term_code = $term_code;
	}//end __construct

	public function accept() {
		$data = $this->current();

		return $data->term_code == $this->term_code;
	}//end accept
}//end PSU\AR\Transaction\Entries\TermFilterIterator
