<?php

/**
 * A container for all memos, grouped by term.
 */
class PSU_AR_Memos_Terms extends PSU_AR_TermContainer {
	public function __construct() {
		parent::__construct( 'PSU_AR_Memo_Sum' );	
	}//end constructor
}//end class PSU_AR_Memos_Terms
