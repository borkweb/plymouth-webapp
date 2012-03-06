<?php
/**
 * contains SQL statements for ResLife 
 *
 *
 * access public
 * @version		0.0.1
 * @module		ResLifeSQL.class.php
 * @author		Betsy Coleman <bscoleman@plymouth.edu>
 * @copyright 2009, Plymouth State University, Residential Life
 */ 

 /**
 * contains sql definitions
 *
 * @package ResLifeSQL.class.php
 */ 
class ResLifeSQL
{

/**
 *
 * contains object of database
 * @access public
 * @var object
 *
 **/

 /**
 * sets instance of db connection to var db
 * sets instance of resutil connection to var resutil
 *
 * @param object $db
 * @param object $resutil
 * @access public
 *
 **/
  function __construct()
  {
  }

/**
 * add an Incident Report to the MySQL reslife database
 * 
 * @param int $pidm 
 * @access public
 * @return string of query
 *
 */ 
	function addIncidentPerson($data) 
	{
		$sql = "REPLACE INTO incident_people 
							(
								incident_id,
								person_type,
								lastname,
								firstname,
								person_id,
								username,
								pidm,
								dob,
								building,
								room
							)
						VALUES 
							(
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?
							)";

		$rs = PSU::db('reslife')->Execute($sql,
						array(
								$data['incident_id'],
								$data['person_type'],
								$data['lastname'],
								$data['firstname'],
								$data['person_id'],
								$data['username'],
								$data['pidm'],
								$data['dob'],
								$data['building'],
								$data['room'],
								));
	}

/**
 * add an Incident Report to the MySQL reslife database
 * 
 * @param int $pidm 
 * @access public
 * @return string of query
 *
 */ 
	function addIncidentReport($filer, $data, $now) 
	{
		// The incident report spans 2 tables, the incident_reports table and the
		// incident_people table. The incident_people table is used to store the 
		// people who were either involved in the incident - usually students and 
		// staff_involved, those people dealing with the incident

		// Since using MySQL, can use a single REPLACE instead of check for existence
		// and then having to use INSERT or UPDATE
		//
		$sql = "REPLACE INTO incident_reports 
							(
								i_datetime,
								location_code,
								location,
								filer_pidm,
								filer_id,
								filer_username,
								description,
								action_taken,
								created,
								modified
							)
						VALUES 
							(
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?
							)";

		$rs = PSU::db('reslife')->Execute($sql,
						array(
								$data['i_datetime'],
								$data['ilocation_code'],
								$data['loc_desc'],
								$filer->pidm,
								$filer->id,
								$filer->username,
								$data['i_description'],   
								$data['action_taken'],
								$now,
								$now
								));

		$incident_id = PSU::db('reslife')->Insert_ID();

    foreach($data['iStudents'] as $key => $student)
    {
			if (!empty($student['si_lname']))
			{
			  $savedata = array(
								'incident_id' => $incident_id,
								'person_type' => 'STUDENT',
								'lastname' => $student['si_lname'],
								'firstname' => $student['si_fname'],
								'person_id' => $student['si_id'],
								'username' => '',	// HARD CODED FOR NOW
								'pidm' => $student['si_lname'],
								'dob' => $student['si_dob'],
								'building' => $student['si_building'],
								'room' => $student['si_room'],
				);
			$this->addIncidentPerson($savedata);
			}
		} //end foreach

    foreach($data['iStaff'] as $key => $staff)
    {
			if (!empty($staff['staff_lname']))
			{
			  $savedata = array(
								'incident_id' => $incident_id,
								'person_type' => 'STAFF',
								'lastname' => $staff['staff_lname'],
								'firstname' => $staff['staff_fname'],
				);
			$this->addIncidentPerson($savedata);
			}
		} //end foreach

	return $incident_id;
	}

/**
 * get the list of application that are available
 * 
 * @access public
 * @return string of query
 *
 */ 
	function getHousingApplicationOptions($app_type, $admin) 
	{
		// if in administration - get ALL the options that are available - TBD SORT ORDER FOR THESE
		//
		if (isset($admin))
		{
			$sql = "SELECT * FROM `housing_app_terms` terms";
			$rs = PSU::db('reslife')->Execute($sql);
		}
	else
		{
			$sql = "SELECT * FROM `housing_app_terms` terms 
								WHERE terms.app_type=?
								AND terms.enabled=1";
			$rs = PSU::db('reslife')->Execute($sql, array($app_type, $area));
		}

	return $rs->GetRows();
	}
/**
 * get the list of application that are available
 * 
 * @access public
 * @return string of query
 *
 */ 
	function getHousingApplicationOption($id=-1) 
	{
	  if ($id == -1)
		{
			$sql = "SELECT `id`  FROM `housing_app_terms` WHERE `default_admin_term` = 1";
			return PSU::db('reslife')->GetOne($sql);
		}
		else
		{
		  $sql = "SELECT * FROM `housing_app_terms` WHERE `id`=?";
			$rs = PSU::db('reslife')->Execute($sql, array($id));
			return $rs->GetRows();
		}

	}

/**
 * get the lease agreement from Housing Options Table
 * 
 * @param int $pidm 
 * @access public
 * @return string of query
 *
 */ 
	function getLease($term) 
	{
		$sql = "SELECT lease_name 
							FROM `housing_app_terms` 
							WHERE year_term=?";

	return PSU::db('reslife')->GetOne($sql, array($term));
	}
/**
 * get The saved liveon-campus information
 * 
 * @param int $pidm 
 * @access public
 * @return string of query
 *
 */ 
	function getStaffInfo($person_type, $building = null) 
	{
		if (isset($building))
		{
			$sql = "SELECT * FROM `staff-info` info 
							WHERE info.person_type=?
								AND info.building=?";

			$rs = PSU::db('reslife')->Execute($sql, array($person_type, $building));
		}
		else
		{
			$sql = "SELECT * FROM `staff-info` info 
							WHERE info.person_type=?";

			$rs = PSU::db('reslife')->Execute($sql, array($person_type));
		}
		return $rs->GetRows();
	}

/**
 * gets the SQL statement for all students active in housing, 
 * is used by other reports
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @access public
 * @return string of query
 *
 */ 
	function getSQLAllInHousing($term, $gterm) 
	{
		return "SELECT slrrasg.slrrasg_term_code, 
									slrrasg.slrrasg_PIDM, 
									slrrasg.slrrasg_BLDG_CODE, 
									slrrasg.slrrasg_room_number, 
									slrrasg.slrrasg_rrcd_code, 
									slrrasg.slrrasg_begin_date, 
									slrrasg.slrrasg_end_date, 
									slrrasg.slrrasg_total_days, 
									slrrasg.slrrasg_ascd_code, 
									slrrasg.slrrasg_ascd_date 
						FROM slrrasg 
						WHERE slrrasg.slrrasg_term_code IN (:term,:gterm)
							AND slrrasg.slrrasg_ascd_code='AC'";
	}

/**
 * gets SQL statement for all meal plans, is used by other reports
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @access public
 * @return string of query
 *
 */ 
	function getSQLAllResMealPlans($term, $gterm) 
	{
		return "SELECT slrmasg.slrmasg_term_code, 
									slrmasg.slrmasg_pidm, 
									slrmasg.slrmasg_mrcd_code, 
									slrmasg.slrmasg_begin_date, 
									slrmasg.slrmasg_end_date, 
									slrmasg.slrmasg_mscd_code, 
									slrmasg.slrmasg_mscd_date 
						FROM slrmasg 
						WHERE slrmasg.slrmasg_term_code IN (:term, :gterm)
							AND slrmasg.slrmasg_mscd_code='AC'";
	}

/**
 * get SQL for all rooms in res halls, is used by other reports
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @access public
 * @return string of query
 *
 */ 
	function getSQLAllRoomsCurAndPrev($term, $gterm) 
	{
		return "SELECT slbrdef.slbrdef_term_code_eff, 
					 slbrdef.slbrdef_bldg_code, 
					 slbrdef.slbrdef_room_number, 
	     	   slbrdef.slbrdef_capacity, 
					 slbrdef.slbrdef_maximum_capacity, 
					 slbrdef.slbrdef_rmst_code, 
					 slbrdef.slbrdef_rrcd_code, 
					 slbrdef.slbrdef_phone_area, 
					 slbrdef.slbrdef_phone_number, 
					 slbrdef.slbrdef_sex, 
					 slbrdef.slbrdef_room_type, 
					 slbrdef.slbrdef_area
			FROM slbrdef
			 WHERE (slbrdef.slbrdef_term_code_eff <= :term
			    OR slbrdef.slbrdef_term_code_eff <= :gterm) 
			   AND slbrdef.slbrdef_bldg_code!='PEND' 
			   AND slbrdef.slbrdef_rmst_code='AC' 
			   AND slbrdef.slbrdef_room_type='D' 
			ORDER BY slbrdef.slbrdef_bldg_code ASC, 
			   slbrdef.slbrdef_room_number ASC ";
	}

/**
 * get SQL for all rooms in res halls in current term, is used by other reports
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @access public
 * @return string of query
 *
 */ 
	function getSQLAllRoomsCurTermWithData($term, $gterm) 
	{
		$max_term_code_eff_slbrdef = $this->getSQLMaxTermCodeEffSlbrdef($term, $gterm);

		return "SELECT wd.slbrdef_bldg_code, 
									wd.slbrdef_room_number, 
									wd.slbrdef_capacity, 
									wd.slbrdef_maximum_capacity, 
									wd.slbrdef_rmst_code, 
									wd.slbrdef_rrcd_code, 
									wd.slbrdef_phone_area, 
									wd.slbrdef_phone_number, 
									wd.slbrdef_sex, 
									wd.slbrdef_room_type, 
									wd.slbrdef_area, 
									wd.slbrdef_term_code_eff 
							FROM ($max_term_code_eff_slbrdef) ctwd 
							INNER JOIN slbrdef wd 
									ON ctwd.Maxofslbrdef_term_code_eff=wd.slbrdef_term_code_eff 
									 AND ctwd.slbrdef_bldg_code=wd.slbrdef_bldg_code 
									 AND ctwd.slbrdef_room_number=wd.slbrdef_room_number
							ORDER BY wd.slbrdef_bldg_code, 
										wd.slbrdef_room_number";
	}

/**
 * get SQL for all students, their room info and any attributes they specified, 
 * ordered by deposit date
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @access public
 * @return string of query
 *
 */ 
	function getAttributesByhdepo($term, $gterm) 
	{	
		$sql = "SELECT DISTINCT tbrdepo.tbrdepo_entry_date,
								TO_CHAR(tbrdepo.tbrdepo_entry_date, 'MM/DD/YYYY') as tbrdepo_entry_date,
								TO_CHAR(tbrdepo.tbrdepo_release_date, 'MM/DD/YYYY') as tbrdepo_release_date,
								tbrdepo.tbrdepo_term_code,
								baninst1.as_admissions_applicant.id,
								baninst1.as_admissions_applicant.last_name,
								baninst1.as_admissions_applicant.first_name,
								baninst1.as_admissions_applicant.middle_initial,
								baninst1.as_admissions_applicant.phone_area_code1 || '-' || SUBSTR(baninst1.as_admissions_applicant.phone_number1, 0,3) || '-' || SUBSTR(baninst1.as_admissions_applicant.phone_number1, 4,4) phone,
								stvrdef.stvrdef_desc								
				 FROM tbrdepo 
					LEFT JOIN baninst1.as_admissions_applicant 
						ON tbrdepo.tbrdepo_pidm=baninst1.as_admissions_applicant.pidm_key
						INNER JOIN slbrmap 
							ON tbrdepo.tbrdepo_term_code=slbrmap.slbrmap_from_term
						   AND tbrdepo.tbrdepo_pidm=slbrmap.slbrmap_pidm
						INNER JOIN slrpreq 
							ON slbrmap.slbrmap_from_term=slrpreq.slrpreq_term_code
						   AND slbrmap.slbrmap_pidm=slrpreq.slrpreq_pidm
						INNER JOIN stvrdef 
							ON slrpreq.slrpreq_rdef_code=stvrdef.stvrdef_code
			    WHERE ((tbrdepo.tbrdepo_term_code IN (:term,:gterm)  
					  AND (tbrdepo.tbrdepo_detail_code_deposit='IZRM' OR tbrdepo.tbrdepo_detail_code_deposit='IZRO')))
			 ORDER BY tbrdepo.tbrdepo_entry_date, 
					  baninst1.as_admissions_applicant.last_name, 
					  baninst1.as_admissions_applicant.first_name";

    $rs = PSU::db('banner')->Execute($sql, compact('term', 'gterm'));
    return $rs->GetRows();
	} 


/**
 * get SQL for the base select for Birthday queries
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @param string $building_code which building querying for
 * @access public
 * @return string of query
 *
 */ 
	function getSQLBirthdayBase($term, $gterm, $building_code) 
	{
		return "SELECT sp.spriden_id, 
									 sp.spriden_last_name, 
									 sp.spriden_first_name, 
									 sp.spriden_mi, 
									 sl.slrrasg_bldg_code, 
					   CASE (length(sl.slrrasg_room_number)) 
					    WHEN 2 
								THEN CONCAT('0', sl.slrrasg_room_number) 
					    WHEN 1 
								THEN CONCAT('00' , sl.slrrasg_room_number) 
					    ELSE sl.slrrasg_room_number 
					   END as slrrasg_room_number, 
					   EXTRACT (month from sb.spbpers_birth_date) as month, 
					   EXTRACT (day from sb.spbpers_birth_date) as day, 
									 sb.spbpers_birth_date, 
									 sl.slrrasg_ascd_code, 
									 sl.slrrasg_term_code, 
									 sp.spriden_change_ind 
							FROM slrrasg sl 
							INNER JOIN spriden sp 
								ON sl.slrrasg_pidm=sp.spriden_pidm 
							INNER JOIN spbpers sb 
								ON sl.slrrasg_pidm=sb.spbpers_pidm 
						 WHERE sl.slrrasg_bldg_code=:building_code 
								 AND sl.slrrasg_ascd_code='AC' 
								 AND sl.slrrasg_term_code IN (:term,:gterm) 
					   AND sp.spriden_change_ind is null ";
	}


/**
 * get all students living in the specified hall and their birthdate, ordered by month then day
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @param string $building_code which building querying for
 * @access public
 * @return string of query
 *
 */ 
	function getBirthdaysByMonthDay($term, $gterm, $building_code) 
	{
	  $sql = $this->getSQLBirthdayBase($term, $gterm, $building_code);

		$sql .= " ORDER BY month, day";

		$rs = PSU::db('banner')->Execute($sql, compact('term', 'gterm', 'building_code'));

		return $rs->GetRows();
	}

/**
 * get all students living in the specified hall and their birthdate, ordered by name
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @param string $building_code two letter code for residence hall
 * @access public
 * @return string of query
 *
 */ 
	function getBirthdaysByName($term, $gterm, $building_code) 
	{
	  $sql = $this->getSQLBirthdayBase($term, $gterm, $building_code);

		$sql .= " ORDER BY sp.spriden_last_name";

		$rs = PSU::db('banner')->Execute($sql, compact('term', 'gterm', 'building_code'));

		return $rs->GetRows();
	}

/**
 * get a blank roster for all rooms in the specified hall
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @param string $building_code two letter code for residence hall
 * @access public
 * @return string of query
 *
 */ 
	function getBlank($building_code)
	{
		$sql = "SELECT sl.slbrdef_bldg_code, 
					   CASE (length(sl.slbrdef_room_number)) 
							WHEN 2 
									THEN CONCAT('0', sl.slbrdef_room_number) 
					    WHEN 1 
								THEN CONCAT('00' , sl.slbrdef_room_number) 
					    ELSE sl.slbrdef_room_number 
					   END as slbrdef_room_number, 
								 sl.slbrdef_capacity, 
								 sl.slbrdef_maximum_capacity, 
								 sl.slbrdef_sex, 
								 sl.slbrdef_rmst_code, 
								 sl.slbrdef_rrcd_code, 
								 sl.slbrdef_desc, 
								 MaxTermCodeEff 
				  FROM (SELECT sb.slbrdef_bldg_code, 
							   sb.slbrdef_room_number, 
							   max(sb.slbrdef_term_code_eff) as MaxTermCodeEff 
						FROM slbrdef sb 
					  GROUP BY sb.slbrdef_bldg_code, 
							   sb.slbrdef_room_number 
						HAVING sb.slbrdef_bldg_code != 'PEND' 
					  ORDER BY sb.slbrdef_bldg_code, 
							   sb.slbrdef_room_number, 
							   MaxTermCodeEff) met 
					INNER JOIN slbrdef sl ON met.slbrdef_bldg_code=sl.slbrdef_bldg_code 
						   AND met.slbrdef_room_number=sl.slbrdef_room_number 
						   AND met.MaxTermCodeEff=sl.slbrdef_term_code_eff 
					WHERE sl.slbrdef_bldg_code=:building_code 
							AND sl.slbrdef_rmst_code='AC' 
							AND sl.slbrdef_room_type='D' 
					 ORDER BY sl.slbrdef_bldg_code, 
							slbrdef_room_number";

		$rs = PSU::db('banner')->Execute($sql, compact('building_code'));
		return $rs->GetRows();
	}

/**
 * get SQL for base select for Checkin Queries
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @access public
 * @return string of query
 *
 */ 
	function getSQLCheckinBase($term, $gterm) 
	{
		$all_rooms_current_term_with_data = $this->getSQLAllRoomsCurTermWithData($term, $gterm);
	
		$sql = "SELECT slrrasg.slrrasg_term_code, 
					   slrrasg.slrrasg_bldg_code, 
					   CASE (length(slrrasg.slrrasg_room_number)) 
					    WHEN 2 
					    THEN CONCAT('0', slrrasg.slrrasg_room_number) 
					    WHEN 1 
					    THEN CONCAT('00' , slrrasg.slrrasg_room_number) 
					    ELSE slrrasg.slrrasg_room_number 
					   END as slrrasg_room_number,  
					   adt.slbrdef_maximum_capacity, 
					   adt.slbrdef_capacity, 
					   spriden.spriden_last_name, 
					   spriden.spriden_first_name, 
					   spriden.spriden_mi, 
					   spriden.spriden_id, 
					   meal.slrmasg_mscd_code, 
					   meal.slrmasg_mrcd_code, 
					   spbpers.spbpers_sex, 
					   spbpers.spbpers_birth_date,     
					   adt.slbrdef_rrcd_code, 
					   slrrasg.slrrasg_rrcd_code,  
					   spriden.spriden_change_ind 
				  FROM slrrasg 
					LEFT JOIN ($all_rooms_current_term_with_data) adt 
						ON slrrasg.slrrasg_bldg_code=adt.slbrdef_bldg_code 
					   AND slrrasg.slrrasg_room_number=adt.slbrdef_room_number 
					LEFT JOIN (SELECT slrmasg.slrmasg_term_code, 
							   slrmasg.slrmasg_pidm, 
							   slrmasg.slrmasg_mrcd_code, 
							   slrmasg.slrmasg_begin_date, 
							   slrmasg.slrmasg_end_date, 
							   slrmasg.slrmasg_mscd_code, 
							   slrmasg.slrmasg_mscd_date 
						  FROM slrmasg ";

		return $sql;
	}

/**
 * get for all students, their room info living in the specified hall, ordered by name
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @param string $building_code two letter code for residence hall
 * @access public
 * @return string of query
 *
 */ 
	function getCheckins($term, $gterm, $building_code) 
	{
    $sql = $this->getSQLCheckinBase($term, $gterm);

		$sql .= " WHERE slrmasg.slrmasg_term_code IN (:term, :gterm) 
							   AND slrmasg.slrmasg_mscd_code='AC' 
						) meal 
							ON slrrasg.slrrasg_pidm=meal.slrmasg_pidm 
						INNER JOIN spriden 
							ON slrrasg.slrrasg_pidm=spriden.spriden_pidm 
						INNER JOIN spbpers 
							ON slrrasg.slrrasg_pidm=spbpers.spbpers_pidm 
			     WHERE slrrasg.slrrasg_term_code IN (:term,:gterm) 
					   AND slrrasg.slrrasg_bldg_code IN (:building_code) 
					   AND (meal.slrmasg_mscd_code='AC' OR meal.slrmasg_mscd_code is null) 
					   AND spriden.spriden_change_ind is null 
					   AND slrrasg.slrrasg_ascd_code='AC' ";

			$sql .= $this->getSQLCheckinOrderBy();

		$rs = PSU::db('banner')->Execute($sql, compact('term', 'gterm', 'building_code'));
		return $rs->GetRows();
	}

/**
 * get all students, their room info living in residence halls and apartments, ordered by building, name
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @access public
 * @return string of query
 *
 */ 
	function getCheckinsAllCampus($term, $gterm) 
	{
    $sql = $this->getSQLCheckinBase($term, $gterm);

		$sql .= " WHERE slrmasg.slrmasg_term_code IN (:term,:gterm) 
									AND slrmasg.slrmasg_mscd_code='AC' 
								) meal 
								  ON slrrasg.slrrasg_pidm=meal.slrmasg_pidm 
						 INNER JOIN spriden 
							ON slrrasg.slrrasg_pidm=spriden.spriden_pidm 
						 INNER JOIN spbpers 
							ON slrrasg.slrrasg_pidm=spbpers.spbpers_pidm 
				WHERE slrrasg.slrrasg_term_code IN (:term,:gterm) 
					  AND slrrasg.slrrasg_bldg_code IN ('BE','PE','ML','HA','BL','SM','GR') 
					  AND (meal.slrmasg_mscd_code='AC' OR meal.slrmasg_mscd_code is null) 
					  AND spriden.spriden_change_ind is null 
						AND slrrasg.slrrasg_ascd_code='AC'  ";

