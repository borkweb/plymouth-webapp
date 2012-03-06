<?php

namespace PSU\Course;

class Section extends \BannerObject
{
	/* data sets: */
	public $data_loaders = array();
	public $data_sets = array();
	public $default_load = array();
	public $lib = array();
	public $data = array();
	public $course = null;
	public $failover_data = 'course';

	public function __construct($crn, $term_code, $params = null) {
		parent::__construct();

		$data_loaders = array(
			'cross_list_group' => 'cross_listed',
			'cross_list_desc' => 'cross_listed',
			'cross_list_max_enrollment' => 'cross_listed',
			'cross_list_enrollment' => 'cross_listed',
			'cross_list_seats_available' => 'cross_listed',
			'meeting_times' => 'schedule',
			'meeting_locations' => 'schedule'
		);

		$this->data_loaders = \PSU::params($this->data_loaders, $data_loaders);

		$this->crn = $crn;
		$this->term_code = $term_code;

		if($params) {
			$params = \PSU::params($params);
			$this->parse($params);
		} else {
			$this->load();
		}//end else
	}//end constructor

	/**
	 * reteive instructor data for the given type 
	 */
	public function instructors($type='pidm') {
		$person_objects;
		if ($type == 'pidm') {
			return $this->instructors;
		} elseif ($type == 'PSUPerson') {
			if ($person_objects) {
				return $person_objects;
			}
			$person_objects = array();
			foreach ($this->instructors as $key => $value) {
				$person_objects[$key] = \PSUPerson::get($value);
			}
			return $person_objects;
		}
		return null;
	}

	/**
	 * load the section data from the database
	 */
	public function load() {
		$sql = "SELECT s1.* 
			        FROM ssbsect s1
						 WHERE s1.ssbsect_crn = :crn
						   AND s1.ssbsect_term_code = :term_code";

		$args = array('crn' => $this->crn, 'term_code' => $this->term_code);

    if($results = \PSU::db('banner')->GetRow($sql, $args)) {
			$this->parse($results);
		}//end if
	}//end load

	/**
	 * parses scbcrse data into the object
	 */
	public function parse($data) {
		foreach($data as $key => $value) {
			$key = str_replace('ssbsect_', '', strtolower($key));
			
			$this->$key = $value;
		}//end foreach
		
		if($this->subj_code && $this->crse_numb) {
			$this->subject_code = $this->subj_code;
			$this->course_number = $this->crse_numb;
			$this->section_number = $this->section_num = $this->seq_numb;
			$this->status_code = $this->ssts_code;
			$this->seats_available = $this->seats_avail;
			$this->enrollment = $this->current_enroll;
			$this->max_enrollment = $this->max_enroll;
			$this->course = new \PSU\Course($this->subj_code, $this->crse_numb);

			$this->credits = $this->credit_hrs ? $this->credit_hrs : null;

			if(!$this->credits) {
				$this->credits = $this->course->credit_hr_low;
				if($this->course->credit_hr_high) {
					$this->credits .= ' to '.$this->course->credit_hr_high;
				}//end if
			}//end if
		}//end if

		//set the title.  Use the section title if available.  If not, use the course's long
		//  title if available.  If THAT isn't available, use the course title
		$this->title = $this->crse_title ? $this->crse_title : ($this->course->long_title ? $this->course->long_title : $this->course->title);
	}//end parse

	/**
	 * load cross listed information
	 */
	protected function _load_cross_listed() {
		$this->data['cross_listed'] = false;
		$this->data['cross_list_group'] = null;
		$this->data['cross_list_desc'] = null;
		$this->data['cross_list_max_enrollment'] = null;
		$this->data['cross_list_enrollment'] = null;
		$this->data['cross_list_seats_available'] = null;

		$sql = "SELECT *
							FROM ssbxlst,
							     ssrxlst
						 WHERE ssbxlst_xlst_group = ssrxlst_xlst_group
						   AND ssbxlst_term_code = ssrxlst_term_code
							 AND ssbxlst_term_code = :term_code
							 AND ssrxlst_crn = :crn";
		if($row = \PSU::db('banner')->GetRow($sql, array('term_code' => $this->term_code, 'crn' => $this->crn))) {
			$this->cross_listed = true;
			$this->cross_list_group = $row['ssbxlst_xlst_group'];
			$this->cross_list_desc = $row['ssbxlst_desc'];
			$this->cross_list_max_enrollment = $row['ssbxlst_max_enrl'];
			$this->cross_list_enrollment = $row['ssbxlst_enrl'];
			$this->cross_list_seats_available = $row['ssbxlst_seats_avail'];
		}//end if
	}//end _load_cross_listed

