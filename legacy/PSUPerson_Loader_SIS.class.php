<?php

require_once 'autoload.php';

/**
 * PSUPerson.class.php.
 *
 * Base Person Object
 *
 * &copy; 2009 Plymouth State University ITS
 *
 * @author		Matthew Batchelder <mtbatchelder@plymouth.edu>
 * @todo document $person->first_name and other property usage -jrl
 */ 
class PSUPerson_Loader_SIS extends PSUPerson_Loader implements PSUPerson_Loader_Interface
{
	public $default_load = array();
	public $data = array();
	public $priority = 10;

	public static $loaders = array(
		'account_creation_date'       => 'ad_account_info',
		'ad_roles_sql'                => 'ad_roles',
		'apdc_code'                   => 'applicant',
		'apdc_desc'                   => 'applicant',
		'admt_code'                   => 'applicant',
		'admt_desc'                   => 'applicant',
		'applicant_chkl_zack'         => 'applicant',
		'applicant_pending_invite'   => 'applicant',
		'applicant_provision_error'   => 'applicant',
		'applicant_missing_sabiden_sabnstu' => 'applicant',
		'banner_roles_sql'            => 'banner_roles',
		'birth_date'                  => 'bio',
		'certification_number'        => 'foreign',
		'citizenship_code'            => 'bio',
		'citizenship'                 => 'bio',
		'bill'                        => 'bill',
		'confidential'                => 'bio',
		'deceased'                    => 'bio',
		'deceased_date'               => 'bio',
		'employee_positions'          => 'employment',
		'ethnicity'                   => 'bio',
		'ethnicity_desc'              => 'bio',
		'flexcash_date'               => 'flexcash',
		'meals'												=> 'flexcash',
		'religion'                    => 'bio',
		'religion_desc'               => 'bio',
		'race'                        => 'bio',
		'race_desc'                   => 'bio',
		'legacy_desc'                 => 'bio',
		'ug'                          => 'cohort_data',
		'gr'                          => 'cohort_data',
		'ug_degree'                   => 'cohort_data',
		'gr_degree'                   => 'cohort_data',
		'ug_continuing_ed'            => 'cohort_data',
		'gr_continuing_ed'            => 'cohort_data',
		'ug_certificate'              => 'cohort_data',
		'gr_certificate'              => 'cohort_data',
		'community_ed'                => 'cohort_data',
		'frost'                       => 'cohort_data',
		'foreign_ssn'                 => 'foreign',
		'gpa_institution'             => 'gpa',
		'gpa_overall'                 => 'gpa',
		'gpa_transfer'    	          => 'gpa',
		'in_employee_demog'           => 'inDemographics',
		'in_student_demog'            => 'inDemographics',
		'issues'                      => 'issues',
		'legal_name'                  => 'bio',
		'lms_account_exists'          => 'hasLMSAccount',
		'login_name'                  => 'third_party_access',
		'marital_status'              => 'bio',
		'max_term'                    => 'student',
		'max_term_status'             => 'student',
		'max_term_status_code'        => 'student',
		'name_prefix'                 => 'bio',
		'name_suffix'                 => 'bio',
		'oracle_account_exists'       => 'oracle_account',
		'oracle_account_status'       => 'oracle_account',
		'password_change_date'        => 'ad_account_info',
		'pidm'                        => 'identifiers',
		'id'                          => 'identifiers',
		'login_name'                  => 'identifiers',
		'username'                    => 'identifiers',
		'sourced_id'                  => 'identifiers',
		'first_name'                  => 'identifiers',
		'middle_name'                 => 'identifiers',
		'last_name'                   => 'identifiers',
		'name_prefix'                 => 'identifiers',
		'name_suffix'                 => 'identifiers',
		'wp_id'                       => 'identifiers',
		'pin_disabled'                => 'third_party_access',
		'pin_exists'                  => 'third_party_access',
		'pin_expires'                 => 'third_party_access',
		'preferred_first_name'        => 'bio',
		'religion'                    => 'bio',
		'residential_building'        => 'residential_room_assignment',
		'residential_room_number'     => 'residential_room_assignment',
		'security_question'           => 'third_party_access',
		'sex'                         => 'bio',
		'ssn_exists'                  => 'bio',
		'username'                    => 'third_party_access',
		'usnh_id'                     => 'usnh',
		'usnh_pidm'                   => 'usnh',
		'door_access'                 => 'cardaccess',
		'door_badge_issue_num'        => 'cardaccess',
		'has_mahara_account'		  => 'mahara',
		'is_mahara_admin'			  => 'mahara',
		'has_moodle_account'		  => 'moodle',
		'is_moodle_admin'			  => 'moodle',
		'has_moodle2_account'		  => 'moodle',
		'is_moodle2_admin'			  => 'moodle',
	);

	/**
	 * addressExists
	 *
	 * addressExists Checks whether an address exists for a given pidm and address type.
	 *
	 * @access		public
	 * @param  		string $code Address type
	 * @return		boolean
	 */
	public function addressExists($code)
	{
		return (count($this->person->address[strtoupper($code)]) > 0) ? true : false;
	}//end addressExists

	public function loadBill($term_code)
	{
		$this->person->bill = new \PSU\AR\Bill($this->person, $term_code);
	}//end loadBill

	/**
	 * emailExists
	 *
	 * Checks if the given email exists
	 *
	 * @access		public
	 * @param     string $code Email Type
	 * @return		boolean
	 */
	public function emailExists($code)
	{
		return (count($this->person->email[strtoupper($code)]) > 0) ? true : false;
	}//end emailExists

	/**
	 * hasIDCard
	 *
	 * Checks if the person has an ID Card
	 *
	 * @access		public
	 * @return		boolean
	 */
	public function hasIDCard()
	{	
		if(isset($this->person->idcard_exists)) 
			return $this->person->idcard_exists;
		
		return $this->person->idcard_exists = (PSU::get('banner')->GetOne("SELECT 1 FROM spbcard WHERE spbcard_pidm = " . $this->person->pidm)) ? true : false;
	}//end hasIDCard

	/**
	 * hasSystemAccount
	 * 
	 * Determines if the given user has a systems account
	 *
	 * @param      string|int $type the field
	 * @param      string $value the value
	 * @return     boolean
	 */
	function hasSystemAccount($type = 'pidm', $value = null, $where = '1=1')
	{
		$value = $value ? $value : $this->person->pidm;
		$sql = "SELECT 1 FROM USER_DB WHERE (user_active=1 OR user_alumni=1) AND $type = ? AND $where";
		$has_account = (bool)PSU::db('userinfo')->GetOne($sql, array($value));
		return $has_account;
	}//end hasSystemAccount

