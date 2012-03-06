<?php

/**
 * A container for all memos, grouped by term.
 */
class PSU_AR_Deposits_Terms extends PSU_AR_TermContainer {
	public function __construct() {
		parent::__construct( 'PSU_AR_Deposit_Sum' );	
	}//end constructor
}//end class PSU_AR_Deposits_Terms
