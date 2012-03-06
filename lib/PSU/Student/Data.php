<?php

namespace PSU\Student;

/**
 * PSUStudentData.class.php
 *
 * Student Data Object
 *
 * &copy; 2009 Plymouth State University ITS
 *
 * @author		Matthew Batchelder <mtbatchelder@plymouth.edu>
 */
class Data extends \BannerObject {
	public $data = array();
	public $data_loaders = array();
	public $person;

	/**
	 * constructor
	 *
	 * @param $person \b PSUPerson object
	 * @param $level_or_data \b This can be 1 of two things:
	 *                          - if a string, it is the Level code of student data (e.g. ug, gr, etc)
	 *                          - if an array, it is an array of sgbstdn data
	 * @param $term_code \b Effective term code
	 */
	public function __construct($student, $level_or_data, $max_term_code = null, $term_code_eff = null) {
		parent::__construct();
		
		$data_loaders = array();

		$this->data_loaders = \PSU::params($data_loaders, $this->data_loaders);

		// make sure this object can access its associated PSU_Student
		if( $student instanceof \PSU\Student ) {
			$this->student = $student;
		} else {
			$this->student = $person->student;
		}//end if

		// store the pidm globally to shorten the bind variables in queries :)
		$this->pidm = $this->student->person->pidm;

		if( is_array( $level_or_data ) ) {
			$this->parse( $level_or_data );
			$this->term_code = $max_term_code ? $max_term_code : \PSU\Student::getCurrentTerm( $this->level_code );
		} else {
			$this->level_code = $level_or_data;
			$this->term_code = $max_term_code ? $max_term_code : \PSU\Student::getCurrentTerm( $this->level_code );
			$this->term_code_eff = $term_code_eff;

			$this->load();	
		}//end else
	}//end constructor


	/**
	 * loads data for the person
	 */
	protected function load() {
		$args = array(
			'pidm' => $this->pidm,
			'levl_code' => $this->level_code,
			'term_code' => $this->term_code_eff
		);

		if( $this->term_code_eff ) {
			$sql = \PSU\Student::dataSQL( true, true );
		} else {
			$sql = \PSU\Student::dataSQL( true, false );
		}//end else

		// store term code to prevent overwiting in the execution of dataSQL
		$term_code = $this->term_code;

		if( $row = \PSU::db('banner')->GetRow( $sql, $args)) {
			$this->parse( $row );
		}//end if

		// fix the overwritten term_code
		$this->term_code = $term_code;
	}//end load

	/**
	 * Parse incoming student data and populate $this.
	 * @param $data \b array the data array, with or without sgbstdn_ prefix on the fields
	 */
	protected function parse($data)
	{
		foreach($data as $key => $value)
		{
			$key = str_replace('sgbstdn_', '', strtolower($key));
			
			$this->$key = $value;
		}//end foreach

		// convert the date fields to timestamps
		if( $this->exp_grad_date ) {
			$this->exp_grad_date = strtotime( $this->exp_grad_date );
		}//end if

		if( $this->leav_from_date ) {
			$this->leav_from_date = strtotime( $this->leav_from_date );
		}//end if

		if( $this->leav_to_date ) {
			$this->leav_to_date = strtotime( $this->leav_to_date );
		}//end if

		if( $this->activity_date ) {
			$this->activity_date = strtotime( $this->activity_date );
		}//end if

		// alias some properties
		$this->term_code = $this->term_code_eff;
		$this->level_code = $this->levl_code;
	}//end parse
	

	/**
	 * lazy load academic standing
	 */
	protected function _load_academic_standing() {
		$sql = "SELECT stvastd_desc
	      		  FROM shrttrm s1,
				           stvastd
						 WHERE s1.shrttrm_astd_code_end_of_term = stvastd_code
							 AND s1.shrttrm_pidm = :pidm
							 AND s1.shrttrm_term_code = (
										 SELECT MAX(s2.shrttrm_term_code) 
											 FROM shrttrm s2 
											WHERE s2.shrttrm_pidm = s1.shrttrm_pidm
												AND s2.shrttrm_term_code <= :term_code
									 )";

		$this->academic_standing = \PSU::db('banner')->GetOne($sql, array(
			'pidm' => $this->pidm,
			'term_code' => $this->term_code
		));
	}//end _load_academic_standing	