		$sql .= $this->getSQLCheckinOrderBy();

		$rs = PSU::db('banner')->Execute($sql, compact('term', 'gterm'));
		return $rs->GetRows();
	}

/**
 * get SQL for Checkin Order Bys
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @access public
 * @return string of query
 *
 */ 
	function getSQLCheckinOrderBy() 
	{
		return " ORDER BY slrrasg.slrrasg_bldg_code, 
			           spriden.spriden_last_name, 
					   spriden.spriden_first_name, 
					   spriden.spriden_mi";

	}
/**
 * get SQL for all students and their email addresses in the specified hall
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @param string $building_code two letter code for residence hall
 * @access public
 * @return string of query
 *
 */ 
	function getEmails($term, $gterm, $building_code) 
	{
	  $sql = "SELECT sp.spriden_id, 
									 sp.spriden_last_name, 
									 sp.spriden_first_name, 
									 sp.spriden_mi, 
									 sl.slrrasg_bldg_code, 
									 sl.slrrasg_room_number, 
									 sl.slrrasg_term_code, 
									 sl.slrrasg_ascd_code, 
									 sp.spriden_change_ind, 
									 go.goremal_email_address, 
									 go.goremal_emal_code 
								FROM slrrasg sl 
								INNER JOIN spriden sp 
									ON sl.slrrasg_pidm=sp.spriden_pidm 
								INNER JOIN goremal go 
									ON sl.slrrasg_pidm=go.goremal_pidm 
							 WHERE sl.slrrasg_bldg_code=:building_code 
								 AND sl.slrrasg_ascd_code='AC' 
								 AND sl.slrrasg_term_code IN(:term,:gterm) 
								 AND sp.spriden_change_ind is null 
								 AND go.goremal_emal_code='CA' 
							ORDER BY sp.spriden_last_name";

		$rs = PSU::db('banner')->Execute($sql, compact('term', 'gterm', 'building_code'));
		return $rs->GetRows();
	}
	