	/**
	 * Interface to load system_account_exists
	 */
	function _load_system_account_exists() {
		$this->person->system_account_exists = $this->hasSystemAccount();
	}

	/**
	 * hasLMSAccount
	 * 
	 * Determines if the given user has an LMS account
	 *
	 * @return     boolean
	 */
	function hasLMSAccount()
	{
		if( isset($this->person->lms_account_exists) ) return $this->person->lms_account_exists;
		
		return $this->person->lms_account_exists = PSU::get('lms')->getUserInfo($this->person->username) ? true : false;
	}//end hasLMSAccount

	/**
	 * Return the URL to the user's ID card.
	 * @param string $size one of: thumbnail, orig
	 */
	public function idcard( $size = 'thumbnail' )
	{
		$url = 'https://www.plymouth.edu/webapp/idcard/u/' . $this->person->username;

		if($size == 'orig')
		{
			$url .= '/orig/';
		}	

		return $url;
	}//end idcard

	/**
	 * inDemographics
	 *
	 * Checks if the person is in the given demographic
	 *
	 * @access		public
	 * @param     string $demog Which demographic to check
	 * @return		boolean
	 */
	public function inDemographics($demog)
	{	
		switch($demog)
		{
			case 'employee':
				if(isset($this->person->in_employee_demog))
					return $this->person->in_employee_demog;
				
				return $this->person->in_employee_demog = (PSU::get('banner')->GetOne("SELECT 1 FROM ps_as_employee_demog WHERE pidm = " . $this->person->pidm)) ? true : false;
			break;
			case 'student':
				if(isset($this->person->in_student_demog))
					return $this->person->in_student_demog;
				
				return $this->person->in_student_demog = (PSU::get('banner')->GetOne("SELECT 1 FROM ps_as_student_demographics WHERE pidm = " . $this->person->pidm)) ? true : false;
			break;
		}//end switch
	}//end inDemographics

	/**
	 * Checks if the person is an active student
	 * @deprecated  Use $person->student->active instead.
	 *
	 * @return		boolean
	 */
	public function isActiveStudent()
	{
		return $this->person->student->active;
	}//end isActiveStudent

	/**
	 * Checks if the person is eligible to register
	 * @deprecated  Use $person->student->eligible_to_register instead.
	 *
	 * @return		boolean
	 */
	public function isEligibleToRegister()
	{
		return $this->person->student->eligible_to_register;
	}//end isEligibleToRegister

	/**
	 * Verify that this loader is valid for the given identifier.
	 */
	public function loader_preflight( $identifier ) {
		$identifier = ($identifier) ? $identifier : $this->person->initial_identifier;
		
		if(preg_match('/^[0-9]{9}$/', $identifier))
		{
			$identifier_type = 'id';
		}//end if
		elseif(preg_match('/^[0-9]+$/', $identifier))
		{
			$identifier_type = 'pid';
		}//end if
		elseif(preg_match('/^p[0-9][a-z]{7}$/', $identifier))
		{
			$identifier_type = 'wp_id';
		}//end if
		else
		{
			$identifier_type = 'login_name';
		}//end else

		$identifiers = PSU::get('idmobject')->getIdentifier($identifier, $identifier_type, 'all');

		if( $identifiers ) {
			$this->identifiers_cache = $identifiers;
			$this->person->identifier_type = $identifier_type;
			return true;
		}

		return false;
	}//end loader_preflight

	/**
	 *
	 */
	public function pendingCreation() {
		$sql = "SELECT 1 FROM USER_TEMP WHERE pidm = ?";
		$result = (bool) \PSU::db('userinfo')->GetOne( $sql, $this->person->pidm );

		if( \PSU::db('userinfo')->ErrorMsg() > 0 ) {
			trigger_error( \PSU::db('userinfo')->ErrorMsg(), E_USER_WARNING );
			return null;
		}

		return $result;
	}//end pendingCreation

	/**
	 * returns whether or not the person is pending in an LDI sync
	 */
	public function pendingLDISync() {
		static $pending;

		if( $pending !== null ) return $pending;

		$sql = "SELECT count(1)
			        FROM gobeqrc
							     INNER JOIN goreqrc
									 	ON goreqrc_seqno = gobeqrc_seqno
										AND goreqrc_parm_name = 'SOURCEDID1'
										AND goreqrc_parm_value = :sourced_id
		        WHERE gobeqrc_eqnm_code = 'LDIPERSON'
		          AND gobeqrc_status_ind <> 2";
		return $pending = PSU::db('banner')->GetOne( $sql, array( 'sourced_id' => $this->person->sourced_id ) );	
	}//end pendingLDISync

	/**
	 * executes a logged LDI sync
	 *
	 * @param $source \b source of ldi sync for logging purposes (typically a username)
	 */
	public function sync_ldi( $source ) {
		PSU::get('idmobject')->syncAdmitPin( $this->person->pidm );

		$this->issues;
		// if the username appears to be out of sync, update GOBTPAC to fire off the ldap_user trigger (psu.tr_gobtpac_ldap)
		if( $this->issues['username_sync'] ) {
			PSU::get('log/api/' . $source)->write('Synchronizing username', $this->person->login_name);

			PSU::get('idmobject')->trigger_banner_username_sync( $this->person->pidm );
		}//end if

		$result_ldi = PSU::get('idmobject')->LDISync($this->person->pidm,'all');
		
		return PSU::get('log/api/' . $source)->write('LDI Sync', $this->person->login_name);
	}//end sync_ldi

	/**
	 * returns the password change date
	 */
	public function _load_ad_account_info()
	{
		$ad_info = PSU::get('ad')->user_info($this->person->login_name, array('pwdlastset','createtimestamp'));
		
		if(is_array($ad_info) && count($ad_info)>0)
		{
			$ad_stamp = null;
		
			if(isset($ad_info[0]['pwdlastset'][0]))
			{
				// 116444736000000000 = 10000000 * 60 * 60 * 24 * 365 * 369 + 89 leap days huh.
				$ad_stamp = round(($ad_info[0]['pwdlastset'][0]-116444736000000000)/10000000);
			}
		
			$this->person->data['password_change_date'] = $ad_stamp;
				
			if(isset($ad_info[0]['createtimestamp'][0]))
			{
				$zulu = $ad_info[0]['createtimestamp'][0];
				$year = substr($zulu, 0, 4);
				$month = substr($zulu, 4, 2);
				$day = substr($zulu, 6, 2);
				$hour = substr($zulu, 8, 2);
				$minute = substr($zulu, 10, 2);

				$date_time = $month.'/'.$day.'/'.$year.' '.$hour.':'.$minute;

				$timezone = new DateTimeZone('America/New_York');
				$ad_timezone = new DateTimeZone('UTC');

				$ts = new DateTime( $date_time, $ad_timezone );
				$ts->setTimezone( $timezone );

				$this->person->data['account_creation_date'] = $ts->getTimestamp();
			}//end if
		}//end if
	}//end _load_ad_account_info