	/**
	 * Load results of the first-year advising survey.
	 */
	protected function _load_advising_survey()
	{
		$sql = "SELECT *
		          FROM advising_survey
		         WHERE pidm = ? AND term_code = ?
		";

		$args = array($this->pidm, $this->term_code);

		$advising_survey = \PSU::db('myplymouth')->GetRow($sql, $args);

		$this->data['advising_survey'] = $advising_survey;
	}//end _load_advising_survey

	/**
	 * lazy loads an array of active advisor records
	 */
	protected function _load_advisors()
	{
		$this->advisors = array();

		$query="SELECT *
							 FROM sgradvr a,spriden
							WHERE a.sgradvr_pidm = :pidm
								AND a.sgradvr_advr_pidm = spriden_pidm
								AND spriden_change_ind is null
								AND a.sgradvr_term_code_eff =
											(SELECT MAX (b.sgradvr_term_code_eff)
												FROM sgradvr b
											 WHERE b.sgradvr_pidm = a.sgradvr_pidm
												 AND b.sgradvr_term_code_eff <= :term_code)
								AND a.sgradvr_advr_pidm in
											(SELECT c.sgradvr_advr_pidm
												 FROM sgradvr c
												WHERE c.sgradvr_term_code_eff=a.sgradvr_term_code_eff
													AND c.sgradvr_pidm=a.sgradvr_pidm)
							 AND EXISTS (SELECT 1
														 FROM sgbstdn,sobcurr
														WHERE sgbstdn_pidm = :pidm
															AND sgbstdn_stst_code = 'AS'
															AND sgbstdn_styp_code <> 'X'
															AND sgbstdn_levl_code=sobcurr_levl_code
															AND sgbstdn_degc_code_1=sobcurr_degc_code
															AND sgbstdn_program_1=sobcurr_program
															AND sobcurr_secd_roll_ind='Y'
															AND sobcurr_term_code_init<=:term_code
															AND sgbstdn_term_code_eff =
																	 (SELECT MAX (sg.sgbstdn_term_code_eff)
																			FROM sgbstdn sg
																		 WHERE sg.sgbstdn_pidm = :pidm))
					ORDER BY a.sgradvr_prim_ind DESC,
							spriden_last_name,
							spriden_first_name";
	
		$args = array(
			'pidm' => $this->pidm,
			'term_code' => $this->term_code
		);

		if($results = \PSU::db('banner')->Execute($query, $args)) {
			foreach ($results as $row) {
				$person = \PSUPerson::get($row['sgradvr_advr_pidm']);
				$person->advisor_term_code_eff = $row['sgradvr_term_code_eff'];
				$person->advisor_primary_ind = $row['sgradvr_prim_ind'];

				$this->data['advisors'][] = $person;
			}//end while
		}//end if
	}//end _load_advisors

	/**
	 * lazy loads the number of years student has been an athlete
	 */
	protected function _load_athletic_year() {
		$sql = "SELECT count(DISTINCT SUBSTR(sgrsprt_term_code, 1, 4)) 
							FROM sgrsprt 
             WHERE sgrsprt_pidm = :pidm";
		$this->athletic_year = \PSU::db('banner')->GetOne($sql, array( 'pidm' => $this->pidm ) );
	}//end _load_athletic_year

	/**
	 * retrieve credits the student was enrolled in for the current term
	 */
	protected function _load_credits_enrolled() {
		$sql = "
			SELECT SUM(sfrstcr_credit_hr) 
				FROM sfrstcr
			       JOIN stvrsts
			         ON stvrsts_code = sfrstcr_rsts_code
			 WHERE sfrstcr_pidm = :pidm 
				 AND sfrstcr_term_code = :term_code
			   AND stvrsts_voice_type = 'R'
		";

		$args = array(
			'pidm' => $this->pidm,
			'term_code' => $this->term_code,
		);

		$this->data['credits_enrolled'] = (int) \PSU::db('banner')->GetOne( $sql, $args );
	}//end _load_credits_enrolled