/**
 * return the root sql for the hdepo calls
 * 
 * @param string array $extracolumns for any additional columns to select
 * @access public
 * @return string of query
 *
 */ 
	function getSQLHdepoBase($extracolumns=null) 
	{
		$basesql = "SELECT DISTINCT tbrdepo.tbrdepo_term_code,
				spriden.spriden_id,
				spriden.spriden_last_name,
				spriden.spriden_first_name,
				spriden.spriden_mi,
				tbrdepo.tbrdepo_entry_date,
				TO_CHAR(tbrdepo.tbrdepo_entry_date, 'MM/DD/YYYY') as tbrdepo_entry_date,
				TO_CHAR(tbrdepo.tbrdepo_release_date, 'MM/DD/YYYY') as tbrdepo_release_date,
				tbrdepo.tbrdepo_amount, 
				slrrasg.slrrasg_bldg_code, 
				slrrasg.slrrasg_room_number, 
				slrrasg.slrrasg_ascd_code,	
				TO_CHAR(spbpers.spbpers_birth_date, 'MM/DD/YYYY') as spbpers_birth_date,
				NVL((SELECT 'SM' FROM slrpreq WHERE slrpreq_pidm = spriden_pidm AND slrpreq_rdef_code = 'SM'), ' ') as smoker_status ";

		// add any add'l columns the caller might want...
		if ($extracolumns != null && is_array($extracolumns))
		    foreach ($extracolumns as $column)
			$basesql .= ", " . $column;
	
		$basesql .= " FROM (tbrdepo 
					LEFT JOIN slrrasg 
						ON tbrdepo.tbrdepo_pidm=slrrasg.slrrasg_pidm				 
					   AND tbrdepo.tbrdepo_term_code=slrrasg.slrrasg_term_code)				
					   INNER JOIN spriden 
						ON tbrdepo.tbrdepo_pidm=spriden.spriden_pidm
				       INNER JOIN spbpers 
						ON tbrdepo.tbrdepo_pidm=spbpers_pidm			 
				WHERE ((tbrdepo.tbrdepo_term_code IN (:term, :gterm)
					 AND ((slrrasg.slrrasg_ascd_code='AC') 
					  OR (slrrasg.slrrasg_ascd_code is null)) 
					 AND (tbrdepo.tbrdepo_detail_code_deposit='IZRM' OR tbrdepo.tbrdepo_detail_code_deposit='IZRO') 
					 AND (spriden.spriden_change_ind is null))) ";

		return $basesql;
	}

/**
 * return the order by Gender sql for the hdepo calls
 * 
 * @access public
 * @return string of query
 *
 */ 
	function getSQLHdepoOrderByGender() 
	{
		return " ORDER BY spbpers.spbpers_sex, 
				tbrdepo.tbrdepo_entry_date, 
				spriden.spriden_last_name, 
				spriden.spriden_first_name";
	}

/**
 * return the order by date sql for the hdepo calls
 * 
 * @access public
 * @return string of query
 *
 */ 
	function getSQLHdepoOrderByDate() 
	{
		return " ORDER BY tbrdepo.tbrdepo_entry_date ASC, 
			     spriden.spriden_last_name ASC, 
			     spriden.spriden_first_name ASC";
	}

/**
 * return the order by name sql for the hdepo calls
 * 
 * @access public
 * @return string of query
 *
 */ 
	function getSQLHdepoOrderByName() 
	{
		return " ORDER BY spriden.spriden_last_name, 
			     spriden.spriden_first_name";
	}

/**
 * get SQL for all new ids that have been issued since the date entered
 * 
 * @param string $date date in 
 * @access public
 * @return string of query
 *
 */ 
	function getIdLinkedDB($fromdate) 
	{	
		$sql = "SELECT spbcard.spbcard_first_name,
					   spbcard.spbcard_middle_name,
					   spbcard.spbcard_last_name,
					   spbcard.spbcard_id,
					   spbcard.spbcard_issue_number,
					   spbcard.spbcard_date_issued,
					   TO_CHAR(spbcard.spbcard_date_issued, 'MM/DD/YYYY') as spbcard_date_issued,
					   spbcard.spbcard_update_date,
					   TO_CHAR(spbcard.spbcard_update_date, 'MM/DD/YYYY') as spbcard_update_date
					   
			      FROM psu.spbcard
				 WHERE (spbcard.spbcard_issue_number IS NOT NULL
				   AND spbcard.spbcard_date_issued >=:fromdate)
					OR (spbcard.spbcard_issue_number IS NOT NULL
				   AND spbcard.spbcard_update_date >=:fromdate)
				 
			  ORDER BY spbcard.spbcard_last_name, 
					   spbcard.spbcard_first_name";

		$rs = PSU::db('banner')->Execute($sql, compact('fromdate'));
		return $rs->GetRows();
	} 

/**
 * get SQL base for all lockout calls
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @param string $building_code two letter code for residence hall
 * @access public
 * @return string of query
 *
 */ 
	function getSQLLockoutBase($term, $gterm, $building_code) 
	{
		return "SELECT sp.spriden_id, 
									 sp.spriden_last_name, 
									 sp.spriden_first_name, 
									 sp.spriden_mi, 
									 sl.slrrasg_bldg_code, 
							 CASE (length(sl.slrrasg_room_number)) 
								WHEN 2 
									THEN CONCAT('0', sl.slrrasg_room_number) 
								WHEN 1 
									THEN CONCAT('00' , sl.slrrasg_room_number) 
								ELSE sl.slrrasg_room_number 
							 END as slrrasg_room_number, 
									 sl.slrrasg_term_code, 
									 sl.slrrasg_ascd_code, 
									 sp.spriden_change_ind 
							FROM slrrasg sl 
							INNER JOIN spriden sp 
								ON sl.slrrasg_pidm=sp.spriden_pidm 
							WHERE sl.slrrasg_bldg_code=:building_code 
							 AND sl.slrrasg_ascd_code='AC' 
							 AND sl.slrrasg_term_code IN (:term,:gterm)
							 AND sp.spriden_change_ind is null ";
	}

/**
 * get SQL for all students living in the specified hall and sets up some checkboxes for keeping track of 
 * lockouts, ordered by name
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @param string $building_code two letter code for residence hall
 * @access public
 * @return string of query
 *
 */ 
	function getLockoutsByName($term, $gterm, $building_code) 
	{
	  $sql = $this->getSQLLockoutBase($term, $gterm, $building_code);

		$sql .= " ORDER BY sp.spriden_last_name";

		$rs = PSU::db('banner')->Execute($sql, compact('term', 'gterm', 'building_code'));
		return $rs->GetRows();
	}

