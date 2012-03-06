<?php

class PSU_Student_Aidyear_AttendanceCost_Component extends PSU_DataObject {
	public $aliases = array(
		'rtvcomp_desc' => 'description',
		'rbracmp_amt' => 'amount',
	);

	public function aliases() {
		parent::aliases();

		if( isset( $this->amount ) ) {
			$this->amount_formatted = PSU_MoneyFormatter::create()->format( $this->amount );
		}
	}
}//end class PSU_Student_Aidyear_AttendanceCost_Component