	/**
	 * lazy loads an array of transcript information (academic history)
	 */
	protected function _load_transcript()
	{
		$this->transcript = array();
		
		/**
		 * look for and load any existing psu institution coursework
		 */

		$count_query = " SELECT		count(*)			
											 FROM		shrgrde a, 
															shrtckg, 
															shrtckl, 
															shrtckn, 
															stvlevl
											 WHERE  shrtckn_pidm = :pidm
												 AND  shrtckl_pidm = shrtckn_pidm
												 AND  shrtckl_term_code = shrtckn_term_code
												 AND  shrtckl_tckn_seq_no = shrtckn_seq_no
												 AND  shrtckl_levl_code LIKE '%' || :levl_code
												 AND  shrtckl_levl_code = stvlevl_code
												 AND  shrtckg_pidm = shrtckn_pidm
												 AND  shrtckg_term_code = shrtckn_term_code
												 AND  shrtckg_tckn_seq_no = shrtckn_seq_no
												 AND  shrtckg_seq_no = (SELECT   MAX (y.shrtckg_seq_no)
																									FROM  shrtckg y
																								 WHERE  y.shrtckg_pidm = shrtckn_pidm
																									 AND  y.shrtckg_term_code = shrtckn_term_code
																									 AND  y.shrtckg_tckn_seq_no = shrtckn_seq_no)
												 AND  a.shrgrde_code = shrtckg_grde_code_final
												 AND  a.shrgrde_levl_code = shrtckl_levl_code
												 AND  a.shrgrde_term_code_effective = (SELECT   MAX (b.shrgrde_term_code_effective)
																																 FROM   shrgrde b
																																WHERE   b.shrgrde_code = shrtckg_grde_code_final
																																	AND   b.shrgrde_levl_code = shrtckl_levl_code
																																	AND   b.shrgrde_term_code_effective <= shrtckn_term_code)";
		$elements = \PSU::db('banner')->GetOne($count_query, array( 'pidm' => $this->pidm, 'levl_code' => $this->levl_code ) ); 

		$query="  SELECT  shrtckn_term_code, 
											shrtckn_subj_code, 
											shrtckn_crse_numb,
											shrtckn_crse_title,
											shrtckg_credit_hours, 
											shrtckg_grde_code_final,
											NVL ( shrtckg_credit_hours * DECODE (shrgrde_gpa_ind, 'Y', 1, 'N', 0) * DECODE ( NVL (shrtckn_repeat_course_ind, 'N'), 'I', 1, 'M', 1, 'E', 0, 'N', 1, 'A', 1 ), 0 ) shrtckg_gpa_hours,
											NVL ( shrtckg_credit_hours * shrgrde_quality_points * DECODE (shrgrde_gpa_ind, 'Y', 1, 'N', 0) * DECODE ( NVL (shrtckn_repeat_course_ind, 'N'), 'I', 1, 'M', 1, 'E', 0, 'N', 1, 'A', 1 ) , 0 ) shrtckg_qual_pts
								FROM  shrgrde a, 
											shrtckg, 
											shrtckl, 
											shrtckn, 
											stvlevl
							 WHERE  shrtckn_pidm = :pidm
								 AND  shrtckl_pidm = shrtckn_pidm
								 AND  shrtckl_term_code = shrtckn_term_code
								 AND  shrtckl_tckn_seq_no = shrtckn_seq_no
								 AND  shrtckl_levl_code LIKE '%' || :levl_code
								 AND  shrtckl_levl_code = stvlevl_code
								 AND  shrtckg_pidm = shrtckn_pidm
								 AND  shrtckg_term_code = shrtckn_term_code
								 AND  shrtckg_tckn_seq_no = shrtckn_seq_no
								 AND  shrtckg_seq_no = (SELECT   MAX (y.shrtckg_seq_no)
																					FROM  shrtckg y
																				 WHERE  y.shrtckg_pidm = shrtckn_pidm
																					 AND  y.shrtckg_term_code = shrtckn_term_code
																					 AND  y.shrtckg_tckn_seq_no = shrtckn_seq_no)
								 AND  a.shrgrde_code = shrtckg_grde_code_final
								 AND  a.shrgrde_levl_code = shrtckl_levl_code
								 AND  a.shrgrde_term_code_effective = (SELECT   MAX (b.shrgrde_term_code_effective)
																												 FROM   shrgrde b
																												WHERE   b.shrgrde_code = shrtckg_grde_code_final
																													AND   b.shrgrde_levl_code = shrtckl_levl_code
																													AND   b.shrgrde_term_code_effective <= shrtckn_term_code)
						ORDER BY  shrtckn_term_code";
		if($results = \PSU::db('banner')->Execute($query, array( 'pidm' => $this->pidm, 'levl_code' => $this->levl_code ) )) 
		{
			$term = $row['shrtckn_term_code'];
			$prevterm = $term;
			$credithrs = $row['shrtckg_credit_hours'];
			$finalgrade = $row['shrtckg_grde_code_final'];
			$subject = $row['shrtckn_subj_code'];
			$crsenumb = $row['shrtckn_crse_numb'];
			$title = $row['shrtckn_crse_title'];
			$prevtotalpts = 0;
			$cntr=0;
			foreach($results as $row)
			{
				$cntr++;
				$row['semgpa']='';
				$row['cumgpa']='';
				$row['term']=$term;
				$row['subject']=$subject;
				$row['crsenumb']=$crsenumb;
				$row['title']=$title;
				$row['credits']=$credithrs;
				$row['finalgrade']=$finalgrade;
				$row['gradepoints']=$gradepoints;
				$row['sempoints']=$semesterpts;
				$row['totalpoints']=$totalpts;
				$prevterm = $term;
				$term = $row['shrtckn_term_code'];
				$credithrs = $row['shrtckg_credit_hours'];
				$finalgrade = $row['shrtckg_grde_code_final'];
				$subject = $row['shrtckn_subj_code'];
				$crsenumb = $row['shrtckn_crse_numb'];
				$title = $row['shrtckn_crse_title'];
				if ($term != $prevterm || $cntr==$elements)
				{
					$row['semgpa']=$semgpa;
					$row['cumgpa']=$cumgpa;
					if($cntr==$elements)
					{
						$gradepoints = $row['shrtckg_qual_pts']; 
						$semesterpts = $semesterpts + $row['shrtckg_qual_pts'];
						$totalpts = $totalpts + $row['shrtckg_qual_pts'];
						$totalcrearned = $totalcrearned + $row['shrtckg_gpa_hours'];
						$semestercr = $semestercr + $row['shrtckg_gpa_hours'];
						$semgpa = round($semesterpts/$semestercr,2);
						$cumgpa = round($totalpts/$totalcrearned,2);
						$row['semgpa']=$semgpa;
						$row['cumgpa']=$cumgpa;
					}
					$semesterpts = 0;
					$semestercr = 0;
				}
				$this->data['transcript'][] = $row;
				$gradepoints = $row['shrtckg_qual_pts']; 
				$semesterpts = $semesterpts + $row['shrtckg_qual_pts'];
				$totalpts = $totalpts + $row['shrtckg_qual_pts'];
				$totalcrearned = $totalcrearned + $row['shrtckg_gpa_hours'];
				$semestercr = $semestercr + $row['shrtckg_gpa_hours'];
				if($semestercr > 0)
				{
					$semgpa = round($semesterpts/$semestercr,2);
					$cumgpa = round($totalpts/$totalcrearned,2);
				}
				else
				{
					$semgpa = 0;
					$cumgpa = 0;
				}
			} // end foreach
		}// end if
	}//end _load_transcript

