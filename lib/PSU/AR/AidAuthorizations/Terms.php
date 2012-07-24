<?php
namespace PSU\AR\AidAuthorizations;

/**
 * A container for all receivables, grouped by term.
 */
class Terms extends \PSU\AR\TermContainer {
	public function __construct() {
		parent::__construct( 'PSU\AR\AidAuthorizations\Sum' );	
	}//end constructor
}//end class
