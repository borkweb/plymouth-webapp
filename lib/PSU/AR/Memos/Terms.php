<?php
namespace PSU\AR\Memos;

/**
 * A container for all memos, grouped by term.
 */
class Terms extends \PSU\AR\TermContainer {
	public function __construct() {
		parent::__construct( 'PSU\AR\Memo\Sum' );	
	}//end constructor
}//end class