	/**
	 * lazy loads midterm grades
	 */
	protected function _load_midterm_grades() {
		$this->midterm_grades = array();

		$query="SELECT DISTINCT *
							FROM v_student_midterm_grades
						 WHERE pidm=:pidm
               AND term_code = :term_code";
		if($results = \PSU::db('banner')->Execute($query,array( 'pidm' => $this->pidm, 'term_code' => $this->term_code ))) {
			foreach ($results as $row) {
				$this->data['midterm_grades'][] = $row;
			}//end while
		}//end if
	}//end _load_midterm_grades

	/**
	 * lazy loads reslife info
	 */
	protected function _load_reslife() {
		$this->reslife = array();

		$query="SELECT  term_code_key term_code,
										artp_desc app_type,
										mrcd_desc_application meal_plan,
										bldg_desc_room building,
										room_number room,
										rrcd_desc room_type,
										room_begin_date begin_date,
										room_end_date end_date
							FROM  as_residential_life
						 WHERE  pidm_key=:pidm
					ORDER BY term_code_key";
		if($results = \PSU::db('banner')->Execute($query,array( 'pidm' => $this->pidm))) {
			foreach ($results as $row) {
				$this->data['reslife'][] = $row;
			}//end while
		}//end if
	}//end _load_reslife

	/**
	 * lazy loads hold info
	 */
	protected function _load_holds() {
		$this->holds = array();

		$query="SELECT  sprhold_from_date from_date,
										sprhold_to_date to_date,
										stvhldd_desc description,
										sprhold_reason reason,
										sprhold_amount_owed amount
							FROM  sprhold,
										stvhldd
						 WHERE  sprhold_hldd_code=stvhldd_code
							 AND  sprhold_pidm=:pidm
					ORDER BY  sprhold_from_date";
		if($results = \PSU::db('banner')->Execute($query,array( 'pidm' => $this->pidm))) {
			foreach ($results as $row) {
				$this->data['holds'][] = $row;
			}//end while
		}//end if
	}//end _load_holds

