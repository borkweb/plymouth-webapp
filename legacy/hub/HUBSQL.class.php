<?php
/**
 * contains SQL statements for ResLife 
 *
 *
 * access public
 * @version		0.0.1
 * @module		HUBSQL.class.php
 * @author		Betsy Coleman <bscoleman@plymouth.edu>
 * @copyright 2010, Plymouth State University, Hartman Union
 */ 

 /**
 * contains sql definitions
 *
 * @package HUBSQL.class.php
 */ 
class HUBSQL
{

/**
 *
 * contains object of database
 * @access public
 * @var object
 *
 **/
 /**
 * sets instance of resutil connection to var resutil
 *
 * @param object $resutil
 * @access public
 *
 **/
  function __construct()
  {
  }


/**
 * add an Note Entry for Accident Reports to the MySQL hub database
 * 
 */ 
	function addAccidentNote($filer, $data, $now) 
	{
		$sql = "REPLACE INTO accident_notes
							(
								accident_id,
								lastname,
								firstname,
								username,
								note,
								created
							)
						VALUES 
							(
								?,
								?,
								?,
								?,
								?,
								?
							)";

		$rs = PSU::db('hub')->Execute($sql,
						array(
								$data['accident_id'],
								$filer->formatName('l'),
								$filer->formatName('f'),
								$filer->username,
								$data['notes'],
								$now,
								));
	}

/**
 * add an Accident Witness Entry for Accident Reports to the MySQL hub database
 * 
 */ 
	function addAccidentWitness($data) 
	{
		$sql = "REPLACE INTO accident_witnesses
							(
								accident_id,
								lastname,
								firstname,
								address,
								phone
							)
						VALUES 
							(
								?,
								?,
								?,
								?,
								?
							)";

		$rs = PSU::db('hub')->Execute($sql,
						array(
								$data['accident_id'],
								$data['lastname'],
								$data['firstname'],
								$data['address'],
								$data['phone']
								));
	}

/**
 * add an Accident Report to the MySQL hub database
 * 
 * @access public
 *
 */ 
	function addAccidentReport($filer, $data, $now) 
	{
		// The incident report spans 2 tables, the accedent_reports table and the
		// accident_witnesses table. The accident_witnesses table is used to store the 
		// people who saw the accident 

		// Since using MySQL, can use a single REPLACE instead of check for existence
		// and then having to use INSERT or UPDATE
		//
		$sql = "REPLACE INTO accident_reports 
							(
							  injured_first,
								injured_last,
								injured_phone,
								injured_gender,
								injured_studentstatus,
								injured_id,
								a_datetime,
								location_code,
								location,
								activity,
								injury_type,
								filer_pidm,
								filer_id,
								filer_username,
								description,
								action_taken,
								medical_treatment,
								assisted_by_personnel,
								notes,
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

		$rs = PSU::db('hub')->Execute($sql,
						array(
							  $data['injured_fname'],
								$data['injured_lname'],
								$data['injured_phone'],
								$data['gender'],
								$data['status'],
								$data['banner_id'],
								$data['a_datetime'],
								$data['alocation_code'],
								$data['loc_desc'],
								$data['activity'],
								$data['injury_type'],
								$filer->pidm,
								$filer->id,
								$filer->username,
								$data['injury_desc'],
								$data['action_taken'],
								$data['a_medtreatment'],
								$data['safety_personnel'],
								$data['notes'],
								$now,
								$now
							));

		$accident_id = PSU::db('hub')->Insert_ID();

    foreach($data['aWitnesses'] as $key => $witness)
    {
			if (!empty($witness['witness_lname']))
			{
			  $savedata = array(
								'accident_id' => $accident_id,
								'lastname' => $witness['witness_lname'],
								'firstname' => $witness['witness_fname'],
								'address' => $witness['witness_address'],
								'phone' => $witness['witness_phone']
				);
			$this->addAccidentWitness($savedata);
			}
		} //end foreach

	return $accident_id;
	}

/**
 * add an Accident Report to the MySQL hub database
 * 
 * @access public
 *
 */ 
	function getAccidentReport($id) 
	{
		// The incident report spans 3 tables, the accident_reports table the
		// accident_witnesses table, and the accident_notes table. The accident_witnesses table is used to store the 
		// people who saw the accident 
		// The accident_notes table is used to store follow up information


    $sql = "SELECT * FROM `accident_reports` areport WHERE areport.id=?";

    if ($data = PSU::db('hub')->GetRow($sql, array($id)))
    {
		  $sql = "SELECT * FROM `accident_witnesses` witnesses WHERE witnesses.accident_id=?";
      $rs = PSU::db('hub')->Execute($sql, array($id));
			$witnesses = $rs->GetRows();
			$data['witnesses'] = $witnesses;

		  $sql = "SELECT * FROM `accident_notes` notes WHERE notes.accident_id=?";
      $rs = PSU::db('hub')->Execute($sql, array($id));
			$notes = $rs->GetRows();
			$data['notes'] = $notes;
      return $data;
    }

	return $null;
	}
/**
 *
 * @param int $pidm
 * @access public
 * @return string of query
 *
 */
  function getAccidentReports()
  {
    $sql = "SELECT *
              FROM `accident_reports` accident_report
              ORDER BY accident_report.a_datetime DESC";

    return PSU::db('hub')->GetAll($sql);
  }

/**
 * add an Incident Report to the MySQL hub database
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
								phone,
								position
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
								?
							)";

		$rs = PSU::db('hub')->Execute($sql,
						array(
								$data['incident_id'],
								$data['person_type'],
								$data['lastname'],
								$data['firstname'],
								$data['person_id'],
								$data['username'],
								$data['pidm'],
								$data['phone'],
								$data['position']
								));
	}

/**
 * add an Incident Report to the MySQL hub database
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

		$rs = PSU::db('hub')->Execute($sql,
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

		$incident_id = PSU::db('hub')->Insert_ID();

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
								'username' => 'username_here',	// HARD CODED FOR NOW
								'pidm' => $student['si_lname'],
								'phone' => $student['si_phone']
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
								'position' => $staff['staff_position']
				);
			$this->addIncidentPerson($savedata);
			}
		} //end foreach

	return $incident_id;
	}

}
?>
