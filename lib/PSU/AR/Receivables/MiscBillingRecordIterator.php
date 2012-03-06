<?php

class PSU_AR_Receivables_MiscBillingRecordIterator extends PSU_FilterIterator {
	public $payment_id;
	public $detail_code;

	public function __construct( $it, $detail_code, $payment_id = null ) {
		parent::__construct( $it );

		$this->detail_code = $detail_code;
		$this->payment_id = $payment_id;
	}//end constructor

	public function accept() {
		$el = $this->current();

		return $el->detail_code == $this->detail_code && ( $this->payment_id === null || $el->payment_id == $this->payment_id );
	}
}//end PSU_AR_Receivables_MiscBillingRecordIterator