/**
 * get SQL for all students living in the specified hall and sets up some checkboxes for keeping track of lockouts, 
 * ordered by room
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @param string $building_code two letter code for residence hall
 * @access public
 * @return string of query
 *
 */ 
	function getLockoutsByRoom($term, $gterm, $building_code) 
	{
	  $sql = $this->getSQLLockoutBase($term, $gterm, $building_code);

		$sql .= " ORDER BY slrrasg_room_number";

		$rs = PSU::db('banner')->Execute($sql, compact('term', 'gterm', 'building_code'));
		return $rs->GetRows();
	}

/**
 * get SQL for max term code effective for all rooms
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @access public
 * @return string of query
 *
 */ 
	function getSQLMaxTermCodeEffSlbrdef($term, $gterm) 
	{
		$all_rooms_current_and_prev = $this->getSQLAllRoomsCurAndPrev($term, $gterm);
		return "SELECT ctpt.slbrdef_bldg_code, 
										ctpt.slbrdef_room_number, 
										max(ctpt.slbrdef_term_code_eff) as Maxofslbrdef_term_code_eff 
						 FROM ($all_rooms_current_and_prev) ctpt 
						 GROUP BY ctpt.slbrdef_bldg_code,
											ctpt.slbrdef_room_number
						 ORDER BY ctpt.slbrdef_bldg_code,
											ctpt.slbrdef_room_number";
	}
   
/**
 * get SQL for max term code effective of student demographics view, is used by other queries
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @access public
 * @return string of query
 *
 */ 
	function getSQLMaxTermDemographics() 
	{
		return "SELECT dem.pidm, 
									max(dem.term_code_eff) as MaxOfTerm_code_eff 
						FROM datamart.student_a_active_demog dem 
						GROUP BY dem.pidm 
						ORDER BY dem.pidm";
   }

/**
 * get SQL for all new students, their room info who have a missing lease or R&D ordered by name
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @access public
 * @return string of query
 *
 */ 
	function getMissingLeasesOrRD($term, $gterm) 
	{	
		$sql = "SELECT baninst1.as_residential_life.id,
					   baninst1.as_residential_life.last_name || ', ' || baninst1.as_residential_life.first_name || ' ' || baninst1.as_residential_life.middle_initial name,
					   baninst1.as_residential_life.phone_area_code2 || '-' || SUBSTR(baninst1.as_residential_life.phone_number2, 0,3) || '-' || SUBSTR(baninst1.as_residential_life.phone_number2, 4,4) phone,
					   baninst1.as_residential_life.artp_desc,
					   slrrasg.slrrasg_bldg_code,
					   slrrasg.slrrasg_room_number,
					   slrrasg.slrrasg_rrcd_code,
					   slrrasg.slrrasg_term_code,
					   baninst1.at_deposits.deposit_effective_date				
				FROM   slrrasg
					LEFT JOIN baninst1.as_residential_life 
						ON slrrasg.slrrasg_term_code=baninst1.as_residential_life.term_code_key 
					   AND slrrasg.slrrasg_pidm=baninst1.as_residential_life.pidm_key
					LEFT JOIN slbrdef
						ON slrrasg.slrrasg_bldg_code=slbrdef.slbrdef_bldg_code
					   AND slrrasg.slrrasg_room_number=slbrdef.slbrdef_room_number
					LEFT JOIN baninst1.at_deposits 
						ON baninst1.as_residential_life.pidm_key=baninst1.at_deposits.pidm_key
					   AND baninst1.as_residential_life.term_code_key=baninst1.at_deposits.deposit_term_code_key
				WHERE ((slrrasg.slrrasg_term_code IN (:term,:gterm)) 
					  AND (slrrasg.slrrasg_ascd_code='AC') 
					  AND (baninst1.as_residential_life.artp_code IN ('RD','NONE','LEAS')))		
			 ORDER BY baninst1.as_residential_life.last_name, 
					  baninst1.as_residential_life.first_name, 
					  baninst1.as_residential_life.middle_initial";

		$rs = PSU::db('banner')->Execute($sql, compact('term', 'gterm'));
		return $rs->GetRows();
	} 

/**
 * get all students, their room info, and meal info living in the specified hall, ordered by name
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @param string $building_code two letter code for residence hall
 * @access public
 * @return string of query
 *
 */ 
	function getNameOrder($term, $gterm, $building_code) 
	{
		$sql = $this->getSQLRoomNameBase($term, $gterm, $building_code);

    $sql .= " ORDER BY spriden.spriden_last_name, 
			         spriden.spriden_first_name,
					 spriden.spriden_mi";

		$rs = PSU::db('banner')->Execute($sql, compact('term', 'gterm', 'building_code'));
		return $rs->GetRows();
	}

/**
 * get all students, their room info, and meal info living in residence halls and apartments, ordered by name
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @access public
 * @return string of query
 *
 */ 
	function getNameOrderAllCampus($term, $gterm) 
	{
		$all_rooms_current_term_with_data = $this->getSQLAllRoomsCurTermWithData($term, $gterm);
	
		$sql = "SELECT slrrasg.slrrasg_term_code, 
						slrrasg.slrrasg_bldg_code, 
						CASE (length(slrrasg.slrrasg_room_number))
						 WHEN 2 THEN CONCAT('0', slrrasg.slrrasg_room_number)
						 WHEN 1 THEN CONCAT('00' , slrrasg.slrrasg_room_number) 
						 ELSE slrrasg.slrrasg_room_number 
						END as slrrasg_room_number,  
						adt.slbrdef_maximum_capacity, 
						adt.slbrdef_capacity, 
						spriden.spriden_last_name, 
						spriden.spriden_first_name, 
						spriden.spriden_mi, 
						spriden.spriden_id, 
						meal.slrmasg_mscd_code, 
						meal.slrmasg_mrcd_code, 
						spbpers.spbpers_sex, 
						spbpers.spbpers_birth_date,     
						adt.slbrdef_rrcd_code, 
						slrrasg.slrrasg_rrcd_code,  
						spriden.spriden_change_ind 
					FROM slrrasg 
						LEFT JOIN ($all_rooms_current_term_with_data) adt 
						  ON slrrasg.slrrasg_bldg_code=adt.slbrdef_bldg_code 
						 AND slrrasg.slrrasg_room_number=adt.slbrdef_room_number 
						LEFT JOIN (SELECT slrmasg.slrmasg_term_code, 
										  slrmasg.slrmasg_pidm, 
										  slrmasg.slrmasg_mrcd_code, 
										  slrmasg.slrmasg_begin_date, 
										  slrmasg.slrmasg_end_date,
										  slrmasg.slrmasg_mscd_code, 
										  slrmasg.slrmasg_mscd_date 
									 FROM slrmasg 
									WHERE slrmasg.slrmasg_term_code IN (:gterm,:term)
									  AND slrmasg.slrmasg_mscd_code='AC'
									  AND slrmasg.slrmasg_mrcd_code<>'ADD'
								  ) meal ON slrrasg.slrrasg_pidm=meal.slrmasg_pidm 
							INNER JOIN spriden ON slrrasg.slrrasg_pidm=spriden.spriden_pidm
							INNER JOIN spbpers ON slrrasg.slrrasg_pidm=spbpers.spbpers_pidm 
					WHERE slrrasg.slrrasg_term_code IN (:gterm,:term)
							AND (meal.slrmasg_mscd_code='AC' OR meal.slrmasg_mscd_code is null) 
							AND spriden.spriden_change_ind is null 
							AND slrrasg.slrrasg_ascd_code='AC' 
					ORDER BY spriden.spriden_last_name, 
					spriden.spriden_first_name, 
					spriden.spriden_mi";

		$rs = PSU::db('banner')->Execute($sql, compact('term', 'gterm'));
		return $rs->GetRows();
	}

/**
 * get SQL for future termcodes for both Graduate and Undergraduate
 * 
 * @param string &$sql - graduate
 * @param string &$usql - undergraduate
 * @access public
 * @return string of query
 *
 */ 
	function getSQLNextTerms(&$sql, &$usql) 
	{
		$today = date('Y-m-d');
		
		$sql = "SELECT stvterm_code, 
					   stvterm_housing_start_date, 
					   stvterm_housing_end_date 
				  FROM stvterm 
				 WHERE '$today' < trunc(stvterm_housing_end_date) 
					   AND '$today' < trunc(stvterm_housing_start_date) 
					   AND substr(stvterm_code,5,2) IN ('91','92','93','94') 
			  ORDER BY stvterm_housing_start_date ASC";

		$usql = "SELECT stvterm_code, 
					    stvterm_housing_start_date, 
						stvterm_housing_end_date 
				   FROM stvterm WHERE '$today' < trunc(stvterm_housing_end_date)
					    AND '$today' < trunc(stvterm_housing_start_date) 
						AND substr(stvterm_code,5,2) IN ('10','20','30','40') 
			   ORDER BY stvterm_housing_start_date ASC";

	}
	

