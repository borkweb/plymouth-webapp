<?php

class PSU_Student_Finaid extends BannerObject {
	/**
	 * The target student's pidm.
	 */
	public $pidm;

	/**
	 * Available aid years; not student-specific.
	 */
	public static $aid_years;

	/**
	 * The aid year we are examining.
	 */
	public $aid_year;

	/**
	 * Financial aid web rules.
	 */
	public static $web_rules = array();

	/**
	 * Limiting searching to a specific term code.
	 */
	public $term_code = null;

	public function __construct( $pidm, $aid_year = null ) {
		$this->pidm = $pidm;
		$this->aid_year = $aid_year ? $aid_year : \PSU\Student::getAidYear();

		parent::__construct();
	}//end __construct

	public static function aid_years( ) {
		if( !self::$aid_years ) {
			$aid_years = new PSU_Student_Finaid_AidYears( $aid_year );
			$aid_years->load();
			self::$aid_years = $aid_years;
		}//end if

		return self::$aid_years;
	}//end aid_years

	public function fafsa_received() {
		return (bool)$this->fafsa_receive_date;
	}

	public function has_activity() {
		return $this->awards->has_awards() || $this->requirements->requirements || $this->messages->messages || $this->application->seqno;
	}//end has_activity

	public static function web_rules( $aid_year ) {
		if( !self::$web_rules[ $aid_year ] ) {
			$web_rules = new PSU_Student_Finaid_Rules( $aid_year );
			$web_rules->load();
			self::$web_rules[ $aid_year ] = $web_rules->getIterator();
		}//end if

		return self::$web_rules[ $aid_year ];
	}//end _load_web_rules

	public function _load_awards() {
		$this->data['awards'] = new PSU_Student_Finaid_Awards( $this->pidm(), $this->aid_year, $this->fund_messages );
		$this->data['awards']->load();
	}//end get_awards

	public function _load_fund_messages() {
		$this->data['fund_messages'] = new PSU_Student_Finaid_Awards_Messages( $this->pidm(), $this->aid_year );
		$this->data['fund_messages']->load();
	}//end _load_messages

	public function _load_requirements() {
		$this->data['requirements'] = new PSU_Student_Finaid_Requirements( $this->pidm(), $this->aid_year );
		$this->data['requirements']->load();
	}//end _load_requirements

	public function _load_messages() {
		$this->data['messages'] = new PSU_Student_Finaid_Messages( $this->pidm(), $this->aid_year );
		$this->data['messages']->load();
	}

	public function _load_application() {
		$this->data['application'] = new PSU_Student_Finaid_Application( $this->pidm(), $this->aid_year );
		$this->data['application']->load();
	}

	public function _load_status() {
		$this->data['status'] = PSU_Student_Finaid_Status::fetch( $this->pidm(), $this->aid_year );
	}

	public function _load_fafsa_receive_date() {
		$sql = "SELECT rorstat_appl_rcvd_date FROM rorstat WHERE rorstat_pidm = :pidm AND rorstat_aidy_code = :aidy";
		$args = array( 'pidm' => $this->pidm, 'aidy' => $this->aid_year );

		if( $date = PSU::db('banner')->GetOne( $sql, $args ) ) {
			$this->data['fafsa_receive_date'] = strtotime( $date );
		}
	}

	/**
	 * Greatest aid year for this person.
	 */
	public function _load_max_aid_year() {
		$this->data['max_aid_year'] = $this->aid_years()->max_aid_year( $this->pidm );
	}//end _load_max_aid_year

	/**
	 * Legacy function from when pidm came from studentdata dynamically.
	 * @deprecated
	 */
	public function pidm() {
		return $this->pidm;
	}
}//end class PSU_Student_Finaid
