<?php

/**
 * A instance of a finaid award for a specific user and term.
 */
class PSU_Student_Finaid_Award_Term extends PSU_DataObject {
	public $aliases = array(
		'robprds_desc' => 'term',
		'rtvawst_desc' => 'status',
		'rfrbase_fund_title_long' => 'fund',
		'rpratrm_offer_amt' => 'offered',
		'rpratrm_accept_amt' => 'accepted',
		'rpratrm_decline_amt' => 'declined',
		'rpratrm_cancel_amt' => 'canceled',
		'rpratrm_period' => 'term_code',
		'rprawrd_authorized_amt' => 'authorized',
		'rprawrd_authorized_date' => 'authorized_date',
		'rprawrd_fund_code' => 'fund_code',
		'rprawrd_memo_amt' => 'memo',
		'rprawrd_memo_date' => 'memo_date',
		'tbbdetc_desc' => 'detail_description',
		'tbbdetc_detail_code' => 'detail_code',
		'tbbdetc_type_ind' => 'detail_type',
	);

	public function authorized_date_timestamp() {
		return strtotime( $this->authorized_date );
	}

	public function authorized_formatted() {
		return $this->value_formatted( $this->authorized );
	}

	public function offered_formatted() {
		return $this->value_formatted( $this->offered );
	}

	public function accepted_formatted() {
		return $this->value_formatted( $this->accepted );
	}

	public function declined_formatted() {
		return $this->value_formatted( $this->declined );
	}

	public function canceled_formatted() {
		return $this->value_formatted( $this->canceled );
	}

	public function memo_date_timestamp() {
		return strtotime( $this->memo_date );
	}

	public function memo_formatted() {
		return $this->value_formatted( $this->memo );
	}

	public function value_formatted( $value ) {
		return PSU_MoneyFormatter::create()->format( $value );
	}

	public function field_formatted( $field ) {
		return $this->value_formatted( $this->field( $field ) );
	}

	public function field( $field ) {
		return isset($this->$field) ? $this->$field : null;
	}

	public function has_declined() {
		return $this->canceled > 0 || $this->declined > 0;
	}
}//end class PSU_Student_Finaid_Award_Term
