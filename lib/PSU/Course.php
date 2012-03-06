<?php

namespace PSU;

/**
 * PSU Course API: class for aggregating information about courses
 */

class Course extends \BannerObject
{
	/* data sets: */
	public $data_loaders = array();
	public $data_sets = array();
	public $default_load = array();
	public $lib = array();
	public $data = array();


	/**
	 * PSUCourse constructor
	 *
	 * valid params (in addition to scbcrse data) used in course selection
	 *   term = single/array of term codes from which to query
	 *   crn = single/array of crns from which to query
	 *   since = date selection should begin
	 *   until = date selection should end
	 *   term_low = term selection should begin
	 *   term_high = term selection should end
	 *
	 * @param $identifier \b Identifier of user
	 * @param $load \b data sets to be loaded
	 */
	public function __construct($subject_code, $course_number, $params = null) {
		parent::__construct();

		$data_loaders = array(
			'grading_mode_code' => 'grading_mode',
			'gmod_code'          => 'grading_mode',
			'instructors_unique' => 'instructors',
			'instructors_term_unique' => 'instructors',
			'average_enrollment' => 'rosters'
		);

		$this->data_loaders = \PSU::params($this->data_loaders, $data_loaders);

		$this->subj_code = strtoupper($subject_code);
		$this->crse_numb = strtoupper($course_number);

		if($params) {
			$params = \PSU::params($params);
			if($params['term_code']) $params['term'] = $params['term_code'];
			if($params['crn']) $this->crn = $params['crn'];
			if($params['term']) $this->term = $params['term'];
			if($params['term_low']) $this->term_low = $params['term_low'];
			if($params['term_high']) $this->term_high = $params['term_high'];
			if($params['since']) $this->since = strtotime($params['since']);
			if($params['until']) $this->until = strtotime($params['until']);

			unset($params['crn'], $params['term'], $params['term_code'], $params['term_low'], $params['term_high'], $params['since'], $params['until']);

			if(!empty($params)) $this->parse($params);
		}//end if

		$this->load();
	}//end constructor

	public function credits() {
		if( $this->credit_hr_high ) {
			return "{$this->credit_hr_low}-{$this->credit_hr_high}";
		} else {
			return "{$this->credit_hr_low}";
		}
	}//end credits

	/**
	 * True if the course was found in the database.
	 */
	public function exists() {
		return (bool)($this->subject_code && $this->course_number);
	}//end exists

	/**
	 * helper function to return specific instructor populations
	 */
	public function instructors($return = 'full') {
		if(!$this->instructors) $this->_load_instructors();
		switch($return) {
			case 'unique':
				return $this->instructors_unique;
				break;
			case 'term_unique':
				return $this->instructors_term_unique;
				break;
			case 'full':
			default:
				return $this->instructors;
				break;
		}//end switch
	}//end instructors

	/**
	 * load base course info using passed in subject code and course number
	 */
	public function load() {
		$sql = "SELECT s1.* 
			        FROM scbcrse s1
						 WHERE s1.scbcrse_subj_code = :subj_code
						   AND s1.scbcrse_crse_numb = :crse_numb
							 AND s1.scbcrse_eff_term = (
                     SELECT max(s2.scbcrse_eff_term)
                       FROM scbcrse s2
                      WHERE s2.scbcrse_subj_code = s1.scbcrse_subj_code
                        AND s2.scbcrse_crse_numb = s1.scbcrse_crse_numb
                   )";

		$args = array('subj_code' => $this->subj_code, 'crse_numb' => $this->crse_numb);

		if($results = \PSU::db('banner')->GetRow($sql, $args)) {
			$this->parse($results);
		}//end if
	}//end load

	/**
	 * parses scbcrse data into the object
	 */
	public function parse($data) {
		foreach($data as $key => $value) {
			$key = str_replace('scbcrse_', '', strtolower($key));
			
			$this->$key = $value;
		}//end foreach

		$this->subject_code = $this->subj_code;
		$this->course_number = $this->crse_numb;
	}//end parse

	/**
	 * loads course attributes of the course
	 */
	public function _load_attributes() {
		$this->attributes = array();
		$sql="SELECT stvattr_code,stvattr_desc
						FROM scrattr s1,stvattr
					 WHERE s1.scrattr_subj_code = :subj_code
						 AND s1.scrattr_crse_numb = :crse_numb
						 AND s1.scrattr_attr_code = stvattr_code
						 AND s1.scrattr_eff_term = (
									 SELECT max(s2.scrattr_eff_term) 
										 FROM scrattr s2 
										WHERE s2.scrattr_subj_code=s1.scrattr_subj_code 
											AND s2.scrattr_crse_numb=s1.scrattr_crse_numb 
											AND s2.scrattr_eff_term <= (SELECT max(term_code) FROM psu.v_current_term WHERE housing_active = 'Y')
                 ) 
           ORDER BY stvattr_code";
		$args = array(
			'subj_code' => $this->subj_code,
			'crse_numb' => $this->crse_numb
		);

		if($results = \PSU::db('banner')->Execute($sql, $args)) {
			foreach($results as $row) {
				$row = \PSU::cleanKeys('stvattr_', '', $row);
				$this->data['attributes'][$row['code']] = $row['desc'];
			}//end if
		}//end if
	}//end _load_attributes

