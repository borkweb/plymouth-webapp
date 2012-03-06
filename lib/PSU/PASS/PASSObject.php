<?php

/**
 * A generic PASS object with reusable methods
 */
class PSU_PASS_PASSObject extends PSU_DataObject {

	/**
	 * This function accounts for PASS staff that desire to type
	 * dates as MM-DD-YY, which is not handled in strtotime functions.
	 * This function detects that pattern of numbers and changes the 
	 * datestring to be YYYY-MM-DD.
	 */
	public function checkdate($str) {
		if (preg_match('/^([0-9]{1,2})[-\/]([0-9]{1,2})[-\/]([0-9]{2})/',$str,$matches)) {
			$str = '20'.$matches[3].'-'.$matches[1].'-'.$matches[2];
		}
		return $str;
	}

}//end PSU_PASS_PASSObject
