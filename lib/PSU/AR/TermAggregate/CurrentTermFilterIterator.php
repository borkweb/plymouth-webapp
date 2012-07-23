<?php
namespace PSU\AR\TermAggregate;

class CurrentTermFilterIterator extends \PSU_FilterIterator {
	public $term_code;

	public function __construct( $it, $term_code ) {
		parent::__construct( $it );

		$this->term_code = $term_code;
	}//end constructor

	public function accept() {
		$el = $this->current();

		return $el->term_code == $this->term_code;
	}
}//end class