	/**
	 * loads section data based on the selection criteria specified in the PSUCourse instantiation
	 */
	public function _load_courses() {
		$this->data['courses'] = array();

		$sql = "SELECT ssbsect.* 
							FROM ssbsect,
                   stvterm 
						 WHERE ssbsect_subj_code = :subj_code 
							 AND ssbsect_crse_numb = :crse_numb
               AND ssbsect_term_code = stvterm_code
							 AND stvterm_start_date >= :start_date
             ORDER BY stvterm_code";
		$args = array(
			'subj_code' => $this->subj_code,
			'crse_numb' => $this->crse_numb,
			'start_date' => $this->since ? date('Y-m-d', $this->since) : date('Y-m-d', strtotime('-3 years'))
		);

		if($this->crn) {
			$sql .= " AND ssbsect_crn IN (".implode(',', ((array) $this->crn)).")";
		}//end if

		if($this->term) {
			$sql .= " AND ssbsect_term_code IN ('".implode("','", ((array) $this->term))."')";
		}//end if

		if($this->term_low) {
			$sql .= " AND ssbsect_term_code >= :term_low";
			$args['term_low'] = $this->term_low;
		}//end if

		if($this->term_high) {
			$sql .= " AND ssbsect_term_code <= :term_high";
			$args['term_high'] = $this->term_high;
		}//end if

		if($this->until) {
			$sql .= " AND stvterm_end_date <= :until";
			$args['until'] = date('Y-m-d', $this->until);
		}//end if

		if($results = \PSU::db('banner')->Execute($sql, $args)) {
			foreach($results as $row) {
				$row = \PSU::cleanKeys('ssbsect_', '', $row);
				$this->data['courses'][$row['term_code']][$row['crn']] = new Course\Section($row['crn'], $row['term_code'], $row);
			}//end foreach
		}//end if
	}//end _load_courses 

	/**
	 * load the friendly department description
	 */
	public function _load_department() {
		$sql = "SELECT stvdept_desc FROM stvdept WHERE stvdept_code = :dept_code";
		$this->department = \PSU::db('banner')->GetOne($sql, array('dept_code' => $this->dept_code));
	}//end _load_department

	/**
	 * load description for the course
	 */
	public function _load_description() {
		$sql="SELECT scbdesc_text_narrative 
						FROM scbdesc s1
					 WHERE s1.scbdesc_term_code_eff = (
									 SELECT max(s2.scbdesc_term_code_eff) 
										 FROM scbdesc s2 
										WHERE s2.scbdesc_subj_code=s1.scbdesc_subj_code 
											AND s2.scbdesc_crse_numb=s1.scbdesc_crse_numb 
											AND s2.scbdesc_term_code_eff<= (SELECT max(term_code) FROM psu.v_current_term WHERE housing_active = 'Y')
								 )
						 AND s1.scbdesc_subj_code=:subj_code
						 AND s1.scbdesc_crse_numb=:crse_numb";
		$args = array(
			'subj_code' => $this->subj_code,
			'crse_numb' => $this->crse_numb
		);

		$description = \PSU::db('banner')->GetOne($sql, $args);
		$this->description = $description ? $description : null;
	}//end _load_description

	/**
	 * load grading mode of the course
	 */
	public function _load_grading_mode() {
		$sql = "SELECT stvgmod_code, stvgmod_desc
							FROM scrgmod s1, stvgmod
						 WHERE s1.scrgmod_eff_term=(
										 SELECT max(s2.scrgmod_eff_term) 
											 FROM scrgmod s2 
											WHERE s2.scrgmod_subj_code=s1.scrgmod_subj_code 
												AND s2.scrgmod_crse_numb=s1.scrgmod_crse_numb 
                        AND s2.scrgmod_eff_term <= (SELECT max(term_code) FROM psu.v_current_term WHERE housing_active = 'Y'))
							 AND s1.scrgmod_subj_code=:subj_code
							 AND s1.scrgmod_crse_numb=:crse_numb
               AND s1.scrgmod_gmod_code = stvgmod_code";
		$args = array(
			'subj_code' => $this->subj_code,
			'crse_numb' => $this->crse_numb
		);
		if($grading_mode = \PSU::db('banner')->GetRow($sql, $args)) {
			$this->grading_mode = $grading_mode['stvgmod_desc'];
			$this->grading_mode_code = $this->gmod_code = $grading_mode['stvgmod_code'];
		}//end if
	}//end _load_grading_mode