/**
 * gets all rooms in the specified hall, and shows occupied and empty spaces
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @param string $building_code two letter code for residence hall
 * @access public
 * @return string of query
 *
 */ 
	function getOccupants($term, $gterm, $building_code, $student_rm_number)
	{
		$sql = "SELECT spriden.spriden_id, 
										spriden.spriden_last_name, 
										spriden.spriden_first_name, 
										spriden.spriden_mi, 
										slrrasg.slrrasg_term_code, 
										slrrasg.slrrasg_bldg_code,
							CASE (length(slrrasg.slrrasg_room_number)) 
								WHEN 2 
									THEN CONCAT('0', slrrasg.slrrasg_room_number) 
								WHEN 1 
									THEN CONCAT('00' , slrrasg.slrrasg_room_number) 
								ELSE slrrasg.slrrasg_room_number 
							END as slrrasg_room_number,
										meal.slrmasg_mrcd_code,
							TO_CHAR(spbpers.spbpers_birth_date, 'MM/DD/YYYY') as spbpers_birth_date,
										spbpers.spbpers_pidm
							FROM slrrasg
							INNER JOIN spriden
							ON slrrasg.slrrasg_pidm=spriden.spriden_pidm
							INNER JOIN spbpers
							ON slrrasg.slrrasg_pidm=spbpers.spbpers_pidm
							LEFT JOIN (SELECT slrmasg.slrmasg_term_code, 
										slrmasg.slrmasg_pidm, 
										slrmasg.slrmasg_mrcd_code, 
										slrmasg.slrmasg_begin_date, 
										slrmasg.slrmasg_end_date, 
										slrmasg.slrmasg_mscd_code, 
										slrmasg.slrmasg_mscd_date 
							FROM slrmasg 
								WHERE slrmasg.slrmasg_term_code IN (:term, :gterm)
									AND slrmasg.slrmasg_mscd_code='AC' 
									AND slrmasg.slrmasg_mrcd_code<>'ADD') meal ON slrrasg.slrrasg_pidm=meal.slrmasg_pidm
							WHERE slrrasg.slrrasg_term_code IN (:term, :gterm)
								AND slrrasg.slrrasg_bldg_code=:building_code
								AND spriden.spriden_change_ind is null
								AND slrrasg.slrrasg_ascd_code='AC'
								AND slrrasg.slrrasg_room_number='".$student_rm_number."'
								AND (meal.slrmasg_mscd_code='AC' OR meal.slrmasg_mscd_code is null)";

		$rs = PSU::db('banner')->Execute($sql, compact('term', 'gterm', 'building_code'));
		return $rs->GetRows();
	}
		
/**
 * get SQL for past termcodes for both Graduate and Undergraduate
 * 
 * @param string &$sql - graduate
 * @param string &$usql - undergraduate
 * @access public
 * @return string of query
 *
 */ 
	function getSQLOldTerms(&$sql, &$usql) 
	{
		$today = date('Y-m-d');
		
		$sql = "SELECT stvterm_code, 
					   stvterm_housing_start_date, 
					   stvterm_housing_end_date 
				  FROM stvterm WHERE '$today' > trunc(stvterm_housing_end_date) 
					   AND '2003-08-31' < trunc(stvterm_housing_end_date) 
					   AND substr(stvterm_code,5,2) IN ('91','92','93','94') 
			  ORDER BY stvterm_housing_start_date DESC";


		$usql = "SELECT stvterm_code, 
						stvterm_housing_start_date, 
						stvterm_housing_end_date 
				   FROM stvterm 
				  WHERE '$today' > trunc(stvterm_housing_end_date) 
						AND '2003-08-31' < trunc(stvterm_housing_end_date) 
						AND trunc(stvterm_housing_start_date) != trunc(stvterm_housing_end_date) 
						AND substr(stvterm_code,5,2) IN ('10','20','30','40') 
			   ORDER BY stvterm_housing_start_date DESC";

	}

/**
 * get all rooms in the specified hall, and shows occupied and empty spaces
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @param string $building_code two letter code for residence hall
 * @access public
 * @return string of query
 *
 */ 
	function getOpenBeds($term, $gterm, $building_code)
	{
		$sql = "SELECT sl.slbrdef_bldg_code, 
					   CASE (length(sl.slbrdef_room_number)) 
					    WHEN 2 
					    THEN CONCAT('0', sl.slbrdef_room_number) 
					    WHEN 1 
					    THEN CONCAT('00' , sl.slbrdef_room_number) 
					    ELSE sl.slbrdef_room_number 
					   END as slbrdef_room_number, 
					   sl.slbrdef_room_number as rm_number,
					   sl.slbrdef_capacity, 
					   sl.slbrdef_maximum_capacity, 
					   sl.slbrdef_sex, 
					   sl.slbrdef_rmst_code, 
					   sl.slbrdef_rrcd_code, 
					   sl.slbrdef_desc, 
					   MaxTermCodeEff 
				  FROM (SELECT sb.slbrdef_bldg_code, 
							   sb.slbrdef_room_number, 
							   max(sb.slbrdef_term_code_eff) as MaxTermCodeEff 
						  FROM slbrdef sb 
					  GROUP BY sb.slbrdef_bldg_code, 
							   sb.slbrdef_room_number 
						HAVING sb.slbrdef_bldg_code != 'PEND' 
					  ORDER BY sb.slbrdef_bldg_code, 
							   sb.slbrdef_room_number, 
							   MaxTermCodeEff) met 
					INNER JOIN slbrdef sl ON met.slbrdef_bldg_code=sl.slbrdef_bldg_code 
						   AND met.slbrdef_room_number=sl.slbrdef_room_number 
						   AND met.MaxTermCodeEff=sl.slbrdef_term_code_eff 
				WHERE sl.slbrdef_bldg_code=:building_code 
					  AND sl.slbrdef_rmst_code='AC' 
					  AND sl.slbrdef_room_type='D' 
			 ORDER BY sl.slbrdef_bldg_code, 
					  slbrdef_room_number";

		$rs = PSU::db('banner')->Execute($sql, compact('term', 'gterm', 'building_code'));
		return $rs->GetRows();
	}

/**
 * get all students, their room and meal info, how much their deposit is, city, state, gender, and 
 * paperwork ordered by deposit date
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @access public
 * @return string of query
 *
 */ 
	function getOrientationRoster($term, $gterm) 
	{	
		$sql = "SELECT DISTINCT spriden.spriden_last_name,
								spriden.spriden_first_name,
								spriden.spriden_mi,
								spriden.spriden_id,
								tbrdepo.tbrdepo_entry_date,
								TO_CHAR(tbrdepo.tbrdepo_entry_date, 'MM/DD/YYYY') as tbrdepo_entry_date,
								tbrdepo.tbrdepo_desc,
								tbrdepo.tbrdepo_amount,
								slbrmap.slbrmap_artp_code,
								spbpers.spbpers_sex,
								slbrmap.slbrmap_mrcd_code,
								baninst1.as_admissions_applicant.city1,
								baninst1.as_admissions_applicant.state1
				FROM (tbrdepo 
					LEFT JOIN slrrasg 
						ON tbrdepo.tbrdepo_pidm=slrrasg.slrrasg_pidm				 
					   AND tbrdepo.tbrdepo_term_code=slrrasg.slrrasg_term_code
					 )				
					   INNER JOIN spriden 
						ON tbrdepo.tbrdepo_pidm=spriden.spriden_pidm
				       INNER JOIN spbpers 
						ON tbrdepo.tbrdepo_pidm=spbpers_pidm
					LEFT JOIN slbrmap
						ON tbrdepo.tbrdepo_pidm=slbrmap.slbrmap_pidm
						AND tbrdepo.tbrdepo_term_code=slbrmap.slbrmap_from_term
					LEFT JOIN baninst1.as_admissions_applicant
						ON tbrdepo.tbrdepo_pidm=baninst1.as_admissions_applicant.pidm_key
			   WHERE ((tbrdepo.tbrdepo_term_code IN (:term,:gterm) 
					 AND ((slrrasg.slrrasg_ascd_code='AC') 
					  OR (slrrasg.slrrasg_ascd_code is null)) 
					 AND (tbrdepo.tbrdepo_detail_code_deposit='IZRM' OR tbrdepo.tbrdepo_detail_code_deposit='IZRO') 
					 AND (spriden.spriden_change_ind is null)))
			ORDER BY spriden.spriden_last_name ASC, 
					 spriden.spriden_first_name ASC";

		$rs = PSU::db('banner')->Execute($sql, compact('term', 'gterm'));
		return $rs->GetRows();
	}    

/**
 * get all students in housing, their room info if they are part time or have low credits, ordered by building
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @access public
 * @return string of query
 *
 */ 
	function getPtOrLowCredit($term, $gterm) 
	{	
		$sql = "SELECT baninst1.as_student_enrollment_summary.id,
					   baninst1.as_student_enrollment_summary.first_name,
			           baninst1.as_student_enrollment_summary.middle_initial,
					   baninst1.as_student_enrollment_summary.last_name,
					   slrrasg.slrrasg_bldg_code,
					   slrrasg.slrrasg_room_number,
					   baninst1.as_student_enrollment_summary.full_part_time_ind,
					   baninst1.as_student_enrollment_summary.total_credit_hours		
			      FROM baninst1.as_student_enrollment_summary 
					INNER JOIN slrrasg 
						ON baninst1.as_student_enrollment_summary.pidm_key=slrrasg.slrrasg_pidm 
					   AND baninst1.as_student_enrollment_summary.term_code_key=slrrasg.slrrasg_term_code
				 WHERE slrrasg.slrrasg_ascd_code is null 
						OR slrrasg.slrrasg_ascd_code='AC' 
					   AND baninst1.as_student_enrollment_summary.full_part_time_ind='P' 
					   AND baninst1.as_student_enrollment_summary.term_code_key=:term 
					   AND baninst1.as_student_enrollment_summary.ests_code='EL' 
						OR slrrasg.slrrasg_ascd_code is null 
						OR slrrasg.slrrasg_ascd_code='AC' 
					   AND baninst1.as_student_enrollment_summary.total_credit_hours < 12 
					   AND baninst1.as_student_enrollment_summary.term_code_key=:term 
					   AND baninst1.as_student_enrollment_summary.ests_code='EL'
			  ORDER BY slrrasg.slrrasg_bldg_code, 
					   slrrasg.slrrasg_room_number";

		$rs = PSU::db('banner')->Execute($sql, compact('term', 'gterm'));
		return $rs->GetRows();
	} 

