<?php
namespace PSU\AR\Receivables;

/**
 * A container for all receivables, grouped by term.
 */
class Terms extends \PSU\AR\TermContainer {
	public function __construct() {
		parent::__construct( '\PSU\AR\Receivable\Sum' );	
	}//end constructor
}//end class
