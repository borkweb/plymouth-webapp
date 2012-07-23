<?php
namespace PSU\AR\Deposits;

/**
 * A container for all memos, grouped by term.
 */
class Terms extends \PSU\AR\TermContainer {
	public function __construct() {
		parent::__construct( '\PSU\AR\Deposit\Sum' );	
	}//end constructor
}//end class