/**
 * get SQL for all students, their room info and the specified attribute, ordered by deposit date
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @param string $att_code two or three letter attribute code
 * @access public
 * @return string of query
 *
 */ 
	function getPromptedAttributesByhdepo($term, $gterm, $att_code) 
	{	
		$sql = "SELECT DISTINCT tbrdepo.tbrdepo_entry_date,
								TO_CHAR(tbrdepo.tbrdepo_entry_date, 'MM/DD/YYYY') as tbrdepo_entry_date,
								TO_CHAR(tbrdepo.tbrdepo_release_date, 'MM/DD/YYYY') as tbrdepo_release_date,
								tbrdepo.tbrdepo_term_code,
								baninst1.as_admissions_applicant.id,
								baninst1.as_admissions_applicant.last_name,
								baninst1.as_admissions_applicant.first_name,
								baninst1.as_admissions_applicant.middle_initial,
								baninst1.as_admissions_applicant.phone_area_code1 || '-' || SUBSTR(baninst1.as_admissions_applicant.phone_number1, 0,3) || '-' || SUBSTR(baninst1.as_admissions_applicant.phone_number1, 4,4) phone,
								stvrdef.stvrdef_desc		
				  FROM tbrdepo 
					LEFT JOIN baninst1.as_admissions_applicant 
						ON tbrdepo.tbrdepo_pidm=baninst1.as_admissions_applicant.pidm_key
						INNER JOIN slbrmap 
							ON tbrdepo.tbrdepo_term_code=slbrmap.slbrmap_from_term
						   AND tbrdepo.tbrdepo_pidm=slbrmap.slbrmap_pidm
						INNER JOIN slrpreq 
							ON slbrmap.slbrmap_from_term=slrpreq.slrpreq_term_code
						   AND slbrmap.slbrmap_pidm=slrpreq.slrpreq_pidm
						INNER JOIN stvrdef 
							ON slrpreq.slrpreq_rdef_code=stvrdef.stvrdef_code
				WHERE ((tbrdepo.tbrdepo_term_code IN (:term,:gterm) 
					  AND ((tbrdepo.tbrdepo_detail_code_deposit='IZRM' OR tbrdepo.tbrdepo_detail_code_deposit='IZRO') 
					  AND (stvrdef.stvrdef_code=:att_code))))
		     ORDER BY tbrdepo.tbrdepo_entry_date, 
					  baninst1.as_admissions_applicant.last_name, 
					  baninst1.as_admissions_applicant.first_name";

    $rs = PSU::db('banner')->Execute($sql, compact('term', 'gterm', 'att_code'));
    return $rs->GetRows();
	} 

/**
 * get SQL for the base select for the Room/Name reports
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @param string $building_code two letter code for residence hall
 * @access public
 * @return string of query
 *
 */ 
	function getSQLRoomNameBase($term, $gterm, $building_code) 
	{
		$all_rooms_current_term_with_data = $this->getSQLAllRoomsCurTermWithData($term, $gterm);

		$sql = "SELECT 
					   slrrasg.slrrasg_term_code, 
					   slrrasg.slrrasg_bldg_code,
					   slrrasg.slrrasg_rrcd_code, 

					   CASE (length(slrrasg.slrrasg_room_number)) 
					    WHEN 2 
					    THEN CONCAT('0', slrrasg.slrrasg_room_number) 
					    WHEN 1 
					    THEN CONCAT('00' , slrrasg.slrrasg_room_number) 
					    ELSE slrrasg.slrrasg_room_number 
					   END as slrrasg_room_number, 

					   adt.slbrdef_maximum_capacity, 
					   adt.slbrdef_capacity, 
					   adt.slbrdef_rrcd_code, 

					   spriden.spriden_last_name, 
					   spriden.spriden_first_name, 
					   spriden.spriden_mi, 
					   spriden.spriden_id, 
					   spriden.spriden_change_ind,

					   meal.slrmasg_mscd_code, 
					   meal.slrmasg_mrcd_code, 

					   spbpers.spbpers_sex, 
					   spbpers.spbpers_birth_date 

				  FROM slrrasg 

			 LEFT JOIN ($all_rooms_current_term_with_data) adt ON slrrasg.slrrasg_bldg_code=adt.slbrdef_bldg_code 
			       AND slrrasg.slrrasg_room_number=adt.slbrdef_room_number 

			 LEFT JOIN (SELECT slrmasg.slrmasg_term_code, 
							   slrmasg.slrmasg_pidm, 
							   slrmasg.slrmasg_mrcd_code, 
							   slrmasg.slrmasg_begin_date, 
							   slrmasg.slrmasg_end_date, 
							   slrmasg.slrmasg_mscd_code, 
							   slrmasg.slrmasg_mscd_date 
						  FROM slrmasg 
						 WHERE slrmasg.slrmasg_term_code=:term 
							   AND slrmasg.slrmasg_mscd_code='AC' 
							   AND slrmasg.slrmasg_mrcd_code<>'ADD' 
							   OR slrmasg_term_code=:gterm
							   AND slrmasg.slrmasg_mscd_code='AC'
							   AND slrmasg.slrmasg_mrcd_code<>'ADD') meal ON slrrasg.slrrasg_pidm=meal.slrmasg_pidm 
					INNER JOIN spriden 
						ON slrrasg.slrrasg_pidm=spriden.spriden_pidm 
					INNER JOIN spbpers 
						ON slrrasg.slrrasg_pidm=spbpers.spbpers_pidm 

			     WHERE slrrasg.slrrasg_term_code IN (:term, :gterm)
						 AND slrrasg.slrrasg_bldg_code=:building_code
						 AND (meal.slrmasg_mscd_code='AC' OR meal.slrmasg_mscd_code is null) 
						 AND spriden.spriden_change_ind is null 
						 AND slrrasg.slrrasg_ascd_code='AC'  ";

		return $sql;
	}

/**
 * get all students, their room info, and meal info living in the specified hall, ordered by room
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @param string $building_code two letter code for residence hall
 * @access public
 * @return string of query
 *
 */ 
	function getRoomOrder($term, $gterm, $building_code) 
	{
		$sql = $this->getSQLRoomNameBase($term, $gterm, $building_code);

		$sql .= " ORDER BY slrrasg_room_number";

		$rs = PSU::db('banner')->Execute($sql, compact('term', 'gterm', 'building_code'));
		return $rs->GetRows();
	}

/**
 * get for given a student id, are they are smoker or not?
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @param string $student_id student id looking for
 * @access public
 * @return string of query
 *
 */ 
	function getSmokerAttr($term, $gterm, $student_id) 
	{
    $sql = "SELECT stvrdef.stvrdef_code
							FROM slrpreq
								INNER JOIN stvrdef
									 ON slrpreq.slrpreq_rdef_code=stvrdef.stvrdef_code
								WHERE slrpreq.slrpreq_term_code IN (:term, :gterm)
									AND slrpreq.slrpreq_pidm='".$student_id."'
									AND slrpreq.slrpreq_rdef_code='SM'";

		$rs = PSU::db('banner')->Execute($sql, compact('term', 'gterm'));
		return $rs->FetchRow();
	}

/**
 * get SQL for housing and meal data for a student by their id number
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @param int $student_id 
 * @access public
 * @return string of query
 *
 */ 
	function getStudentById($term, $gterm, $student_id) 
	{
		$allRoomsCurTermWithData = $this->getSQLAllRoomsCurTermWithData($term, $gterm);

		$max_demographics = $this->getSQLMaxTermDemographics();

		$meals = $this->getSQLAllResMealPlans($term, $gterm);

		$sql = $this->getSQLAllInHousing($term, $gterm);

		$full_sql = "SELECT data.id, 
						 data.last_name, 
						 data.first_name, 
						 data.middle_name, 
						 data.dob, data.age, 
						 data.bi_address1, 
						 data.bi_address2, 
						 data.bi_address3, 
						 data.bi_city, 
						 data.bi_state, 
						 data.bi_zip, 
						 data.pa_address1, 
						 data.pa_address2, 
						 data.pa_address3, 
						 data.pa_city, 
						 data.pa_state, 
						 data.pa_zip, 
						 data.lo_address1,
						 data.lo_address2, 
						 data.lo_address3, 
						 data.lo_city, 
						 data.lo_state, 
						 data.lo_zip, 
						 data.ma_address1, 
						 data.ma_address2, 
						 data.ma_address3, 
						 data.ma_city, 
						 data.ma_state, 
						 data.ma_zip, 
						 data.ca_address1, 
						 data.ca_address2, 
						 data.ca_address3, 
						 data.ca_city, 
						 data.ca_state, 
						 data.ca_zip, 
						 data.ca_email, 
						 data.bi_phone_area, 
						 data.bi_phone_number, 
						 data.pa_phone_area, 
						 data.pa_phone_number, 
						 data.ma_phone_area, 
						 data.ma_phone_number, 
						 data.ca_phone_area, 
						 data.ca_phone_number, 
						 data.full_part_time, 
						 data.ug_gr, 
						 data.status, 
						 data.credits_earned, 
						 data.class_code, 
						 data.degree, 
						 data.major, 
						 data.confident, 
						 data.pidm,
						 slrrasg.slrrasg_bldg_code, 
						 slrrasg.slrrasg_room_number, 
						 slrrasg.slrrasg_rrcd_code, 
						 slrrasg.slrrasg_ascd_code, 
						 slrrasg.slrrasg_term_code, 
						 slrmasg.slrmasg_mrcd_code, 
						 slrmasg.slrmasg_mscd_code, 
						 slrmasg.slrmasg_term_code, 
						 twd.slbrdef_capacity, 
						 twd.slbrdef_maximum_capacity, 
						 twd.slbrdef_phone_number 
					FROM datamart.student_a_active_demog data 
							LEFT JOIN ($meals) slrmasg 
							ON data.pidm = slrmasg.slrmasg_pidm 
						LEFT JOIN ($sql) slrrasg 
							ON data.pidm = slrrasg.slrrasg_pidm 
						LEFT JOIN ($allRoomsCurTermWithData) twd 
							ON slrrasg.slrrasg_room_number = twd.slbrdef_room_number 
							 AND slrrasg.slrrasg_bldg_code = twd.slbrdef_bldg_code 
				 WHERE ((data.id = :student_id) 
						 AND ((slrrasg.slrrasg_ascd_code='AC') 
						OR (slrrasg.slrrasg_ascd_code is null)) 
						 AND ((slrmasg.slrmasg_mscd_code='AC') 
						OR (slrmasg.slrmasg_mscd_code is null))) 
				ORDER BY data.last_name, 
						 data.first_name, 
						 data.middle_name, 
						 slrrasg.slrrasg_rrcd_code, 
						 slrmasg.slrmasg_mrcd_code";


		$row = PSU::db('banner')->GetRow($full_sql, compact('term', 'gterm', 'student_id'));
		return $row;
	}