	/**
	 * load section description. 
	 */
	public function _load_section_description() {
		$sql = "SELECT ssbdesc_text_narrative
							FROM ssbdesc s1
						 WHERE s1.ssbdesc_term_code= :term_code
							 AND s1.ssbdesc_crn= :crn";

		$args = array(
			'term_code' => $this->term_code,
			'crn' => $this->crn
		);

		$description = \PSU::db('banner')->GetOne($sql, $args);

		if($description) $this->section_description = $description;
	}//end _load_section_description

	/**
	 * load instructors for the course
	 */
	public function _load_instructors() {
		$this->data['instructors'] = array();

		$sql = "SELECT * FROM sirasgn WHERE sirasgn_crn = :crn AND sirasgn_term_code = :term_code";

		$args = array(
			'crn' => $this->crn,
			'term_code' => $this->term_code
		);

		if($results = \PSU::db('banner')->Execute($sql, $args)) {
			foreach($results as $row) {
				$row = \PSU::cleanKeys('sirasgn_', '', $row);
				$this->data['instructors'][] = $row['pidm'];
			}//end foreach
		}//end if
	}//end _load_instructors

	/**
	 * load course roster
	 */
	public function _load_roster() {
		$this->data['roster'] = array();

		$sql = "SELECT * 
			        FROM sfrstcr 
						 WHERE sfrstcr_crn = :crn 
						   AND sfrstcr_term_code = :term_code
						 ORDER BY sfrstcr_add_date";
		
		$args = array(
			'crn' => $this->crn,
			'term_code' => $this->term_code
		);

		if($results = \PSU::db('banner')->Execute($sql, $args)) {
			foreach($results as $row) {
				$row = \PSU::cleanKeys('sfrstcr_', '', $row);
				$this->data['roster'][] = $row['pidm'];
			}//end foreach
		}//end if
	}//end _load_roster

	/**
	 * load the meeting time information for the course
	 */
	public function _load_schedule() {
		$this->data['schedule'] = array();

		$sql = "SELECT ssrmeet_begin_time,
									 ssrmeet_end_time,
									 ssrmeet_bldg_code building_code,
									 stvbldg_desc building,
									 ssrmeet_room_code room_number,
									 ssrmeet_start_date,
									 ssrmeet_end_date,
									 ssrmeet_catagory ssrmeet_category,
									 ssrmeet_sun_day sunday,
									 ssrmeet_mon_day monday,
									 ssrmeet_tue_day tuesday,
									 ssrmeet_wed_day wednesday,
									 ssrmeet_thu_day thursday,
									 ssrmeet_fri_day friday,
									 ssrmeet_sat_day saturday,
									 ssrmeet_schd_code schedule_type_code,
									 stvschd_desc schedule_type,
									 ssrmeet_credit_hr_sess session_credit_hours,
									 ssrmeet_meet_no num_meeting_times,
									 ssrmeet_hrs_week hours_per_week
							FROM ssrmeet,
                   stvbldg,
                   stvschd
					   WHERE ssrmeet_crn = :crn 
							 AND ssrmeet_term_code = :term_code
               AND stvbldg_code = ssrmeet_bldg_code
							 AND stvschd_code = ssrmeet_schd_code
             ORDER BY ssrmeet_start_date, ssrmeet_end_date, ssrmeet_begin_time";		

		$args = array(
			'crn' => $this->crn,
			'term_code' => $this->term_code
		);

		if($results = \PSU::db('banner')->Execute($sql, $args)) {
			foreach($results as $row) {
				$row = \PSU::cleanKeys('ssrmeet_', '', $row);
				$row['begin_time'] = preg_replace('/([0-9]{2})([0-9]{2})/', '\1:\2', $row['begin_time']);
				$row['end_time'] = preg_replace('/([0-9]{2})([0-9]{2})/', '\1:\2', $row['end_time']);
				$row['start_date'] = strtotime($row['start_date']);
				$row['end_date'] = strtotime($row['end_date']);
				$row['days'] = $row['sunday'].$row['monday'].$row['tuesday'].$row['wednesday'].$row['thursday'].$row['friday'].$row['saturday'];
				$this->data['schedule'][] = $row;
			}//end foreach
		}//end if
		$this->meeting_times = $this->meeting_locations = $this->schedule;
	}//end _load_schedule

	/**
	 * load the friendly status description
	 */
	public function _load_status() {
		$sql = "SELECT stvssts_desc FROM stvssts WHERE stvssts_code = :status_code";
		$this->status = \PSU::db('banner')->GetOne($sql, array('status_code' => $this->status_code));
	}//end _load_status
}//end PSU\Course\Section