	/**
	 * lazy loads academic standing (wps) info
	 */
	protected function _load_wps() {
		$this->wps = array();

		$query="SELECT	DISTINCT shrttrm_term_code term_code, 
										a.stvastd_desc standing,
										b.stvastd_desc deans_list,
										a.stvastd_max_reg_hours restricted_hours
							FROM	shrttrm,
										stvastd a,
										stvastd b
						 WHERE	shrttrm_pidm=:pidm
							 AND  shrttrm_astd_code_end_of_term = a.stvastd_code
							 AND	shrttrm_astd_code_dl = b.stvastd_code(+)
					ORDER BY	shrttrm_term_code";
		if($results = \PSU::db('banner')->Execute($query,array( 'pidm' => $this->pidm))) {
			foreach ($results as $row) {
				$this->data['wps'][] = $row;
			}//end while
		}//end if
	}//end _load_wps 


	/**
	 * lazy loads student notes and comments
	 */
	protected function _load_notes() {
		$this->notes = array();
		$query="SELECT *
	                  FROM sgrscmt
	                 WHERE sgrscmt_pidm=:pidm
	              ORDER BY sgrscmt_activity_date DESC";
		if($results = \PSU::db('banner')->Execute($query,array( 'pidm' => $this->pidm ))) 
		{
			foreach ($results as $row) 
			{
				$row = \PSU::cleanKeys('sgrscmt_', '', $row);
				$this->data['notes'][] = $row;
			}//end while
		}//end if
	}//end _load_notes