/**
 * get SQL for housing and meal data for a student by their last name
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @param string $last_name the student's last name
 * @access public
 * @return string of query
 *
 */ 
	function getStudentByLastName($term, $gterm, $last_name) 
	{
		$allRoomsCurTermWithData = $this->getSQLAllRoomsCurTermWithData($term, $gterm);

		$max_demographics = $this->getSQLMaxTermDemographics();

		$meals = $this->getSQLAllResMealPlans($term, $gterm);

		$sql = $this->getSQLAllInHousing($term, $gterm);

		$full_sql = "SELECT data.id, 
							   data.last_name, 
							   data.first_name, 
							   data.middle_name 
						  FROM datamart.student_a_active_demog data 
								LEFT JOIN ($meals) slrmasg 
									ON data.pidm = slrmasg.slrmasg_pidm 
								LEFT JOIN ($sql) slrrasg 
									ON data.pidm = slrrasg.slrrasg_pidm 
								LEFT JOIN ($allRoomsCurTermWithData) twd 
									ON slrrasg.slrrasg_room_number = twd.slbrdef_room_number 
								   AND slrrasg.slrrasg_bldg_code = twd.slbrdef_bldg_code  
						 WHERE ((upper(data.LAST_NAME) LIKE upper('$last_name')) 
							   AND ((slrrasg.slrrasg_ascd_code='AC') 
								OR (slrrasg.slrrasg_ascd_code is null)) 
							   AND ((slrmasg.slrmasg_mscd_code='AC') 
								OR (slrmasg.slrmasg_mscd_code is null))) 
					  ORDER BY data.last_name, 
						       data.first_name, 
							   data.middle_name, 
							   slrrasg.slrrasg_rrcd_code, 
							   slrrasg.slrrasg_ascd_code, 
							   slrmasg.slrmasg_mrcd_code";

		$rs = PSU::db('banner')->Execute($full_sql, compact('term', 'gterm', 'last_name'));
		return $rs->GetRows();
	}

/**
 * get all students living in the specified hall for winterim or summer, ordered by arrival date
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @param string $building_code two letter code for residence hall
 * @access public
 * @return string of query
 *
 */ 
	function getWintSumByArrival($term, $gterm, $building_code) 
	{
	  $sql = $this->getSQLWintSumBase($term, $gterm);

		// this is the specific information for this query
		// and is appended onto the end of the base query
	  $sql .= " AND slrrasg.slrrasg_bldg_code=:building_code
					    AND spriden.spriden_change_ind is null 
					    AND slrrasg.slrrasg_ascd_code='AC' 
					ORDER BY slrrasg.slrrasg_begin_date, 
							 end_plus_1";

		$rs = PSU::db('banner')->Execute($sql, compact('term', 'gterm', 'building_code'));
		return $rs->GetRows();
	}

/**
 * get all students living in residence halls or apartments for winterim or summer, ordered by arrival
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @access public
 * @return string of query
 *
 */ 
	function getWintSumArrivalAllCampus($term, $gterm) 
	{
	  $sql = $this->getSQLWintSumBase($term, $gterm);

		// this is the specific information for this query
		// and is appended onto the end of the base query
	  $sql .= " AND spriden.spriden_change_ind is null 
							AND slrrasg.slrrasg_ascd_code='AC' 
					ORDER BY slrrasg.slrrasg_bldg_code, 
							 slrrasg.slrrasg_begin_date, 
							 end_plus_1";

		$rs = PSU::db('banner')->Execute($sql, compact('term', 'gterm'));
		return $rs->GetRows();
	}

/**
 * get SQL for the root sql for the hdepo calls
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @access public
 * @return string of query
 *
 */ 
	function getSQLWintSumBase($term, $gterm) 
	{
		$all_rooms_current_term_with_data = $this->getSQLAllRoomsCurTermWithData($term, $gterm);

		$sql = "SELECT slrrasg.slrrasg_term_code, 
				       spriden.spriden_id, 
					   spriden.spriden_last_name, 
					   spriden.spriden_first_name, 
					   spriden.spriden_mi, 
					   spbpers.spbpers_sex, 
					   slrrasg.slrrasg_bldg_code,
					   CASE (length(slrrasg.slrrasg_room_number)) 
					    WHEN 2 
					    THEN CONCAT('0', slrrasg.slrrasg_room_number) 
					    WHEN 1 
					    THEN CONCAT('00' , slrrasg.slrrasg_room_number) 
					    ELSE slrrasg.slrrasg_room_number 
					   END as slrrasg_room_number, 
					   ct.slbrdef_maximum_capacity, 
					   slrrasg.slrrasg_rrcd_code, 
					   slrrasg.slrrasg_begin_date, 
					   slrrasg.slrrasg_end_date+1 as end_plus_1, 
					   ct.slbrdef_capacity, 
					   spriden.spriden_change_ind 
				  FROM slrrasg 
					LEFT JOIN ($all_rooms_current_term_with_data) ct 
						ON slrrasg.slrrasg_room_number=ct.slbrdef_room_number 
			           AND slrrasg.slrrasg_bldg_code=ct.slbrdef_bldg_code 
						INNER JOIN spriden 
							ON slrrasg.slrrasg_pidm=spriden.spriden_pidm 
						INNER JOIN spbpers 
							ON slrrasg.slrrasg_pidm=spbpers_pidm 
				 WHERE slrrasg.slrrasg_term_code IN (:term, :gterm) ";

    return $sql;
	}


/**
 * get all students living in the specified hall for winterim or summer, ordered by name
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @param string $building_code two letter code for residence hall
 * @access public
 * @return string of query
 *
 */ 
	function getWintSumByName($term, $gterm, $building_code) 
	{
	  $sql = $this->getSQLWintSumBase($term, $gterm);

		// this is the specific information for this query
		// and is appended onto the end of the base query
	  $sql .= " AND slrrasg.slrrasg_bldg_code=:building_code
					    AND spriden.spriden_change_ind is null 
					    AND slrrasg.slrrasg_ascd_code='AC' 
					ORDER BY spriden.spriden_last_name, 
							 spriden.spriden_first_name, 
							 spriden.spriden_mi";

		$rs = PSU::db('banner')->Execute($sql, compact('term', 'gterm', 'building_code'));
		return $rs->GetRows();
	}


/**
 * get all students living in residence halls or apartments for winterim or summer, ordered by name
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @access public
 * @return string of query
 *
 */ 
	function getWintSumNameAllCampus($term, $gterm) 
	{
	  $sql = $this->getSQLWintSumBase($term, $gterm);

		// this is the specific information for this query
		// and is appended onto the end of the base query
	  $sql .= " AND spriden.spriden_change_ind is null 
						  AND slrrasg.slrrasg_ascd_code='AC' 
					ORDER BY spriden.spriden_last_name, 
							 spriden.spriden_first_name, 
							 spriden.spriden_mi";

		$rs = PSU::db('banner')->Execute($sql, compact('term', 'gterm'));
		return $rs->GetRows();
	}

/**
 * get all students living in the specified hall for winterim or summer, ordered by room
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @param string $building_code two letter code for residence hall
 * @access public
 * @return string of query
 *
 */ 
	function getWintSumByRoom($term, $gterm, $building_code) 
	{
	  $sql = $this->getSQLWintSumBase($term, $gterm);

		// this is the specific information for this query
		// and is appended onto the end of the base query
	  $sql .= " AND slrrasg.slrrasg_bldg_code=:building_code 
					    AND spriden.spriden_change_ind is null 
					    AND slrrasg.slrrasg_ascd_code='AC' 
					ORDER BY slrrasg_room_number";

		$rs = PSU::db('banner')->Execute($sql, compact('term', 'gterm', 'building_code'));
		return $rs->GetRows();
	}


/**
 * get all students living in residence halls or apartments for winterim or summer, ordered by room
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @access public
 * @return string of query
 *
 */ 
	function getWintSumRoomAllCampus($term, $gterm) 
	{
	  $sql = $this->getSQLWintSumBase($term, $gterm);

		// this is the specific information for this query
		// and is appended onto the end of the base query
	  $sql .= " AND spriden.spriden_change_ind is null 
						  AND slrrasg.slrrasg_ascd_code='AC' 
					ORDER BY slrrasg_bldg_code, 
							 slrrasg_room_number";

		$rs = PSU::db('banner')->Execute($sql, compact('term', 'gterm'));
		return $rs->GetRows();
	}

/**
 * get all students, that were admitted but have withdrawn, deferred, or revoked - ordered by decision date
 * 
 * @param string $term undergraduate term code
 * @param string $gterm graduate term code
 * @access public
 * @return string of query
 *
 */ 
	function getWithdrawnOrRevoked($term, $gterm) 
	{	
		$old_date_format = PSU::db('banner')->NLS_DATE_FORMAT;
		PSU::db('banner')->Execute("ALTER SESSION SET nls_date_format = 'DD-Mon-YY'");

		$sql = "SELECT DISTINCT baninst1.as_admissions_applicant.id,
								baninst1.as_admissions_applicant.term_code_key,
								baninst1.as_admissions_applicant.last_name,
								baninst1.as_admissions_applicant.first_name,
								baninst1.as_admissions_applicant.middle_initial,
								baninst1.as_admissions_applicant.apdc_code1,
								baninst1.as_admissions_applicant.apdc_decision_date1,
								TO_CHAR(baninst1.as_admissions_applicant.apdc_decision_date1, 'MM/DD/YYYY') as apdc_decision_date1,
								baninst1.as_admissions_applicant.apdc_code2,
								slrrasg.slrrasg_ascd_code,
								slrrasg.slrrasg_bldg_code,
								slrrasg.slrrasg_room_number,
								slrmasg.slrmasg_mscd_code,
								slrmasg.slrmasg_mrcd_code		
				FROM baninst1.as_admissions_applicant
					LEFT JOIN slrrasg 
						ON baninst1.as_admissions_applicant.term_code_key=slrrasg.slrrasg_term_code
					   AND baninst1.as_admissions_applicant.pidm_key=slrrasg.slrrasg_pidm
					LEFT JOIN slrmasg 
						ON baninst1.as_admissions_applicant.term_code_key=slrmasg.slrmasg_term_code
					   AND baninst1.as_admissions_applicant.pidm_key=slrmasg.slrmasg_pidm
		   	   WHERE ((baninst1.as_admissions_applicant.apdc_code1 IN ('FW', 'RE', 'WP', 'DN', 'WB', 'WA', 'WN')) 
					 AND (baninst1.as_admissions_applicant.term_code_key IN (:term,:gterm)) 
					 AND (baninst1.as_admissions_applicant.apdc_code2 IN ('PD', 'PW', 'TP')))		
			ORDER BY baninst1.as_admissions_applicant.apdc_decision_date1, 
					 baninst1.as_admissions_applicant.last_name, 
					 baninst1.as_admissions_applicant.first_name";

		$rs = PSU::db('banner')->Execute($sql, compact('term', 'gterm'));
		$rows = $rs->GetRows();

		PSU::db('banner')->Execute("ALTER SESSION SET nls_date_format = '$old_date_format'");

		return $rows;
	} 

