<?php

namespace PSU;

use PSU\Student\Tests;

/**
 * Student Object
 *
 * &copy; 2009 Plymouth State University ITS
 *
 * @author		Matthew Batchelder <mtbatchelder@plymouth.edu>
 */
class Student extends \BannerObject {
	// constants for \PSU\Student::getDateFromTermcode()
	const DATE_START = 1;
	const DATE_END = 2;
	const DATE_HOUSING = 4;

	public $data = array();
	public $person;
	public $data_loaders = array(
		'gr' => 'student_data',
		'gr_inactive' => 'student_data',
		'ia' => 'student_data',
		'ia_inactive' => 'student_data',
		'levels' => 'student_data',
		'levels_inactive' => 'student_data',
		'nc' => 'student_data',
		'nc_inactive' => 'student_data',
		'ug' => 'student_data',
		'ug_inactive' => 'student_data',
		'highschool_gpa' => 'highschool',
		'highschool_test_scores' => 'highschool'
	);

	public function __construct(\PSUPerson $person, $date = null) {
		parent::__construct();
		
		$this->possible_levels = array( 'ug', 'gr', 'nc', 'ia' );

		foreach( $this->possible_levels as $level ) {
			$data_loaders[ $level ] = 'student_data';
		}//end foreach

		if( isset($date) ) {
			$this->date = $date;
		} else {
			$this->date = time();
		}//end if

		$this->person = $person;
		$this->pidm = $this->person->pidm;
	}//end constructor

	/**
	 * base recent student record by term and level
	 */
	public static function dataSQL( $include_level = true, $include_term = false ) {
		$sql = "SELECT s1.*,
		               s1.sgbstdn_term_code_ctlg_1 catalog_term_code,
									 cterm.stvterm_desc catalog_term,
									 s1.sgbstdn_term_code_admit admit_term_code,
									 aterm.stvterm_desc admit_term,
									 s1.sgbstdn_exp_grad_date expected_grad_date,
									 s1.sgbstdn_term_code_grad expected_grad_term_code,
									 gterm.stvterm_desc expected_grad_term,
									 to_char( s1.sgbstdn_exp_grad_date, 'YYYY' ) expected_grad_year,
			             stvstyp_desc \"type\",
									 stvstst_desc \"status\",
									 stvlevl_desc \"level\",
									 f_class_calc_fnc(s1.sgbstdn_pidm, s1.sgbstdn_levl_code, :term_code) class_code,
									 decode(substr(stvclas_desc, 3), 'aduate', 'Graduate', substr(stvclas_desc, 3)) \"class\",
									 stvadmt_desc admit_type,
									 stvcamp_desc campus,
									 stvdept_desc department,
									 stvresd_desc residency,
									 stvrate_desc rate,
									 term.stvterm_desc term,
									 nvl(to_number(substr(f_split_fields(s1.as_of_cum_gpa,2),1,5)), 0) AS hours_earned,
									 nvl(to_number(substr(f_split_fields(s1.as_of_cum_gpa,5),1,10)), 0) AS gpa,
									 nvl(to_number(substr(f_split_fields(s1.term_gpa,1),1,5)), 0) AS term_credits,
									 nvl(to_number(substr(f_split_fields(s1.term_gpa,5),1,10)), 0) AS term_gpa,
									 nvl(to_number(substr(f_split_fields(s1.prev_term_gpa,1),1,5)), 0) AS previous_term_credits,
									 nvl(to_number(substr(f_split_fields(s1.prev_term_gpa,5),1,10)), 0) AS previous_term_gpa
							FROM (SELECT stdn.*,
                      		 f_concat_as_of_cum_gpa(stdn.sgbstdn_pidm, :term_code, upper(:levl_code), 'O') AS as_of_cum_gpa,
									         f_concat_term_gpa(stdn.sgbstdn_pidm, :term_code, upper(:levl_code), 'O') AS term_gpa,
									         f_concat_prev_term_gpa(stdn.sgbstdn_pidm, :term_code, 'O') AS prev_term_gpa
											FROM sgbstdn stdn
                   ) s1
                   INNER JOIN stvstyp ON stvstyp_code = s1.sgbstdn_styp_code
                   INNER JOIN stvstst ON stvstst_code = s1.sgbstdn_stst_code
                   INNER JOIN stvterm term ON term.stvterm_code = s1.sgbstdn_term_code_eff
                   LEFT OUTER JOIN stvlevl ON stvlevl_code = s1.sgbstdn_levl_code
                   LEFT OUTER JOIN stvcamp ON stvcamp_code = s1.sgbstdn_camp_code
                   LEFT OUTER JOIN stvresd ON stvresd_code = s1.sgbstdn_resd_code
                   LEFT OUTER JOIN stvdept ON stvdept_code = s1.sgbstdn_dept_code
                   LEFT OUTER JOIN stvadmt ON stvadmt_code = s1.sgbstdn_admt_code
                   LEFT OUTER JOIN stvrate ON stvrate_code = s1.sgbstdn_rate_code
                   LEFT OUTER JOIN stvterm gterm ON gterm.stvterm_code = s1.sgbstdn_term_code_grad
                   LEFT OUTER JOIN stvterm aterm ON aterm.stvterm_code = s1.sgbstdn_term_code_admit
                   LEFT OUTER JOIN stvterm cterm ON cterm.stvterm_code = s1.sgbstdn_term_code_ctlg_1
                   LEFT OUTER JOIN stvclas ON stvclas_code = f_class_calc_fnc(s1.sgbstdn_pidm, s1.sgbstdn_levl_code, :term_code)
						 WHERE s1.sgbstdn_pidm = :pidm
									 %s
							 AND s1.sgbstdn_term_code_eff = (
                     SELECT max(s2.sgbstdn_term_code_eff)
                       FROM sgbstdn s2
                      WHERE s2.sgbstdn_pidm = s1.sgbstdn_pidm
                        AND s2.sgbstdn_levl_code = s1.sgbstdn_levl_code
                        %s
                   )";

		return sprintf(
			$sql, 
			$include_level ? "AND s1.sgbstdn_levl_code = upper(:levl_code)" : '',
			$include_term ? "AND s2.sgbstdn_term_code_eff <= :term_code" : ''
		);
	}//end dataSQL

