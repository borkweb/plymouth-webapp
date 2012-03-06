<?php

class PSU_AR_AidAuthorizations extends PSU_AR_TermAggregate {
	public $pidm;

	public function __construct( $pidm, $term_code = null ) {
		parent::__construct( __CLASS__, $pidm, $term_code );
	}//end __construct

	public function get() {
		$args = array(
			'pidm' => $this->pidm,
		);

		$sql = "
			SELECT rprauth_pidm pidm,
						 rprauth_term_code term_code,
						 rfrbase_detail_code detail_code,
						 rprauth_amount amount
			FROM   rfrbase, rprauth
			WHERE  rprauth_pidm             = :pidm
				AND  rfrbase_fund_code        = rprauth_fund_code
		";

		$rset = PSU::db('banner')->Execute( $sql, $args );
		return $rset;
	}//end get
}//end PSU_AR_AidAuthorizations