	/**
	 * _load_ad_roles
	 * 
	 * loads portal roles for the given person
	 *
	 * @access		protected
	 */
	protected function _load_ad_roles()
	{
		$roles = array();
		
		$roles = PSU::get('ad')->user_groups($this->person->username);
		if(is_array($roles)) 
		{
			$roles = array_unique($roles);
			natcasesort($roles);
			
			$this->person->data['ad_roles'] = array();
		
			foreach($roles as $role)
			{
				$description = isset($this->person->role_descriptions['ad_roles'][$role]) ? $this->person->role_descriptions['ad_roles'][$role] : '';
				$this->person->data['ad_roles'][$role] = $this->person->role_descriptions['ad'][$role];
			}//end foreach

			$this->person->data['ad_roles_sql'] = sprintf("('%s')", implode("','", $roles));
		}//end if
	}//end _load_ad_roles

	/**
	 * _load_address
	 * 
	 * loads address info for the person
	 *
	 * @access		protected
	 */
	public function _load_address()
	{
		$address_data = array();
		
		$sql = "BEGIN :c_cursor := gb_address.f_query_all(:pidm); END;";

		if($results = PSU::get('banner')->ExecuteCursor($sql, 'c_cursor', array('pidm' => $this->person->pidm)))
		{
			foreach($results as $address)
			{
				$address = psu::cleanKeys('spraddr_', '', $address);
				if($address['status_ind'] != 'I' && (!$address['to_date'] || time() <= strtotime($address['to_date'])))
				{
					$address_data[$address['atyp_code']][] = new \PSU\Person\Address($address);
				}//end if
			}//end foreach
		}//end if
		
		$this->person->address = $address_data;
	}//end _load_address

	/**
	 * Load advisees
	 */
	public function _load_advisees()
	{
		$this->person->data['advisees'] = array();

		$sql ="SELECT DISTINCT sgradvr_pidm, 
									sgradvr_advr_code,
									sgradvr_term_code_eff,
									nvl((SELECT 'Y' FROM psu.v_student_account_active WHERE pidm = sgradvr_pidm), 'N') as active_advisee
						 FROM sgradvr a
						WHERE a.sgradvr_advr_pidm = :pidm
							AND a.sgradvr_term_code_eff =
									 (SELECT MAX (b.sgradvr_term_code_eff)
											FROM sgradvr b
										 WHERE b.sgradvr_pidm = a.sgradvr_pidm
											 AND b.sgradvr_term_code_eff <= :term)";

		if($results = PSU::db('banner')->Execute($sql, array('pidm' => $this->person->pidm, 'term' => $this->person->term_code)))
		{
			foreach($results as $row)
			{
				$row = PSU::cleanKeys('sgradvr_', '', $row);
				$code = $row['advr_code'];
				$active = $row['active_advisee'];
				unset($row['advr_code']);
				unset($row['active_advisee']);

				if($code == 'ACAD')
				{
					$row['begin_term'] = $row['term_code_eff'];
					unset($row['term_code_eff']);
				}//end if

				if($code == 'ACAD' && $active == 'Y')
				{
					$this->person->data['advisees']['current'][$row['pidm']] = $row;
				}//end if
				else
				{
					if($code == 'PACA')
					{
						$sql = "SELECT sgradvr_term_code_eff 
							        FROM sgradvr a 
										 WHERE a.sgradvr_advr_pidm = :pidm 
										   AND a.sgradvr_pidm = :student_pidm
											 AND a.sgradvr_term_code_eff = (SELECT MAX(b.sgradvr_term_code_eff) 
                                                        FROM sgradvr b 
                                                       WHERE b.sgradvr_pidm = a.sgradvr_pidm
                                                         AND b.sgradvr_term_code_eff <= :term
                                                         AND b.sgradvr_advr_pidm = a.sgradvr_advr_pidm
                                                         AND b.sgradvr_advr_code = 'ACAD')";
						$row['begin_term'] = PSU::db('banner')->GetOne($sql, array('pidm' => $this->person->pidm, 'student_pidm' => $row['pidm'], 'term' => $this->person->term_code));
						$row['end_term'] = $row['term_code_eff'];
						unset($row['term_code_eff']);
					}//end if

					$this->person->data['advisees']['past'][$row['pidm']] = $row;
				}//end if
			}//end foreach
		}//end if
	}//end _load_advisees

	/**
	 * Load results of the first-year advising survey.
	 * @deprecated Use $person->student->ug->advising_survey instead.
	 */
	public function _load_advising_survey()
	{
		if( $this->person->student->ug ) {
			$this->person->data['advising_survey'] = $this->person->student->ug->advising_survey;
		}//end if
	}//end _load_advising_survey

	/**
	 * lazy loads an array of active advisor records
	 * @deprecated Use $person->student->gr/ug/etc->advisors instead.
	 */
	function _load_advisors()
	{
		$this->person->data['advisors']=array();

		if( $this->person->student->gr ) {
			$this->person->advisors = $this->person->student->gr->advisors;
		} elseif( $this->person->student->ug ) {
			$this->person->advisors = $this->person->student->ug->advisors;
		}//end else
	}//end _load_advisors

	protected function _load_alumni()
	{
		$this->person->data['alumni']=new \PSU\Alumni($this->person->pidm);
	}

