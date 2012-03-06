<?php

/**
 * Holds information about a parent.
 */
class PSU_Student_Finaid_Application_Parent extends PSU_DataObject {
	/**
	 * Aliases are doubled-up; this is fine if you're only passing father or mother,
	 * the object won't overwrite aliases with fields that don't exist in the incoming row.
	 */
	public $aliases = array(
		'rcrapp4_fath_ssn' => 'ssn',
		'rcrapp4_fath_last_name' => 'last_name',
		'rcrapp4_fath_first_name_ini' => 'first_initial',
		'rcrapp4_fath_birth_date' => 'birth_date',

		'rcrapp4_moth_ssn' => 'ssn',
		'rcrapp4_moth_last_name' => 'last_name',
		'rcrapp4_moth_first_name_ini' => 'first_initial',
		'rcrapp4_moth_birth_date' => 'birth_date',
	);

	/**
	 * Test if this parent is the same as another parent.
	 */
	public function equals( $other ) {
		return $this->ssn == $other->ssn;
	}//end equals

	/**
	 * Return date in YYYY-MM-DD format.
	 *
	 * @sa http://en.wikipedia.org/wiki/ISO_8601#Calendar_dates
	 */
	public function birthdate_iso8601() {
		$ts = strtotime( $this->birth_date );
		return strftime( '%Y-%m-%d', $ts );
	}//end birthdate_iso8601
}//end PSU_Student_Finaid_Application_Parent