	public static function getAidYear( $term = null ) {
		if( !isset($term) ) {
			$term = self::getCurrentTerm( 'UG' );
		}

		// grab the second two digits of the year
		$term_part = substr( $term, 2, 2 );

		// grab the last two digits of term
		$term_end = substr( $term, -2 );

		// when the summer term hits, the aid year changes.  Account for
		// that by incrementing the term_part
		if( $term_end == '40' || $term_end == '94' ) {
			$term_part++;
		}//end if

		// add the captured digits as the first part of the aidyear
		$aidyear = --$term_part;

		// increment the captured digits and append them to the aidyear
		$aidyear .= ++$term_part; 

		return $aidyear;
	}//end getAidYear

	/**
	 * load all courses the student has been enrolled it
	 */
	public static function getCourses( $pidm, $low_date = null, $high_date = null )
	{
		$courses = array();

		$args = array(
			'pidm' => $pidm
		);

		if( $low_date ) {
			$args['low_date'] = \PSU::db('banner')->BindDate( $low_date );
		}//end if

		if( $high_date ) {
			$args['high_date'] = \PSU::db('banner')->BindDate( $high_date );
		}//end if

		$sql = "SELECT ssbsect.*
							FROM sfrstcr,
							     ssbsect,
									 stvrsts,
									 scbcrse s1,
									 stvterm
		         WHERE sfrstcr_pidm = :pidm
		           AND sfrstcr_term_code = ssbsect_term_code
		           AND sfrstcr_crn = ssbsect_crn
							 AND sfrstcr_rsts_code = stvrsts_code
							 AND stvrsts_voice_type = 'R'
               AND scbcrse_subj_code = ssbsect_subj_code
               AND scbcrse_crse_numb = ssbsect_crse_numb
							 AND stvterm_code = ssbsect_term_code
					     ".($low_date ? " AND stvterm_start_date >= :low_date " : "")."
					     ".($high_date ? " AND stvterm_start_date >= :high_date " : "")."
							 AND scbcrse_eff_term = (
										SELECT max(s2.scbcrse_eff_term)
                      FROM scbcrse s2
                     WHERE s2.scbcrse_subj_code = s1.scbcrse_subj_code
                       AND s2.scbcrse_crse_numb = s1.scbcrse_crse_numb
                       AND s2.scbcrse_eff_term <= ssbsect_term_code
                   )
		      ORDER BY ssbsect_term_code, s1.scbcrse_title";

		if($results = \PSU::db('banner')->CacheExecute($sql, $args))
		{
			foreach($results as $row)
			{
				$row = \PSU::cleanKeys('ssbsect_', '', $row);
				$courses[$row['term_code']][$row['crn']] = new Course\Section($row['crn'], $row['term_code'], $row);
			}//end foreach
		}//end if	

		$grde_sql = "SELECT sfrstcr_term_code, sfrstcr_crn, sfrstcr_grde_code, sfrstcr_grde_code_mid
		               FROM sfrstcr
		              WHERE sfrstcr_pidm = :pidm";

		$grde_rset = \PSU::db('banner')->CacheExecute($grde_sql, $args);

		foreach($grde_rset as $row)
		{
			$row = \PSU::cleanKeys('sfrstcr_', '', $row);
			$courses[$row['term_code']][$row['crn']]->grde_code = $row['grde_code'];
			$courses[$row['term_code']][$row['crn']]->grde_code_mid = $row['grde_code_mid'];
		}

		return $courses;
	}//end getCourses