	/**
	 * Load various pieces of applicant data.
	 */
	protected function _load_applicant()
	{
		$args = array('pidm' => $this->person->pidm);

		$sql = "
			SELECT apdc_code, apdc_date, admt_code, stvapdc_desc apdc_desc, stvadmt_desc admt_desc
			FROM
				v_ug_app a LEFT JOIN
				stvapdc ON a.apdc_code = stvapdc.stvapdc_code
				LEFT JOIN stvadmt ON a.admt_code = stvadmt.stvadmt_code
			WHERE pidm = :pidm
		";

		$row = PSU::db('banner')->GetRow($sql, $args);
		$this->person->data = $this->person->data + $row;

		if( $this->person->data['apdc_date'] ) {
			$this->person->data['apdc_date'] = strtotime($this->person->data['apdc_date']);
		}

		require_once 'ugApplicants.class.php';
		$this->person->data['applicant_email'] = ugApplicants::getApplicantEmail( $this->person->pidm );

		// has admissions flagged them for application acknowledgement?
		$sql = "SELECT 1 FROM v_ug_app_chkl_zack WHERE pidm = :pidm";
		$this->person->data['applicant_chkl_zack'] = (bool)PSU::db('banner')->GetOne($sql, $args);

		// have they been flagged for a missing email?
		$sql = "SELECT reason FROM app_missing_email WHERE pidm = ? AND resolved = 0";
		$this->person->data['applicant_provision_error'] = PSU::db('myplymouth')->GetOne($sql, $args);

		// does the user have a sabiden/sabnstu record?
		$sql = "SELECT 1 FROM sabnstu, sabiden WHERE sabiden_pidm = :pidm AND sabiden_aidm = sabnstu_aidm";
		$this->person->data['applicant_missing_sabiden_sabnstu'] = !((bool) PSU::db('banner')->GetOne($sql, $args));

		// is user still in the invite pool?
		$sql = "SELECT 1 FROM v_ug_app_unsent_myp_invite WHERE pidm = :pidm";
		$this->person->data['applicant_pending_invite'] = (bool)PSU::db('banner')->GetOne($sql, $args);
	}//end _load_applicant

	protected function _load_applicant_invite_timestamp() {
		$value = false;

		if( $this->person->is_applicant ) {
			$args = array(
				'pidm' => $this->person->pidm,
			);

			$sql = "
				SELECT gurmail_date_init
				FROM v_ug_app LEFT JOIN gurmail ON pidm = gurmail_pidm AND term_code_entry = gurmail_term_code
				WHERE gurmail_pidm = :pidm AND gurmail_letr_code = 'AA_MYP_INVITE'
			";

			$value = PSU::db('banner')->GetOne($sql, $args);

			$value = $value ? strtotime( $value ) : false;
		}

		$this->person->data['applicant_invite_timestamp'] = $value;
	}//end _load_applicant_invite_timestamp

	/**
	 * Load the applicant pin.
	 */
	protected function _load_applicant_pin()
	{
		$sql = "SELECT pin FROM applicants WHERE pidm = ?";
		$pin = PSU::db('myplymouth')->GetOne( $sql, array($this->person->pidm) );

		if( ! $pin ) {
			$pin = false;
		}

		$this->person->data['applicant_pin'] = $pin;
	}//end _load_applicant_pin

	/**
	 * Load the of at-risk indicators for this student. Keys are the indicators
	 * (ie. low_gpa), values are any details, or true if now more details can
	 * be provided.
	 */
	public function _load_atrisk()
	{
		$this->person->data['atrisk'] = array();

		$sql = "SELECT * FROM v_student_ug_fy_at_risk WHERE pidm = :pidm AND at_risk = 'Y'";
		$args = array('pidm' => $this->person->pidm);

		$row = PSU::db('banner')->GetRow($sql, $args);

		if( empty($row) ) {
			return;
		}

		// only set keys where the value is true. lets us do a quick count($p->atrisk) to determine if use
		// has any at-risk indicators
		if( $row['low_gpa'] == 'Y' ) $this->person->data['atrisk']['low_gpa'] = true;
		if( $row['failing_grade'] == 'Y'  ) $this->person->data['atrisk']['failing_grade'] = true;
	}//end atrisk

	/**
	 * _load_banner_roles
	 * 
	 * loads address info for the person
	 *
	 * @access		protected
	 */
	protected function _load_banner_roles()
	{
		$roles = array();
		
		$roles = PSU::get('idmobject')->getAllBannerRoles($this->person->pidm);
			
		if($roles)
		{
			$this->person->data['banner_roles'] = array();
			
			foreach($roles as $role)
			{
				$description = isset($this->person->role_descriptions['banner'][$role]) ? $this->person->role_descriptions['banner'][$role] : '';
				$this->person->data['banner_roles'][$role] = $description;
			}//end foreach

			if(is_array($this->person->data['banner_roles']))
			{
				ksort($this->person->data['banner_roles']);
			}//end if

			$this->person->data['banner_roles_sql'] = sprintf("('%s')", implode("','", $roles));
		}//end if
	}//end _load_banner_roles

	/**
	 * initializes the person's bill
	 *
	 * @access		protected
	 */
	protected function _load_bill()
	{
		$this->person->bill = new \PSU\AR\Bill($this->person, $this->person->term_code);
	}//end _load_bill

	/**
	 * loads data from Banner Bio API
	 *
	 * @access		protected
	 */
	protected function _load_bio()
	{
		$data = array();
		
		$sql="SELECT  spbpers.*, 
			            stvrelg_desc religion,
									stvethn_desc ethnicity,
									stvcitz_desc citizenship,
									gorrace_desc race,
									stvlgcy_desc legacy_desc
						FROM  spbpers,
									gorprac,
									gorrace,
									stvrelg,
									stvethn,
									stvlgcy,
									stvcitz
					 WHERE  spbpers_pidm = :pidm
						 AND  spbpers_relg_code = stvrelg_code(+)
						 AND  spbpers_lgcy_code = stvlgcy_code(+)
						 AND  spbpers_citz_code = stvcitz_code(+)
						 AND  spbpers_pidm = gorprac_pidm(+)
						 AND  gorprac_race_cde = gorrace_race_cde(+)
						 AND  spbpers_ethn_code = stvethn_code(+)";
		if($row = PSU::db('banner')->GetRow($sql, array('pidm' => $this->person->pidm)))
		{
			$data = psu::cleanKeys('spbpers_', 'r_', $row);
		}//end if

		$this->person->birth_date = strtotime($data['r_birth_date']);
		$this->person->ethnicity_code = $data['r_ethn_code'];
		$this->person->ethnicity = $data['ethnicity'];
		$this->person->citizenship_code = $data['r_citz_code'];
		$this->person->citizenship = $data['citizenship'];
		$this->person->race = $data['race'];
		$this->person->marital_status = $data['r_mrtl_code'];
		$this->person->religion_code = $data['r_relg_code'];
		$this->person->religion = $data['religion'];
		$this->person->sex = $data['r_sex'];
		$this->person->confidential = ($data['r_confid_ind'] == 'Y') ? true : false;
		$this->person->deceased = ($data['r_dead_ind'] == 'Y') ? true : false;
		if($this->person->deceased)
		{
			$this->person->deceased_date = strtotime($data['r_dead_date']);
		}//end if
		
		$this->person->ssn_exists = ($data['r_ssn']) ? true : false;
		unset($data['r_ssn']);
		$this->person->legal_name = $data['r_legal_name'];
		$this->person->preferred_first_name = $data['r_pref_first_name'];
		$this->person->name_prefix = $data['r_name_prefix'];
		$this->person->name_suffix = $data['r_name_suffix'];
		$this->person->confirmed_race = $data['r_confirmed_re_cde'] == 'Y' ? true : false;
		$this->person->confirmed_ethnicity = $data['r_confirmed_re_cde'] == 'Y' ? true : false;
		$this->person->legacy_desc = $data['legacy_desc'];
	}//end _load_bio

	
	/**
	 * loads card access data
	 */
	protected function _load_cardaccess()
	{
		require_once 'CardAccess.class.php';
		$this->person->data['door_access'] = CardAccess::doorAccess($this->person->id);
		$this->person->data['door_badge_issue_num'] = CardAccess::badgeIssueNumber($this->person->id);
	} // end function _load_cardaccess


