<?php

/**
 * A container for all receivables, grouped by term.
 */
class PSU_AR_Receivables_Terms extends PSU_AR_TermContainer {
	public function __construct() {
		parent::__construct( 'PSU_AR_Receivable_Sum' );	
	}//end constructor
}//end class PSU_AR_Receivables_Terms