	public static function getCurrentTerm( $level ) {
		return self::getTerm( $level, time() );
	}//end getCurrentTerm

	/**
	 * Pull the start or end date of a term
	 */
	public static function getDateFromTermcode( $term, $which = self::DATE_START ) {
		$term = (int)$term;

		$col = 'stvterm_' . ($which & self::DATE_HOUSING ? 'housing_' : '');
		$col .= $which & self::DATE_END ? 'end_date' : 'start_date';
		$sql = "SELECT $col FROM stvterm WHERE stvterm_code = '$term'";

		$date = \PSU::db('banner')->CacheGetOne($sql);
		$date = strtotime($date);

		return $date;
	}//end getDateFromTermcode

	/**
	 * Get the student's first active future term.
	 */
	public function getFutureActiveTerm( $level ) {
		$args = array(
			'pidm' => $this->pidm,
			'levl_code' => $level,
		);

		$sql = "
			SELECT term_code_eff
			FROM
				v_student_account_active_terms LEFT JOIN
				stvterm ON term_code_eff = stvterm_code
			WHERE
				pidm = :pidm AND
				levl_code = :levl_code AND
				stvterm_start_date > SYSDATE
		";

		$term_code = \PSU::db('banner')->GetOne( $sql, $args );
		return $term_code;
	}//end getFirstActiveTerm

	/**
	 * Get the courses a user can see from Moodle
	 * $moodle_path is the location of the moodle application starting from the PSU base url
	 */
	public static function getMoodleCourses( $username, $moodle_path = 'webapp/courses/course/report/portal/index.php' ) {
		$moodle_curl_url = 'http://www'.(\PSU::isDev() ? '.dev' : '').'.plymouth.edu/'.$moodle_path;

		$today = mktime();
		$get_data = '?username=' . $username . '&time=' . $today . '&hash=' . md5($username . $today . 'monkeyballz');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_URL, $moodle_curl_url . $get_data);
		curl_setopt($ch, CURLOPT_COOKIE, $_SERVER['HTTP_COOKIE']);
		$moodle_json = curl_exec($ch);
		curl_close($ch);