	/**
	 * loads student cohort data
	 */
	protected function _load_cohort_data()
	{
		$sql = "SELECT * FROM v_stu_cohort WHERE pidm = :pidm";
		$data = PSU::db('banner')->GetRow($sql, array('pidm' => $this->person->pidm));

		$this->person->ug = $data['ug'] == 'Y' ? true : false;
		$this->person->gr = $data['gr'] == 'Y' ? true : false;
		$this->person->ug_degree = $data['ug_degree'] == 'Y' ? true : false;
		$this->person->gr_degree = $data['gr_degree'] == 'Y' ? true : false;
		$this->person->ug_continuing_ed = $data['ug_continuing_ed'] == 'Y' ? true : false;
		$this->person->gr_continuing_ed = $data['gr_continuing_ed'] == 'Y' ? true : false;
		$this->person->ug_certificate = $data['ug_certificate'] == 'Y' ? true : false;
		$this->person->gr_certificate = $data['gr_certificate'] == 'Y' ? true : false;
		$this->person->community_ed = $data['community_ed'] == 'Y' ? true : false;

		$this->person->frost = PSU::db('banner')->GetOne("SELECT count(*) FROM v_student_frost WHERE pidm = :pidm", array('pidm' => $this->person->pidm)) ? true : false;
	}//end _load_cohort_data

	/**
	 * Get a combined list of myP/Banner roles.
	 */
	protected function _load_combined_roles()
	{
		$this->person->data['combined_roles'] = array();

		foreach( (array) $this->person->banner_roles as $role => $description )
		{
			if( !isset( $this->person->data['combined_roles'][$role] ) )
			{
				$this->person->data['combined_roles'][$role] = array(
					'description' => $description,
					'portal' => false,
					'banner' => true
				);
			}
			else
			{
				$this->person->data['combined_roles'][$role]['banner'] = true;
			}
		}

		ksort( $this->person->data['combined_roles'] );
	}//end _load_combined_roles

	/**
	 * load all courses the instructor has been enrolled it
	 */
	protected function _load_courses_instructor()
	{
		$this->person->courses_instructor = array();
		$sql = "SELECT ssbsect.*
		          FROM sirasgn,ssbsect,stvterm
		         WHERE sirasgn_pidm = :pidm
		           AND sirasgn_term_code = ssbsect_term_code
		           AND sirasgn_crn = ssbsect_crn
		           AND ssbsect_term_code = stvterm_code
		           AND stvterm_start_date >= :start_date
		         ORDER BY ssbsect_term_code";

		$args = array(
			'pidm' => $this->person->pidm,
			'start_date' => $this->person->data['ranges']['since'] ? date('Y-m-d', $this->person->data['ranges']['since']) : date('Y-m-d', strtotime('-1 years')) 
		);

		if($results = PSU::db('banner')->Execute($sql, $args))
		{
			foreach($results as $row)
			{
				$row = PSU::cleanKeys('ssbsect_', '', $row);
				$this->person->data['courses_instructor'][$row['term_code']][$row['crn']] = new \PSU\Course\Section($row['crn'], $row['term_code'], $row);
			}//end foreach
		}//end if	
	}//end _load_courses_instructor

	/**
	 * load all courses the student has been enrolled it
	 * @deprecated  Use the $person->student->courses instead
	 */
	protected function _load_courses_student()
	{
		if( $this->person->student ) {
			$this->person->courses_student = $this->person->student->courses;
		}//end if
	}//end _load_courses_student

	/**
	 * retrieves curriculumn information for person
	 * @deprecated  Use the $person->student->curriculum->gpa instead
	 */
	public function _load_curriculum()
	{
		if( $this->person->student->gr ) {
			$this->person->curriculum = $this->person->student->gr->curriculum;
		} elseif( $this->person->student->ug ) {
			$this->person->curriculum = $this->person->student->ug->curriculum;
		}//end else
	}//end _load_curriculum

	public function _load_department() {
		$this->person->data['department'] = null;

		$sql = "SELECT attribute FROM psu_identity.person_attribute WHERE pidm = :pidm AND type_id = 4";
		$this->person->data['department'] = PSU::db('banner')->GetOne( $sql, array( 'pidm' => $this->person->pidm) );
	}//end _load_department
	protected function _load_email()
	{
		$email_data = array();
		
		$sql = "BEGIN :c_cursor := gb_email.f_query_all(:pidm, '%', '%'); END;";
		
		if($results = PSU::get('banner')->ExecuteCursor($sql, 'c_cursor', array('pidm' => $this->person->pidm)))		
		{
			foreach($results as $email)
			{
				$email = psu::cleanKeys('goremal_', '', $email);
				$email_data[$email['emal_code']][] = new \PSU\Person\Email($email);
			}//end foreach
		}//end if
		
		$this->person->data['email'] = $email_data;
	}//end _load_email

	/**
	 * lazy load employment info
	 */
	public function _load_employment() {
		$this->person->data['employee_positions'] = array();
		$sql = "SELECT position_code, organization_title, classification FROM v_hr_psu_employee_active WHERE pidm = :pidm";
		if( $results = PSU::db('banner')->Execute( $sql, array('pidm' => $this->person->pidm) ) ) {
			foreach( $results as $row ) {
				$this->person->data['employee_positions'][ $row['position_code'] ] = $row;
			}//end foreach
		}//end if
	}//end _load_employment

	protected function _load_flexcash() {
		$sql = "
			SELECT value, 
						 flag,
			       activity_date 
				FROM flexcash 
			 WHERE pidm = :pidm 
				 AND flag IN (3, 4) 
		";
		$rows = PSU::db('banner')->GetAll( $sql, array('pidm' => $this->person->pidm));

		foreach( $rows as $row ) {
			if( $row['flag'] == 3 ) {
				$what = 'board_flexcash';
			} else {
				$what = 'campus_flexcash';
			}//end if

			$what_date = $what . '_date';

			$this->person->{$what} = $row['value'];
			$this->person->{$what_date} = strtotime($row['activity_date']);
		}//end foreach

		$this->person->flexcash = $this->person->board_flexcash + $this->person->campus_flexcash;
		$this->person->flexcash_date = $this->person->board_flexcash_date ?: $this->person->campus_flexcash_date;

		$this->person->meals = PSU::db('banner')->GetOne("SELECT value FROM flexcash WHERE pidm = :pidm AND flag = 1", array('pidm' => $this->person->pidm));
	}//end _load_flexcash

