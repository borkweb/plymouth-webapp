<?php

class PSU_AR_DetailCode extends PSU_DataObject {
	public $aliases = array(
	);

	/**
	 * constructor
	 *
	 * @param $row array Array of row elements
	 */
	public function __construct( $row = null ) {
		if( $row ) {
			// get rid of table name from field names
			$row = PSU::cleanKeys('tbbdetc_', '', $row);
		}//end if

		parent::__construct( $row );
	}//end constructor
}//end class PSU_AR_DetailCode