		return json_decode($moodle_json, true);
	} // end getMoodleCourses

	/**
	 * Determine if the user has ever had finaid activity. Copies functionality from GUASYST, see FIN_AID_APPL.
	 */
	public function has_finaid_activity() {
		$sql = "SELECT 1 FROM rorstat WHERE rorstat_pidm = :pidm";
		$result = \PSU::db('banner')->GetOne( $sql, array( 'pidm' => $this->pidm ) );
		return (bool)$result;
	}//end has_finaid_activity

	/**
	 * executes student query and returns a result set for looping
	 */
	public static function query( $args = array(), $return_results = true, $debug = false ){
		if( !is_array( $args ) ) {
			$args = array('term_code' => $args);
		}//end if

		$valid_args = array(
			'term_code',
			'last_name',
			'first_name',
			'gender',
			'veteran',
			'advisor_first_name',
			'advisor_last_name',
			'continuing_ed',
			'class1',
			'class2',
			'class3',
			'class4',
			'confirmed_status',
			'pt_ft',
			'age_low',
			'age_high',
			'gpa_low',
			'gpa_high',
			'credit_low',
			'credit_high',
			'state_code',
			'degree',
			'department',
			'major',
			'option',
			'minor',
			'degs',
			'sport',
			'teacher_cert',
			'c_age',
			'c_commute',
			'c_sex',
			'c_dean',
			'c_styp',
			'c_advr',
			'c_conf',
			'c_ma',
			'c_pt_cred',
			'c_t_cred',
			'c_birth',
			'c_cred',
			'c_majr',
			'c_pt_gpa',
			'c_t_gpa',
			'c_ca',
			'c_gpa',
			'c_min',
			'c_sport',
			'c_vet',
			'c_ca_ph',
			'c_degr',
			'c_opt',
			'c_sport_comments',
			'c_webreg',
			'c_citz',
			'c_dept',
			'c_pa',
			'c_sport_eligibility',
			'c_class',
			'c_email',
			'c_load',
			'c_stat',
			'c_degs',
			'c_cat'
		);

		$validated_args = array();
		foreach( $args as $key => $value ) {
			if( in_array( $key, $valid_args ) ) {
				$validated_args['ps_'.$key] = $value;
			}//end if
		}//end foreach	

		if( !$validated_args['ps_term_code'] ) {
			$validated_args['ps_term_code'] = self::getCurrentTerm('UG');
		}//end if

		$sql = "BEGIN pkg_student_utility.p_query(pb_load_address => TRUE";
		foreach( $validated_args as $key => $value ) {
			$sql .= ", ".$key." => :".$key;
		}//end foreach	
		$sql .= "); END;";

		$stmt = \PSU::db('banner')->PrepareSP( $sql );

		foreach( $validated_args as $key => $value ) {
			\PSU::db('banner')->InParameter( $stmt, $value, $key );
		}//end foreach	

		\PSU::db('banner')->Execute( $stmt );

		if( $debug ) {
			psu::dbug( psu::db('banner')->GetOne("SELECT pkg_student_utility.f_get_unexclude() FROM dual") );
		}//end if

		$sql = "SELECT * FROM gt_student_query WHERE exclude IS NULL";
		if( $return_results ) {
			return \PSU::db('banner')->Execute($sql);
		} else {
			return $sql;
		}//end if
	}//end query

	/**
	 * returns the student's schedule for a given term
	 */
	public function schedule( $term_code ) {
		return $this->courses[ $term_code ];
	}//end schedule

	public static function getTerm( $level, $date = null ) {
		if( !$date ) {
			$date = time();
		}//end if
		return \PSU::db('banner')->GetOne("SELECT f_get_currentterm(:level_code, :month, :year, :day) FROM dual", array( 'level_code' => $level, 'month' => strtoupper(date('M', $date)), 'year' => date('Y', $date), 'day' => date('d', $date)));
	}//end getTerm

	public static function termRange( $level = 'any', $start_date = null, $end_date = null) {
		$terms = array();

		if( !$start_date ) {
			$start_date = strtotime('-2 years');
		}//end if

		if( !$end_date ) {
			$end_date = strtotime('+6 months');
		}//end if

		if( $level == 'UG' ) {
			$where = " AND substr( stvterm_code, -2, 1 ) IN ('1','2','3','4')";
		} elseif( $level == 'GR' ) {
			$where = " AND substr( stvterm_code, -2, 1 ) IN ('8','9')";
		}//end if		

		$args = array(
			'start_date' => \PSU::db('banner')->BindTimeStamp( $start_date ),
			'end_date' => \PSU::db('banner')->BindTimeStamp( $end_date )
		);

		$sql = "SELECT stvterm_code,
			             stvterm_desc
							FROM stvterm
						 WHERE substr(stvterm_code, 1, 1) IN ('1', '2')
							 AND stvterm_housing_start_date BETWEEN :start_date AND :end_date
							 		 {$where}
		         ORDER BY stvterm_code";
		if( $results = \PSU::db('banner')->CacheExecute( $sql, $args ) ) {
			foreach( $results as $row ) {
				$terms[ $row['stvterm_code'] ] = $row['stvterm_desc'];
			}//end foreach
		}//end if

		return $terms;
	}//end termRange

	/**
	 * returns the type of term
	 */
	public static function term_type( $term_code ) {
		switch( substr($term_code, -2) ) {
			case '10':
				return 'ug_fall';
				break;
			case '20':
				return 'ug_winter';
				break;
			case '30':
				return 'ug_spring';
				break;
			case '40':
				return 'ug_summer';
				break;
			case '91':
				return 'gr_fall';
				break;
			case '92':
				return 'gr_winter';
				break;
			case '93':
				return 'gr_summer';
				break;
			case '94':
				return 'gr_summer';
				break;
			case '85':
				return 'community';
				break;
		}//end switch

		return 'unknown';
	}//end term_type

	/**
	 * retrieves web registration data
	 */
	public function web_registration( $term_code ) {
		if( !isset( $this->data['web_registration'] ) ) {
			$this->web_registration = array();
		}//end if

		if( $this->web_registration[ $term_code ] ) {
			return $this->web_registration[ $term_code ];
		} else {
			$this->web_registration[ $term_code ] = array();
			$sql="SELECT sprapin_pin sfrwctl_pin,
										 sfrwctl_begin_date,
										 sfrwctl_end_date,
										 sfrwctl_hour_begin,
										 sfrwctl_hour_end
								FROM sprapin,
										 sfbrgrp,
										 sfbwctl,
										 sfrwctl
							 WHERE sprapin_pidm= :pidm
								 AND sprapin_pidm=sfbrgrp_pidm
								 AND sfbwctl_rgrp_code=sfbrgrp_rgrp_code
								 AND sfrwctl_priority=sfbwctl_priority
								 AND sprapin_term_code =:term_code
								 AND sfbwctl_term_code=sprapin_term_code
								 AND sfrwctl_term_code=sprapin_term_code
								 AND sfbrgrp_term_code=sprapin_term_code";

			$args = array(
				'pidm' => $this->pidm,
				'term_code' => $term_code
			);

			if($row = \PSU::db('banner')->CacheGetRow($sql, $args))
			{
					$row = \PSU::cleanKeys('sfrwctl_','',$row);
					$row['begin_date'] = date('F d, Y',strtotime($row['begin_date']));
					$row['end_date'] = date('F d, Y',strtotime($row['end_date']));
					$row['hour_begin'] = substr($row['hour_begin'], 0, 2) . ':' . substr($row['hour_begin'], 2, 2).date('A',$row['hour_begin']);
					$row['hour_end'] = substr($row['hour_end'], 0, 2) . ':' . substr($row['hour_end'], 2, 2).date('A',$row['hour_begin']);
					$this->web_registration[ $term_code ] = $row;
			}//end if
		}//end else

		return $this->web_registration[ $term_code ];
	}//end web_registration

	/**
	 * lazy loads appearance in v_student_account_active
	 */
	protected function _load_account_active(){
		$sql="SELECT 'Y' FROM v_student_account_active WHERE pidm = :pidm";
		$this->account_active = (\PSU::get('banner')->GetOne($sql, array('pidm' => $this->pidm)) == 'Y') ? true : false;
	}//end _load_account_active

	/**
	 * lazy loads appearance in v_student_active
	 */
	protected function _load_active(){
		$sql="SELECT 'Y' FROM v_student_active WHERE pidm = :pidm";
		$this->active = (\PSU::get('banner')->GetOne($sql, array('pidm' => $this->pidm)) == 'Y') ? true : false;
	}//end _load_active

	protected function _load_aidyears() {
		$this->data['aidyears'] = new \PSU_Student_Aidyears( $this->pidm );
	}

	/**
	 * load all courses the student has been enrolled it
	 */
	protected function _load_courses()
	{
		$this->courses = array();

		$this->data['courses'] = self::getCourses( $this->pidm );
	}//end _load_courses

	protected function _load_finaid() {
		if( $this->pidm ) {
			$this->data['finaid'] = new \PSU_Student_Finaid( $this->pidm );
		}
	}//end load_finaid

	/**
	 * Checks if the person is eligible to register
	 *
	 * @access		public
	 * @return		boolean
	 */
	public function _load_eligible_to_register()
	{
		$this->eligible_to_register = false;

		$data = \PSU::get('banner')->GetCol("SELECT sobterm_term_code term_code FROM sobterm WHERE sobterm_profile_send_ind = 'Y'");

		$global_term_string = implode("','",$data);

		$sql="SELECT 'Y'
						 FROM sgbstdn a,
									stvstst
						WHERE a.sgbstdn_pidm = :pidm
									AND a.sgbstdn_stst_code = stvstst_code
									AND stvstst_reg_ind = 'Y'
									AND a.sgbstdn_term_code_eff IN
									 (SELECT MAX (b.sgbstdn_term_code_eff)
											FROM sgbstdn b, stvterm c
										 WHERE     b.sgbstdn_pidm = a.sgbstdn_pidm
													 AND b.sgbstdn_term_code_eff <= c.stvterm_code
													 AND c.stvterm_code IN ('".$global_term_string."')
										 GROUP BY c.stvterm_code)";

		$this->eligible_to_register =  (\PSU::get('banner')->GetOne($sql, array( 'pidm' => $this->pidm )) == 'Y') ? true : false;
	}//end _load_eligible_to_register

	/**
	 * lazy loads holds
	 */
	function _load_holds() {
		$this->holds = array();

		$args = array('pidm' => $this->pidm);

		$sql = "BEGIN :cursorvar := gb_hold.f_query_all(p_pidm => :pidm); END;";

		if($results = \PSU::db('banner')->ExecuteCursor($sql, 'cursorvar', $args))
		{
			while($row = $results->FetchRow())
			{
				$row = \PSU::cleanKeys('sprhold_','',$row);
				unset($row['rowid'], $row['pidm']);

				$row['from_date'] = strtotime( $row['from_date'] );
				$row['to_date'] = strtotime( $row['to_date'] );
				
				$this->data['holds'][] = $row;
			}//end while
		}//end if
	}//end _load_holds

	/**
	 * Get the courses a user can see from Moodle
	 */
	public function _load_moodle_courses() {
		$this->data['moodle_courses'] = array();
		$username = $this->person->username;

		$this->data['moodle_courses'] = self::getMoodleCourses( $username );
	} // end _load_moodle_courses

	/**
	 * lazy load student data
	 */
	protected function _load_student_data() {
		$this->levels = array();
		$this->levels_inactive = array();
		$this->all_levels = array();
		$this->terms = array();

		// date may be false, in which case we don't want the levels set
		if( $this->date ) {
			// retrieve possible terms
			$sql = "
				SELECT stvterm_code 
				FROM stvterm 
				WHERE
					SUBSTR(stvterm_code, 1, 1) IN ('1', '2') AND
					:query_date BETWEEN stvterm_housing_start_date AND stvterm_housing_end_date+1
			";

			$terms = \PSU::db('banner')->CacheGetCol($sql, array('query_date' => \PSU::db('banner')->BindDate($this->date)));

			// retrieve possible terms $sql = "SELECT stvterm_code FROM stvterm WHERE substr(stvterm_code, 1, 1) in ('1', '2') AND :query_date BETWEEN stvterm_housing_start_date AND stvterm_housing_end_date"; $terms = \PSU::db('banner')->GetCol($sql, array('query_date' => \PSU::db('banner')->BindDate($this->date))); // build max terms by level array
			foreach( (array) $terms as $term ) {
				if( substr( $term, 4, 1 ) == 9 || substr( $term, 4, 2 ) == 80 ) {
					$this->terms[ 'gr' ] = $this->terms[ 'gr' ] > $term ? $this->terms[ 'gr' ] : $term;
				} elseif( substr( $term, 4, 2 ) == 85 ) {
					$this->terms['nc'] = $this->terms[ 'ia' ] = $this->terms[ 'ia' ] > $term ? $this->terms[ 'ia' ] : $term;
				} else {
					$this->terms[ 'ug' ] = $this->terms[ 'ug' ] > $term ? $this->terms[ 'ug' ] : $term;
				}//end else
			}//end foreach
		}//end if
		
		// generate the two types of statements we'll need
		$term_sql = self::dataSQL( true, true );
		$no_term_sql = self::dataSQL( true, false );

		// loop over levels and get the student data by level
		foreach( $this->possible_levels as $level ) {
			$args = array(
				'pidm' => $this->person->pidm, 
				'term_code' => $this->terms[ $level ],
				'levl_code' => $level
			);

			// retrieve the student data
			if( $data = \PSU::db('banner')->GetRow( $this->terms[ $level ] ? $term_sql : $no_term_sql, $args ) ) {
				if( !isset($this->terms[ $level ]) ) {
					$this->terms[ $level ] = $data['sgbstdn_term_code_eff'];
				}//end if

				// is the record active?
				if( $data['sgbstdn_stst_code'] == 'AS' ) {
					// yeah.  Create a level based student data property and null out the inactive property
					$this->data[ $level ] = new Student\Data( $this, $data, $this->terms[ $level ] ); 
					$this->data[ $level . '_inactive' ] = null;
					$this->all_levels[$level] = $this->levels[$level] = $level;
				} else {
					// no.  Create an inactive level based student data property and null out the level property
					$this->data[ $level . '_inactive' ] = new Student\Data( $this, $data, $this->terms[ $level ] ); 
					$this->data[ $level ] = false;
					$this->all_levels[$level] = $this->levels_inactive[$level] = $level.'_inactive';
				}//end if
			} else {
				// no data was retrieved.  set active and inactive to null
				$this->data[ $level ] = false;
				$this->data[ $level . '_inactive' ] = null;
			}//end else
		}//end foreach
	}//end _load_student_data

	/**
	 * lazy loads high schools and high school information
	 */
	protected function _load_highschool()
	{
		$this->highschool_test_scores = array();
		$this->highschool_gpa = array();

		$query="SELECT sorhsch_gpa gpa,
		               sortest_test_date test_date,
		               stvtesc_desc test_description,
		               sortest_test_score test_score,
		               stvsbgi_desc highschool
		          FROM sorhsch, sortest, stvtesc, stvsbgi
		         WHERE sorhsch_pidm=:pidm AND
		               sorhsch_pidm=sortest_pidm(+) AND
		               sorhsch_sbgi_code=stvsbgi_code AND
		               sortest_tesc_code=stvtesc_code
		      ORDER BY sortest_test_date";
		if($results = \PSU::db('banner')->CacheExecute($query, array( 'pidm' => $this->pidm ))) 
		{
			foreach($results as $row) 
			{
				$this->highschool_test_scores[] = $row;
				if( $row['gpa'] ) {
					$this->highschool_gpa[ $row['highschool'] ] = $row['gpa'];
				}//end if
			}//end while
		}//end if
	}//end _load_test_scores

	protected function _load_tests() {
		$this->tests = new Tests( $this );
	}//end _load_tests
}//end class \PSU\Student