	/**
	 * loads data from Banner Bio API
	 *
	 * @access		protected
	 */
	protected function _load_foreign()
	{
		$data = array();
		
		$sql = "SELECT decode(gobintl_cert_number, NULL, NULL, gobintl_cert_number) as cert_number, 
		               gobintl_foreign_ssn as foreign_ssn 
		          FROM gobintl 
		         WHERE gobintl_pidm = :pidm";
		
		if($data = PSU::get('banner')->GetRow($sql, array('pidm' => $this->person->pidm)))		
		{
			$this->person->certification_number = $data['cert_number'];
			$this->person->foreign_ssn = $data['foreign_ssn'];
		}//end if
	}//end _load_foreign

	/**
	 * loads former last names
	 */
	protected function _load_former_last_names(){
		$sql = "SELECT DISTINCT spriden_last_name FROM spriden WHERE spriden_pidm = :pidm AND spriden_last_name <> :last_name";
		$this->person->former_last_names = psu::db('banner')->GetCol($sql, array( 'pidm' => $this->person->pidm, 'last_name' => $this->person->last_name));
	}//end _load_former_last_names

	/**
	 * lazy loads gpa information
	 * @deprecated  Use the $person->student->ug/gr/etc->gpa instead
	 */
	function _load_gpa()
	{
		$this->person->gpa = 0;
		$this->person->gpa_overall = 0;
		$this->person->gpa_institution = 0;
		$this->person->gpa_transfer= 0 ;
		
		if( $this->person->student->gr ) {
			$this->person->gpa = $this->person->student->gr->gpa;
			$this->person->gpa_overall = $this->person->student->gr->gpa_overall;
			$this->person->gpa_institution = $this->person->student->gr->gpa_institution;
			$this->person->gpa_transfer= $this->person->student->gr->gpa_transfer;
		} elseif( $this->person->student->ug ) {
			$this->person->gpa = $this->person->student->ug->gpa;
			$this->person->gpa_overall = $this->person->student->ug->gpa_overall;
			$this->person->gpa_institution = $this->person->student->ug->gpa_institution;
			$this->person->gpa_transfer= $this->person->student->ug->gpa_transfer;
		}//end else
	}//end getOverallGPA


	/**
	 * lazy loads idcard issue num
	 */
	protected function _load_idcard_issue_num()
	{
		$this->person->data['idcard_issue_num'] = PSU::db('banner')->GetOne("SELECT spbcard_issue_number FROM spbcard WHERE spbcard_pidm=:pidm", array('pidm'=>$this->person->pidm));
	} // end function _load_idcard_issue_num


	/**
	 * _load_identifiers
	 * 
	 * loads base identifier information
	 *
	 * @access		protected
	 */
	protected function _load_identifiers($identifier = null)
	{
		$this->person->data['pidm'] = $this->identifiers_cache['pid'];
		$this->person->data['id'] = $this->identifiers_cache['psu_id'];
		$this->person->data['login_name'] = $this->identifiers_cache['login_name'];
		$this->person->data['username'] = $this->identifiers_cache['username'];
		$this->person->data['sourced_id'] = $this->identifiers_cache['sourced_id'];
		$this->person->data['first_name'] = $this->identifiers_cache['first_name'];
		$this->person->data['middle_name'] = $this->identifiers_cache['middle_name'];
		$this->person->data['last_name'] = $this->identifiers_cache['last_name'];
		$this->person->data['name_prefix'] = $this->identifiers_cache['name_prefix'];
		$this->person->data['name_suffix'] = $this->identifiers_cache['name_suffix'];
		$this->person->data['wp_id'] = $this->person->data['wpid'] = $this->identifiers_cache['wp_id'];
		$this->person->data['has_ad'] = (bool)$this->identifiers_cache['ad'];
	}//end _load_identifiers

	public function _load_is_applicant() {
		$sql = "SELECT 1 FROM gorirol WHERE gorirol_pidm = :pidm AND gorirol_role = 'UG_APP' AND gorirol_role_group = 'INTCOMP'";

		$this->person->is_applicant = (bool) \PSU::db('banner')->GetOne( $sql, array( 'pidm' => $this->person->pidm ) );
	}//end _load_is_applicant

	public function _load_immutable_id() {
		if( $this->person->login_name ) {
			$immid = PSU::db('luminisportal')->getImmID( $this->person->login_name );
			if( $immid ) {
				$this->person->immutable_id = $immid;
			}//end if
		}//end if
	}//end _load_immutable_id
	
	public function _load_issues()
	{
		$this->person->issues = array();
		$this->person->getIssues();
	}//end _load_isues

	/**
	 * Load hardware information.
	 */
	public function _load_hardware()
	{
		require_once('PSUHardware.class.php');
		$this->person->hardware = new PSUHardware( $this->person );
	}//end _load_hardware

	/**
	 * Load boolean showing Zimbra account status.
	 */
	public function _load_has_zimbra()
	{
		$this->person->has_zimbra = null;

		try {
			$this->person->has_zimbra = PSU::get('zimbraadmin')->accountExists( $this->person->login_name );
		} catch( Exception $e ) {
			// leave it null.
			// @todo logging?
		}
	}//end _load_has_zimbra

	/**
	 *_load_notes
	 *
	 *Loads the notes attached to the person
	 *
	 */
	function _load_notes(){
		$notes = new \PSU\Person\Notes( $this->person->data['wp_id'] );
		$notes->load();
		$this->person->data['notes']  = $notes;
		$this->person->notes = $notes; 
	}//end _load_notes
	/**
	 * _load_mahara
	 *
	 * loads the information about a user in Mahara
	 */
	function _load_mahara() {	

		//Retrieve a users information from mahara, and set variables as necessary...
		$mahara_info = PSU::db('mahara')->GetRow('SELECT email, admin FROM usr WHERE username = ?', array( $this->person->username ));
		$this->person->has_mahara_account = (bool)$mahara_info['email'];
		$this->person->is_mahara_admin = (bool)$mahara_info['admin'];

	}//end _load_mahara

