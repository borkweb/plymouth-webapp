<?php

/**
 * A container for all receivables, grouped by term.
 */
class PSU_AR_AidAuthorizations_Terms extends PSU_AR_TermContainer {
	public function __construct() {
		parent::__construct( 'PSU_AR_AidAuthorizations_Sum' );	
	}//end constructor
}//end class PSU_AR_AidAuthorizations_Terms