	/**
	 * load instructor information based on course selection criteria
	 */
	public function _load_instructors() {
		$this->instructors = array();
		$this->instructors_unique = array();
		$this->instructors_term_unique = array();

		foreach($this->courses as $term_code => $term) {
			$this->data['instructors_term_unique'][$term_code] = array();
			foreach($term as $crn => $course) {
				$this->data['instructors'][$term_code][$crn] = $course->instructors;
				foreach($course->instructors as $instructor) {
					$this->data['instructors_unique'][] = $instructor;
					$this->data['instructors_term_unique'][$term_code][] = $instructor;
				}//end foreach
			}//end foreach

			$this->data['instructors_term_unique'][$term_code] = array_unique($this->data['instructors_term_unique'][$term_code]);
		}//end foreach

		$this->data['instructors_unique'] = array_unique($this->data['instructors_unique']);
	}//end _load_instructors

	/**
	 * get the course's last active term
	 */
	protected function _load_last_active_term() {
		$this->data['last_active_term'] = $this->eff_term;

		$this->last_active_term = array_pop( array_keys( $this->courses ) );
	}//end _load_last_active_term

	/**
	 * load course level
	 */
	public function _load_level() {
		$sql = "SELECT *
							FROM scrlevl s1
						 WHERE s1.scrlevl_eff_term=(
										SELECT max(s2.scrlevl_eff_term) 
											FROM scrlevl s2 
										 WHERE s2.scrlevl_subj_code=s1.scrlevl_subj_code 
											 AND s2.scrlevl_crse_numb=s1.scrlevl_crse_numb 
                       AND s2.scrlevl_eff_term <= (SELECT max(term_code) FROM psu.v_current_term WHERE housing_active = 'Y')
                   )
							 AND s1.scrlevl_subj_code= :subj_code
							 AND s1.scrlevl_crse_numb= :crse_numb";
		$args = array(
			'subj_code' => $this->subj_code,
			'crse_numb' => $this->crse_numb
		);
		$level = \PSU::db('banner')->GetOne($sql, $args);
		$this->level = $level ? $level : null;
	}//end _load_level

	/**
	 * load the long title of the course
	 */
	public function _load_long_title() {
		$sql = "SELECT scrsyln_long_course_title
							FROM scrsyln s1
						 WHERE s1.scrsyln_term_code_eff=(
										 SELECT max(s2.scrsyln_term_code_eff) 
											 FROM scrsyln s2 
											WHERE s2.scrsyln_subj_code=s1.scrsyln_subj_code 
												AND s2.scrsyln_crse_numb=s1.scrsyln_crse_numb 
                        AND s2.scrsyln_term_code_eff <= (SELECT max(term_code) FROM psu.v_current_term WHERE housing_active = 'Y') 
                        AND (s2.scrsyln_term_code_end > (SELECT max(term_code) FROM psu.v_current_term WHERE housing_active = 'Y') 
                             OR 
                             s2.scrsyln_term_code_end is null
                            )
                   )
							 AND s1.scrsyln_subj_code= :subj_code
							 AND s1.scrsyln_crse_numb= :crse_numb";
		$args = array(
			'subj_code' => $this->subj_code,
			'crse_numb' => $this->crse_numb
		);
		$long_title = \PSU::db('banner')->GetOne($sql, $args);
		$this->long_title = $long_title ? $long_title : null;
	}//end _load_long_title

	/**
	 * load roster information based on course selection criteria
	 */
	public function _load_rosters() {
		$this->rosters = array();

		$student_count = 0;
		$course_count = 0;

		foreach($this->courses as $term_code => $term) {
			foreach($term as $crn => $course) {
				$this->data['rosters'][$term_code][$crn] = $course->roster;
				if(count($course->roster) > 0) {
					$course_count++;
					$student_count += count($course->roster);
				}//end if
			}//end foreach
		}//end foreach

		$this->average_enrollment = $course_count ? ($student_count / $course_count) : 0;
	}//end _load_rosters

	/**
	 * load course text
	 */
	public function _load_text() {
		$sql = "SELECT scrtext_text
							FROM scrtext s1
						 WHERE s1.scrtext_subj_code = :subj_code
							 AND s1.scrtext_crse_numb = :crse_numb
							 AND s1.scrtext_text_code = 'A'
							 AND s1.scrtext_eff_term = (
										 SELECT max(s2.scrtext_eff_term) 
											 FROM scrtext s2 
											WHERE s2.scrtext_subj_code=s1.scrtext_subj_code 
												AND s2.scrtext_crse_numb=s1.scrtext_crse_numb 
												AND s2.scrtext_eff_term <= (SELECT max(term_code) FROM psu.v_current_term WHERE housing_active = 'Y')
                   ) 
             ORDER BY scrtext_seqno";
		$args = array(
			'subj_code' => $this->subj_code,
			'crse_numb' => $this->crse_numb
		);
		$text = \PSU::db('banner')->GetOne($sql, $args);
		$this->text = $text ? $text : '';
	}//end _load_text
}//end PSU\Course