	/**
	 * _load_moodle
	 *
	 * loads the information about the user in both Moodle instances
	 *
	 */
	function _load_moodle() {
		$this->person->has_moodle_account = false;
		$this->person->has_moodle2_account = false;
		$this->person->is_moodle_admin = false;
		$this->person->is_moodle2_admin = false;

		$args = array(
			$this->person->username,
		);

		//Does the user have a Moodle 1.9 account?
		$this->person->has_moodle_account = (bool) PSU::db('moodle')->GetOne('SELECT 1 FROM mdl_user WHERE username = ?', $args );

		//If they do, are they an admin?
		if( $this->person->data['has_moodle_account'] ) {
			$sql = "SELECT DISTINCT a.roleid
					FROM mdl_role_assignments a
					     JOIN mdl_user u
					       ON u.id = a.userid
								AND u.username = ?
				WHERE a.roleid = 1
			";

			$results = PSU::db('moodle')->GetOne( $sql, $args );

			$this->person->is_moodle_admin = (bool) $results;
		}//end if

		//Does the user have a Moodle 2 account?
		$mdl2_row = PSU::db('moodle2')->GetRow('SELECT * FROM mdl_user WHERE username = ?', $args ); 
		$this->person->has_moodle2_account = (bool)$mdl2_row['email'];

		//If they do, are they an admin?
		if( $this->person->data['has_moodle2_account'] ) {
			$mdl2_admins = explode(',', PSU::db('moodle2')->GetOne("SELECT value FROM mdl_config WHERE name = 'siteadmins'"));
			$this->person->is_moodle2_admin = (in_array($mdl2_row['id'], $mdl2_admins))?true:false;
		}//end if
	}//end _load_moodle2

	/**
	 * loads oracle accoutn information
	 */
	public function _load_oracle_account() {
		$this->person->oracle_account_exists = false;
		if( $status = PSU::db('banner')->GetOne("SELECT account_status FROM sys.dba_users WHERE username = upper(:username)", array('username' => $this->person->username)) ) {
			$this->person->oracle_account_exists = true;
			$this->person->oracle_account_status = $status;
		}//end if
	}//end _load_oracle_account

	/**
	 * Return the number of open Call Log tickets for a user.
	 */
	function _load_tickets_open() {
		$sql = "
			SELECT COUNT(*)
			FROM call_log AS l JOIN 
				(SELECT call_id, MAX(id) AS id FROM call_history GROUP BY call_id)
			AS h ON l.call_id = h.call_id JOIN call_history AS h2 ON h.id = h2.id
			WHERE pidm = {$this->person->pidm} AND call_status = 'open'
		";

		$this->person->tickets_open = PSU::db('calllog')->GetOne($sql);
	}//end _load_open_ticket_count

	/**
	 * _load_phone
	 * 
	 * loads phone info for the person
	 *
	 * @access		protected
	 */
	public function _load_phone()
	{
		$phone_data = array();
		
		$sql = "BEGIN :c_cursor := gb_telephone.f_query_all(:pidm); END;";
		
		if($results = PSU::get('banner')->ExecuteCursor($sql, 'c_cursor', array('pidm' => $this->person->pidm)))		
		{
			foreach($results as $phone)
			{
				$phone = psu::cleanKeys('sprtele_', '', $phone);
				if($phone['status_ind'] != 'I')
				{
					$phone_data[$phone['tele_code']][] = new PSUPhone($phone);
				}//end if
			}//end foreach
		}//end if
		
		$this->person->phone = $phone_data;
	}//end _load_phone

	/**
	 * _load_pin
	 * 
	 * loads Banner PIN
	 *
	 * @access		protected
	 */
	protected function _load_pin()
	{
		$this->person->pin = PSU::db('banner')->GetOne("SELECT gobtpac_pin FROM gobtpac WHERE gobtpac_pidm = :pidm", array('pidm' => $this->person->pidm));
	}//end _load_pin

	/**
	 * _load_portal_roles
	 * 
	 * loads portal roles for the given person
	 *
	 * @access		protected
	 */
	protected function _load_portal_roles()
	{
		$roles = array();
		
		$roles = PSU::get('luminisportal')->getRoles($this->person->login_name);
		
		if($roles)
		{
			$this->person->data['portal_roles'] = array();
			natcasesort($roles);

			foreach($roles as $role)
			{
				$description = isset($this->person->role_descriptions['banner'][$role]) ? $this->person->role_descriptions['banner'][$role] : '';
				$this->person->data['portal_roles'][$role] = $description;
			}//end foreach
		}//end if
	}//end _load_portal_roles

	/**
	 * loads the person's residential building if it exists
	 */
	protected function _load_residential_room_assignment()
	{
    $sql = "SELECT sl.slrrasg_bldg_code, sl.slrrasg_room_number
		          FROM slrrasg sl
		         WHERE sl.slrrasg_pidm = :pidm
		           AND sl.slrrasg_ascd_code = 'AC'
		           AND sl.slrrasg_term_code = :term";

		$row = PSU::db('banner')->GetRow($sql, array('pidm' => $this->person->pidm, 'term' => $this->person->term_code));

		$this->person->residential_building = $row['slrrasg_bldg_code'];
		$this->person->residential_room_number = $row['slrrasg_room_number'];
	}//end _load_residential_building

	/**
	 * Load role descriptions.
	 *
	 * @todo doesn't need to be per-peson
	 */
	protected function _load_role_descriptions() {
		$this->person->role_descriptions = PSU::get('idmobject')->getRoleDescriptions();
	}//end _load_role_descriptions

	/**
	 * _load_security_response
	 * 
	 * loads Banner Security Question Response
	 *
	 * @access		protected
	 */
	protected function _load_security_response()
	{
		$sql = "BEGIN :c_cursor := gb_third_party_access.f_query_one(:pidm); END;";
		
		if($results = PSU::get('banner')->ExecuteCursor($sql, 'c_cursor', array('pidm' => $this->person->pidm)))		
		{
			$data = $results->FetchRow();
			$this->person->security_response = $data['gobtpac_response'];
		}//end if
	}//end _load_security_response

	/**
	 * _load_should_have_account
	 * 
	 * Determines whether or not the user should have a systems account
	 *
	 * @access		public
	 */
	public function _load_should_have_account()
	{
		/*********************************************************************
		 *    Determine if this user SHOULD have a systems account
		 *********************************************************************/
		$account_roles = array('alumni','student_account_active','employee','faculty','psu_friend');
		$this->person->should_have_account = (count(array_intersect($account_roles, array_keys($this->person->banner_roles))) > 0) ? true : false;
	}//end shouldHaveAccount

