<?php

namespace PSU\AR\PaymentPlan;

class Contract extends \PSU\AR\PaymentPlan {
	public $aliases = array();
	public $meta = null;
	public $origin = null;

	public function amount() {
		if( 'UG' == $this->type() ) {
			return $this->contract_balance + $this->funds_not_disbursed;
		} else {
			return $this->summer_contract_balance
				+ $this->fall_contract_balance
				+ $this->winter_contract_balance
				+ $this->spring_contract_balance
				+ $this->funds_not_disbursed;
		}//end else
	}//end amount

	/**
	 * prepares arguments for DML
	 */
	protected function _prep_args() {
		// this is the data prepared for binding.
		// these fields are ordered as they are in the table
		$args = array(
			'the_id' => $this->id,
			'psu_id' => $this->psu_id,
			'name' => $this->name,
			'report_group' => $this->report_group,
			'contract_balance' => $this->contract_balance,
			'account_status' => $this->account_status,
			'record_type' => $this->record_type,
			'plan_type' => $this->plan_type,
			'fund_not_disbursed' => $this->fund_not_disbursed,
			'tms_customer_number' => $this->tms_customer_number,
			'file_id' => $this->file_id,
			'date_parsed' => $this->date_parsed ? \PSU::db('banner')->BindDate( $this->date_parsed_timestamp() ) : null,
			'date_processed' => $this->date_processed ? \PSU::db('banner')->BindDate( $this->date_processed_timestamp() ) : null,
			'summer_contract_balance' => $this->summer_contract_balance,
			'fall_contract_balance' => $this->fall_contract_balance,
			'winter_contract_balance' => $this->winter_contract_balance,
			'spring_contract_balance' => $this->spring_contract_balance,
		);

		return $args;
	}//end _prep_args
}//end class