	/**
	 * lazy loads transfer credit and advsnced placement information
	 */
	protected function _load_transfer_credit()
	{
		$this->transfer_credit = array();
		$query="SELECT 1  
		          FROM sgrsatt 
		         WHERE sgrsatt_atts_code='OGEN' AND sgrsatt_pidm=:pidm";
		$oldgened=\PSU::db('banner')->GetOne($query, array( 'pidm' => $this->pidm ));
		$query="SELECT shrtrit_sbgi_code 
		          FROM shrtrit,shrtram,stvsbgi 
		         WHERE shrtrit_pidm=:pidm AND
		               shrtrit_pidm=shrtram_pidm AND
		               shrtrit_sbgi_code=stvsbgi_code AND
		               shrtram_trit_seq_no=shrtrit_seq_no
		      GROUP BY stvsbgi_desc, shrtrit_sbgi_code
		      ORDER BY stvsbgi_desc";
			if($results = \PSU::db('banner')->Execute($query, array( 'pidm' => $this->pidm ))) 
			{
				foreach($results as $row2)
				{
					$args = array(
						'pidm' => $this->pidm,
						'ceeb' => $row2['shrtrit_sbgi_code'],
						'levl_code' => $this->level_code
					);
					if($oldgened)
					{
						$query = "SELECT shrtrcr_term_code term_code,
						                 shrtrcr_trans_course_numbers tc_id,
						                 shrtrce_crse_title tc_title,
						                 shrtrcr_trans_credit_hours tc_credits,
						                 shrtrce_subj_code||shrtrce_crse_numb equivalent,
						                 shrtrce_credit_hours credit_hours,
						                 stvattr_desc general_ed,
						                 stvsbgi_desc college
						            FROM shrtrit LEFT OUTER JOIN
						                 stvsbgi ON (
						                     shrtrit_sbgi_code = stvsbgi_code
						                 ) JOIN
						                 shrtram ON (
						                     shrtrit_pidm = shrtram_pidm AND
																 shrtrit_seq_no = shrtram_trit_seq_no AND
																 shrtram_levl_code = :levl_code
						                 ) LEFT OUTER JOIN
						                 shrtrcr ON (
						                     shrtram_pidm = shrtrcr_pidm AND
						                     shrtrit_seq_no = shrtrcr_trit_seq_no AND
						                     shrtram_seq_no = shrtrcr_tram_seq_no
						                 ) LEFT OUTER JOIN
						                 shrtrce ON (
						                     shrtrcr_pidm = shrtrce_pidm AND
						                     shrtrce_trcr_seq_no = shrtrcr_seq_no AND
						                     shrtrce_trit_seq_no = shrtrit_seq_no AND
						                     shrtrce_tram_seq_no = shrtram_seq_no
						                 ) LEFT OUTER JOIN
						                 shrtatt ON (
						                     shrtatt_pidm = shrtrce_pidm AND
						                     shrtatt_trit_seq_no = shrtrit_seq_no AND
						                     shrtatt_tram_seq_no = shrtram_seq_no AND
						                     shrtatt_trcr_seq_no = shrtrcr_seq_no AND
						                     shrtatt_trce_seq_no = shrtrce_seq_no AND
						                     shrtatt_attr_code IN ('ARTS', 'GLOB', 'HIST', 'INTG', 'LITY', 'PHIL', 'QUAN', 'SCIE', 'SLAB', 'SCLB',
						                                           'SPSY', 'TECH', 'WRIT', 'FYSM', 'MATH', 'COMP', 'LIBS', 'CPTS', 'PACT')
						                 ) LEFT OUTER JOIN
						                 stvattr ON(shrtatt_attr_code = stvattr_code)
						           WHERE shrtrit_pidm = :pidm AND shrtrit_sbgi_code= :ceeb
						        ORDER BY stvsbgi_desc, shrtrce_term_code_eff
						";
					}
					else
					{
						$query = "SELECT	shrtrcr_term_code term_code,
															shrtrcr_trans_course_numbers tc_id,
															shrtrce_crse_title tc_title,
															shrtrcr_trans_credit_hours tc_credits,
															shrtrce_subj_code||shrtrce_crse_numb equivalent,
															shrtrce_credit_hours credit_hours,
															stvattr_desc general_ed,
															stvsbgi_desc college
												FROM	shrtrit
						 LEFT OUTER JOIN	stvsbgi ON(shrtrit_sbgi_code = stvsbgi_code)
						 JOIN	shrtram ON(shrtrit_pidm = shrtram_pidm
																				AND shrtrit_seq_no = shrtram_trit_seq_no
																			  AND shrtram_levl_code = :levl_code)
						 LEFT OUTER JOIN	shrtrcr ON(shrtram_pidm = shrtrcr_pidm
																				AND shrtrit_seq_no = shrtrcr_trit_seq_no
																				AND shrtram_seq_no = shrtrcr_tram_seq_no)
						 LEFT OUTER JOIN	shrtrce ON(shrtrcr_pidm = shrtrce_pidm
																				AND shrtrce_trcr_seq_no = shrtrcr_seq_no
																				AND shrtrce_trit_seq_no = shrtrit_seq_no
																				AND shrtrce_tram_seq_no = shrtram_seq_no)
						 LEFT OUTER JOIN	shrtatt ON(shrtatt_pidm = shrtrce_pidm
																				AND	shrtatt_trit_seq_no=shrtrit_seq_no
																				AND	shrtatt_tram_seq_no=shrtram_seq_no
																				AND	shrtatt_trcr_seq_no=shrtrcr_seq_no
																				AND	shrtatt_trce_seq_no=shrtrce_seq_no
																				AND	shrtatt_attr_code IN('CTDI', 'PPDI', 'SSDI', 'SIDI', 'DICO', 'GACO', 'INCO', 'QRCO', 'TECO', 'WECO', 
																																 'WRCO', 'FYSM', 'MATH', 'COMP'))
						 LEFT OUTER JOIN	stvattr ON(shrtatt_attr_code = stvattr_code)
											 WHERE	shrtrit_pidm = :pidm
											   AND	shrtrit_sbgi_code= :ceeb
										ORDER BY	stvsbgi_desc,
															shrtrce_term_code_eff";
					}
					if($results = \PSU::db('banner')->Execute($query, $args)) 
					{
						foreach ($results as $row) 
						{
							$this->data['transfer_credit'][] = $row;
						}//end while
					}//end if
				}// end foreach
			}//end if
		}//end _load_transfer_credit