/**
 * get the housing ranking number for the person being asked about...
 * 
 * @param int $pidm 
 * @access public
 * @return string of query
 *
 */ 
	function getRanking($pidm) 
	{
		$sql = "SELECT ranking 
							FROM `housing_ranking` 
							WHERE pidm=?";

		return PSU::db('reslife')->GetOne($sql, array($pidm));
	}
/**
 * get The saved on-line app settings for this student
 * 
 * @param int $pidm 
 * @access public
 * @return string of query
 *
 */ 
	function getHousingApp($pidm,$area,$year_term,$application_type) 
	{
		$sql = "SELECT rank.ranking as rank_ranking, app.*
							FROM `housing_ranking` rank
							LEFT OUTER JOIN `housing_app` app ON rank.pidm = app.pidm
						WHERE rank.pidm=?
								AND (app.housing_area = ? OR app.housing_area IS NULL)
								AND app.year_term=? AND app.application_type=?";


/*
mysql:								WHERE pidm=?";
oracle:								WHERE pidm=:pidm";

oracle if($results = PSU::db('reslife')->Execute/getRow/fetch($sql, compact('pidm')))
mysql  if($results = PSU::db('reslife')->Execute/getRow/fetch($sql, array($pidm)))
//
*/

		// this will not return data if the student doesn't have a ranking number
		// we allow them to save an app, even without a ranking, so do the query below
		// if this one fails.
		
		if ($data = PSU::db('reslife')->getRow($sql, array($pidm,$area,$year_term,$application_type)))
    {
			if (isset($data['id']))
			{
				if ($data['housing_area'] == "RN")
				{
					if (isset($data['rnroompicks']))
						$data['roompicks'] = explode(",", $data['rnroompicks']);
				}
				else
				{
					if (isset($data['trroompicks']))
						$data['roompicks'] = explode(",", $data['trroompicks']);
				}
			}
			$data['signatures'] = array($data['signed_leasedining'], $data['signed_foryear']);
			return $data;
		}
		else
		{
				$sql = "SELECT * FROM `housing_app` app WHERE app.pidm=? AND app.housing_area=? AND app.year_term=? AND app.application_type=?";

				if ($data = PSU::db('reslife')->GetRow($sql, array($pidm,$area,$year_term,$application_type)))
				{
					if ($data['housing_area'] == "RN")
					{
						if (isset($data['rnroompicks']))
							$data['roompicks'] = explode(",", $data['rnroompicks']);
					}
					else
					{
						if (isset($data['trroompicks']))
							$data['roompicks'] = explode(",", $data['trroompicks']);
					}
					$data['signatures'] = array($data['signed_leasedining'], $data['signed_foryear']);
					return $data;
				}
				else
				{
					return null;
				}
		}
	}
	
/**
 * save the updated Housing App information to the database
 * 
 * @param int $pidm 
 * @access public
 * @return string of query
 *
 */ 
	function saveHousingApp($pidm, $area, $now, $data, $admin) 
	{
		$data['pidm'] = $pidm;

		$data['signed_leasedining'] = '';
		$data['signed_foryear'] = '';

		if (isset($data['signatures']))
		{
			if (in_array("LEAS", $data['signatures']))
				$data['signed_leasedining'] = "LEAS";
			if (in_array("YEAR", $data['signatures']))
				$data['signed_foryear'] = "YEAR";
		}
			
		$ranking = $this->getRanking($pidm);

		$housingapp = $this->getHousingApp($pidm, $area, $data['year_term'], $data['application_type']);

		if ($housingapp['housing_area'] == "RN"  && $admin== true)
			$area = "RN";		// make sure area is correct if running as admin 

		$data['housing_area'] = $area;

		if ($area == "RN")
		{
			$roompick = array();
			if (isset($data['roompick1']))
				$roompick = array_merge($roompick, $data['roompick1']);
			if (isset($data['roompick2']))
				$roompick = array_merge($roompick, $data['roompick2']);
			if (isset($data['roompick3']))
				$roompick = array_merge($roompick, $data['roompick3']);
			if (isset($roompick))
			{
				$data['rnroompicks'] = implode(",",$roompick);
				$data['rn_roompick_lws'] = (in_array('LWS', $roompick)) ? 1 : 0;
				$data['rn_roompick_lwd'] = (in_array('LWD', $roompick)) ? 1 : 0;
				$data['rn_roompick_4ps'] = (in_array('4PS', $roompick)) ? 1 : 0;
				$data['rn_roompick_2pa'] = (in_array('UA2', $roompick)) ? 1 : 0;
				$data['rn_roompick_4pa'] = (in_array('4PA', $roompick)) ? 1 : 0;
				$data['rn_roompick_ntrad'] = (in_array('NTRAD', $roompick)) ? 1 : 0;
			}
		}
		else
		{
			$data['intention'] = $housingapp['intention'];
			if (isset($data['roompick']))
				$data['trroompicks'] = implode(",",$data['roompick']);
		}
		
		$data['modified'] = $now;

		if (isset($housingapp['created']))
			$data['created'] = $housingapp['created'];
		else
			$data['created'] = $now;

		// Since using MySQL, can use a single REPLACE instead of check for existence
		// and then having to use INSERT or UPDATE
		//
		$sql = "REPLACE INTO housing_app 
							(
								pidm,
								studentid,
								student_id_entered,
								ranking,
								username,
								lastname,
								firstname,
								housing_area,
								application_type,
								mealplan,
								phone,
								intention,
								year_term,
								term,
								year,
								maritalstatus,
								studenttype,
								attending,
								smpref,
								circadia,
								interest1,
								interest2,
								interest3,
								roommate1id,
								roommate1l,
								roommate1f,
								roommate2id,
								roommate2l,
								roommate2f,
								roommate3id,
								roommate3l,
								roommate3f,
								comments,
								buildreq1,
								buildreq2,
								buildreq3,
								signed_leasedining,
								signed_foryear,
								notes,
								groupno,
								assigned,
								assigned_building,
								assigned_room,
								checkedin,
								trroompicks,
								rnroompicks,
								rn_roompick_lws,
								rn_roompick_lwd,
								rn_roompick_4ps,
								rn_roompick_2pa,
								rn_roompick_4pa,
								rn_roompick_ntrad,
								created,
								modified,
								misc

							)
						VALUES 
							(
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?,
								?
							)";

  $rs = PSU::db('reslife')->Execute($sql,
						array(
								$data['pidm'],
								$data['studentid'],
								$data['student_id_entered'],
								$ranking,
								$data['username'],
								$data['lastname'],
								$data['firstname'],
								$data['housing_area'],
								$data['application_type'],
								$data['mealplan'],
								$data['phone'],
								$data['intention'],
								$data['year_term'],
								$data['term'],
								$data['year'],
								$data['maritalstatus'],
								$data['studenttype'],
								$data['attending'],
								$data['smpref'],
								$data['circadia'],
								$data['interest1'],
								$data['interest2'],
								$data['interest3'],
								$data['roommate1id'],
								$data['roommate1l'],
								$data['roommate1f'],
								$data['roommate2id'],
								$data['roommate2l'],
								$data['roommate2f'],
								$data['roommate3id'],
								$data['roommate3l'],
								$data['roommate3f'],
								$data['comments'],
								$data['buildreq1'],
								$data['buildreq2'],
								$data['buildreq3'],
								$data['signed_leasedining'],
								$data['signed_foryear'],
								$data['notes'],
								$data['groupno'],
								$data['assigned'],
								$data['assigned_building'],
								$data['assigned_room'],
								$data['checkedin'],
								$data['trroompicks'],
								$data['rnroompicks'],
								$data['rn_roompick_lws'],
								$data['rn_roompick_lwd'],
								$data['rn_roompick_4ps'],
								$data['rn_roompick_2pa'],
								$data['rn_roompick_4pa'],
								$data['rn_roompick_ntrad'],
								$data['created'],
								$data['modified'],
								1
								
								));
	}
/**
 * save the updated Housing App information to the database
 * 
 * @param int $pidm 
 * @access public
 * @return string of query
 *
 */ 
	function checkinHousingApp($pidm, $now, $data, $area) 
	{
		// Since using MySQL, can use a single REPLACE instead of check for existence
		// and then having to use INSERT or UPDATE
		//
		$sql = " UPDATE housing_app
							SET groupno=?,
									modified=?,
									checkedin=1,
									checkedinby=?,
									checkedinwhen=?
							WHERE pidm=?
									AND housing_area=?
									AND year_term=?
									AND application_type=?";

  $rs = PSU::db('reslife')->Execute($sql,
						array(
								$data['groupno'],
								$now,
								$data['checkedinby'],
								$now,
								$pidm,
								$area,
								$data['year_term'],
								$data['application_type']
								));
	}

}
?>