	/**
	 * _load_ssn
	 * 
	 * loads SSN
	 *
	 * @access		public
	 * @param   regex $mask Regular Expression for X replacement
	 */
	public function _load_ssn($mask = '/^[0-9]{5}/', $mask_with = 'X')
	{
		if($ssn = PSU::get('idmobject')->getIdentifier($this->person->pidm, 'pid', 'ssn'))
		{
			preg_match($mask, $ssn, $matches);
			
			$mask_value = '';
			$mask_value = str_pad($mask_value, strlen($matches[0]), $mask_with);
			
			$this->person->data['ssn'] = preg_replace($mask, $mask_value, $ssn);
		}//end if
	}//end _load_ssn

	/**
	 * Load student data.
	 */
	public function _load_student()
	{
		$this->person->data['student'] = new \PSU\Student( $this->person );

		$max = PSU::db('banner')->GetRow("SELECT * FROM sgbstdn WHERE sgbstdn_pidm = :pidm AND rownum = 1 ORDER BY sgbstdn_term_code_eff DESC", array('pidm' => $this->person->pidm));

		if( $max )
		{
			$this->person->data['max_term'] = $max['sgbstdn_term_code_eff'];
			$this->person->data['max_term_status'] = PSU::db('banner')->GetOne("SELECT stvstst_desc FROM stvstst WHERE stvstst_code = :code", array('code' => $max['sgbstdn_stst_code']));
			$this->person->data['max_term_status_code'] = $max['sgbstdn_stst_code'];
		}
		else
		{
			$this->person->data['max_term'] = null;
			$this->person->data['max_term_status'] = null;
			$this->person->data['max_term_status_code'] = null;
		}
	}//end _load_student

	/**
	 * _load_system_roles
	 * 
	 * loads portal roles for the given person
	 *
	 * @access		protected
	 */
	protected function _load_system_roles()
	{
		$db = PSUDatabase::connect('mysql/data_mart-admin');

		$this->person->data['system_roles'] = array();

		$sql = "SELECT flag_student, 
		               flag_pat, 
		               flag_os, 
		               flag_faculty, 
		               flag_pa, 
		               flag_supervisor, 
		               flag_lecturer, 
		               flag_dept_contact, 
		               flag_dept_chair, 
		               flag_supplemental 
		          FROM DATA_MART 
		         WHERE pidm = '{$this->person->pidm}'";

		$row = $db->GetRow($sql);
	
		if(is_array($row) && count($row)>0)
		{
			$roles = array();
			foreach($row as $key=>$col)
			{
				if($col)
				{
					$roles[] = str_replace('flag_','',$key);
				}//end if
			}//end foreach
			natcasesort($roles);
			$this->person->system_roles = $roles;
		}//end if
	}//end _load_system_roles

	/**
   * loads a current term_code for the user
   */
  protected function _load_term_code()
	{
		if($this->person->student->gr)
		{
			$this->person->term_code = PSU::db('banner')->GetOne("SELECT f_get_currentterm('GR') FROM dual");
		}//end if
		else
		{
			$this->person->term_code = PSU::db('banner')->GetOne("SELECT f_get_currentterm('UG') FROM dual");
		}//end else
	}//end _load_term_code

	/**
	 * loads data from Banner Third Party Access
	 *
	 * @access		protected
	 */
	protected function _load_third_party_access()
	{
		$third_party = array();
		
		$sql = "BEGIN :c_cursor := gb_third_party_access.f_query_one(:pidm); END;";
		
		if($results = PSU::get('banner')->ExecuteCursor($sql, 'c_cursor', array('pidm' => $this->person->pidm)))		
		{
			foreach($results as $data)
			{
				$third_party = $data;
			}//end foreach
		}//end if
		
		$this->person->username = $third_party['gobtpac_external_user'];
		$this->person->login_name = $third_party['gobtpac_ldap_user'];
		
		$this->person->pin_exists = ($third_party['gobtpac_pin'] != '') ? true : false;
		
		if($this->person->pin_exists)
		{
			$this->person->pin_disabled = ($third_party['gobtpac_pin_disabled_ind'] == 'Y') ? true : false;
			$this->person->pin_expires = strtotime($third_party['gobtpac_pin_exp_date']);
		}//end if
		
		$this->person->security_question = $third_party['gobtpac_question'];
	}//end _load_third_party_access

	/**
	 * loads username history
	 */
	protected function _load_username_history()
	{
		$sql = "SELECT gorpaud_activity_date activity_date,
			             trim(gorpaud_external_user) username
							FROM gorpaud
						 WHERE gorpaud_pidm = :pidm
						   AND gorpaud_chg_ind = 'I'
						 ORDER BY gorpaud_activity_date DESC";

		if($results = PSU::db('banner')->Execute($sql, array('pidm' => $this->person->pidm)))
		{
			$previous = null;
			$this->person->username_history = array();
			while($row = $results->FetchRow())
			{
				// skip consecutive identical usernames
				if( $previous == $row['username'] )
				{
					continue;
				}

				$row['activity_date'] = strtotime($row['activity_date']);
				$this->person->data['username_history'][] = $row;

				// record username for the next loop
				$previous = $row['username'];
			}//end while
		}//end if

		if( count($this->person->data['username_history']) == 1 )
		{
			$this->person->data['username_history'] = array();
		}
	}//end _load_username_history

	/**
	 * loads usnh specific data
	 */
	protected function _load_usnh() {
		$this->person->usnh_id = null;
		$this->person->usnh_pidm = null;

		$this->_load_ssn('//');
		$sql = "SELECT * FROM hr_employee WHERE ssn = :ssn";
		if( $row = PSU::db('banner')->GetRow( $sql, array( 'ssn' => $this->person->ssn ) ) ) {
			$this->person->usnh_id = $row['usnh_id'];
			$this->person->usnh_pidm = $row['usnh_pidm'];
		}//end if

		unset( $this->person->data['ssn'] );
	}//end _load_usnh

	/**
	 * _load_voicemail
	 * 
	 * loads voicemail info
	 *
	 * @access		protected
	 */
	protected function _load_voicemail()
	{
		$db = PSUDatabase::connect('mysql/aster-misuser');
		if($db->IsConnected() && $voicemail = $db->GetOne("SELECT mailbox FROM voicemail_users WHERE customer_id =".$this->person->pidm))
		{
			$this->person->voicemail = substr($voicemail,-4);
		}//end if
	}//end _load_voicemail

	/**
	 * __construct
	 * 
	 * PSUPerson constructor
	 *
	 * @access		public
	 * @param string $identifier Identifier of user
	 * @param mixed $load data sets to be loaded
	 */
	public function __construct( PSUPerson $person )
	{
		parent::__construct();

		$this->person = $person;
	}//end constructor
}//end class PSUPerson_Loader_SIS