	/**
	 * retrieves curriculumn information for person
	 */
	public function _load_curriculum()
	{
		$this->curriculum = array();

		$sql = "SELECT * FROM v_curriculum_learner WHERE pidm = :pidm AND levl_code = :levl";

		$args = array(
			'pidm' => $this->pidm,
			'levl' => $this->level_code
		);

		if($results = \PSU::db('banner')->Execute($sql, $args))
		{
			foreach($results as $row)
			{
				$type = strtolower($row['lfst_code']);
				$row['curriculum_sequence'] = $row['curriculum_seqno'];
				$major_code = $row['majr_code'];
				unset($row['lfst_code'], $row['curriculum_seqno'], $row['majr_code'], $row['pidm']);

				$this->data['curriculum'][$type][$major_code][] = $row;
			}// end while
		}//end if
	}//end _load_curriculum

	/**
	 * Returns true if the user has the specified major.
	 */
	public function has_major( $majr_code ) {
		return isset( $this->curriculum['major'][strtoupper($majr_code)] );
	}//end has_major

	/**
	 * lazy loads gpa information
	 */
	function _load_gpa()
	{
		$this->gpa = 0;
		$this->gpa_overall = 0;
		$this->gpa_institution = 0;
		$this->gpa_transfer= 0 ;
		
		$query="SELECT * FROM shrlgpa WHERE shrlgpa_pidm=:pidm AND shrlgpa_levl_code=:levl";

		$args = array(
			'pidm' => $this->pidm,
			'levl' => $this->level_code
		);

		if($results = \PSU::db('banner')->Execute($query, $args) ) {
			foreach($results as $row) {
				if($row['shrlgpa_gpa_type_ind'] == 'O') {
					$this->gpa_overall = $row['shrlgpa_gpa'];
				} elseif ($row['shrlgpa_gpa_type_ind'] == 'I') {
					$this->gpa_institution = $row['shrlgpa_gpa'];
				} elseif ($row['shrlgpa_gpa_type_ind'] == 'T') {
					$this->gpa_transfer = $row['shrlgpa_gpa'];
				}//end if
			}
			$this->gpa = $this->gpa_overall;
		}//end if
	}//end _load_gpa
}//end class PSU\Student\Data
