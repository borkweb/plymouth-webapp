<?php

class PSU_Student_Finaid_Rules implements IteratorAggregate {
	private $rules;

	public $aid_year;

	public function __construct( $aid_year ) {
		$this->aid_year = $aid_year;
	}

	public function load() {
		if( $this->rules ) {
			return;
		}

		$rule_row = $this->get_rules();
		$this->rules = new PSU_Student_Finaid_Rules_Web( $rule_row );
	}//end load

	public function get_rules() {
		$args = array(
			'aidy' => $this->aid_year,
		);

		$sql = "
			SELECT rorwebr_coa_ind,
						 rorwebr_need_calc_ind,
						 rorwebr_cum_loan_ind,
						 rorwebr_detail_resource_ind,
						 rorwebr_acpt_partial_amt_ind,
						 rorwebr_acpt_all_awards_ind,
						 rorwebr_resource_info_ind,
						 rorwebr_award_info_ind,
						 rorwebr_enrollment_status,
						 rorwebr_housing_status_ind,
						 rorwebr_term_zero_awrd_ind,
						 rorwebr_fund_zero_amt_ind,
						 rorwebr_resource_tab_ind,
						 rorwebr_terms_tab_ind,
						 rorwebr_award_acpt_tab_ind,
						 rorwebr_special_msg_tab_ind,
						 rorwebr_terms_cond_print_ind
				FROM rorwebr
			 WHERE rorwebr_aidy_code = :aidy
		";

		$data =  PSU::db('banner')->GetRow( $sql, $args );
		return $data;
	}//end get_rules

	public function getIterator() {
		return new ArrayIterator( $this->rules );
	}//end getIterator
}//end PSU_Student_Finaid_Rules
