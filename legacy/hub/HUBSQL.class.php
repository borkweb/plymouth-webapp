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
 *
 * @param int $pidm
 * @access public
 *
 */
  function getBMReports()
  {
    $sql = "SELECT *
							FROM `bm_reports` bmr
							WHERE bmr.submitted = 1
              ORDER BY bmr.a_datetime DESC";

    return PSU::db('hub')->GetAll($sql);
  }

/**
 * get the building manager report by the id passed - these are submitted - this is used by administrators
 * 
 * @param int $id 
 * @access public
 *
 */ 
	function getBMReportById($id) 
	{
		$sql = "SELECT * FROM `bm_reports` bmr 
						WHERE bmr.id = ? 
							AND bmr.submitted = 1"; 

		
    if ($data = PSU::db('hub')->GetRow($sql, array($id)))
    {
		  $sql = "SELECT * FROM `bm_rounds` rounds WHERE rounds.bmr_id=? ORDER BY time ASC";
      $data['rounds'] = PSU::db('hub')->GetAll($sql, array($id));
      return $data;
    }

	return $null;
	}

/**
 * get the open building manager report for this bm (if they currently have one that is saved and not submitted)
 * 
 * @param int $pidm 
 * @access public
 *
 */ 
	function getBMReportByPidm($pidm) 
	{
		$sql = "SELECT * FROM `bm_reports` bmr 
						WHERE bmr.pidm = ? 
							AND bmr.submitted = 0"; 

    if ($data = PSU::db('hub')->GetRow($sql, array($pidm)))
    {
		  $sql = "SELECT * FROM `bm_rounds` rounds WHERE rounds.bmr_id=? ORDER BY time ASC";
      $data['rounds'] = PSU::db('hub')->GetAll($sql, array($data['id']));
      return $data;
    }

	return $null;
	}

/**
 * add a Round Entry for Building Manager Report to the MySQL hub database
 * 
 */ 
	function addBMRRound($now, $data) 
	{
		// don't save the round if the time is blank - didn't fill anything in...
		// might be just doing a submit - didn't do a round
	  if ($data['r_htime'] == '' && $data['r_mtime'] == '' && $data['notes'] == '')
			return null;

		$data['time'] = date("H:i:s", strtotime($data['r_htime'] . ':' . $data['r_mtime'] . ' ' . $data['r_ampmtime']));

		$sql = "INSERT INTO bm_rounds
							(
								bmr_id,
								courtroom,
								cardio,
								rm119,
								rm109,
								hage,
								rm123,
								tower,
								fpl,
								cluster,
								fitness,
								uniongrille,
								aerobics,
								total,
								notes,
								time,
								disp_time,
								created
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
								?
							)";

		$rs = PSU::db('hub')->Execute($sql,
						array(
								$data['bmr_id'],
								$data['r_rm_courtroom'],
								$data['r_rm_cardio'],
								$data['r_rm_119'],
								$data['r_rm_109'],
								$data['r_rm_hage'],
								$data['r_rm_123'],
								$data['r_rm_tower'],
								$data['r_rm_fpl'],
								$data['r_rm_cluster'],
								$data['r_rm_fitness'],
								$data['r_rm_grille'],
								$data['r_rm_aerobics'],

								//total field:
								$data['r_rm_courtroom'] +
								$data['r_rm_cardio'] +
								$data['r_rm_119'] +
								$data['r_rm_109'] +
								$data['r_rm_hage'] +
								$data['r_rm_123'] +
								$data['r_rm_tower'] +
								$data['r_rm_fpl'] +
								$data['r_rm_cluster'] +
								$data['r_rm_fitness'] +
								$data['r_rm_grille'] +
								$data['r_rm_aerobics'],

								$data['r_notes'],
								$data['time'],
								date('h:i a', strtotime($data['time'])),
								$now,
								));
		return $rs;
	}

/**
 * save the updated Building Manager Report information to the database
 * 
 * @param int $pidm 
 * @access public
 *
 */ 
	function saveBMR($filer, $now, $data) 
	{
		$data['username'] = $filer->username;
		$data['lastname'] = $filer->formatName('l');
		$data['firstname'] = $filer->formatName('f');
    $data['bmr_date'] = date('Y-m-d', strtotime($data['idate']));

		// get previously saved version of the report...
		$bmr = $this->getBMReportByPidm($filer->pidm);

		$data['modified'] = $now;

		// if created is set - already saved one "round" get prev. saved information...
		if (isset($bmr['created']))
		{
			$data['created'] = $bmr['created'];

			// Since using MySQL, can use a single REPLACE instead of check for existence
			// and then having to use INSERT or UPDATE
			//
			$sql = " UPDATE bm_reports
								SET 
									pidm=?,
									username=?,
									lastname=?,
									firstname=?,
									bmr_date=?,
									shift_id=?,
									shift_start=?,
									shift_end=?,
									ar_ir_filed=?,
									cash_box_turned_in=?,
									fitness_room_checked=?,
									salmon_book_checked=?,
									submitted=?,
									created=?,
									modified=?
							WHERE id=?";

			$rs = PSU::db('hub')->Execute($sql,
							array(
									$filer->pidm,
									$data['username'],
									$data['lastname'],
									$data['firstname'],
									$data['bmr_date'],
									$data['sh_id'],
									$data['shift_start'],
									$data['shift_end'],
									$data['ar_ir_report'],
									$data['cash_box'],
									$data['rec_facility_conditions'],
									$data['salmon_sheet'],
									0,
									$data['created'],
									$data['modified'],
									$bmr['id']
									));

			$data['bmr_id'] = $bmr['id'];
		}
		else
		{
			$data['created'] = $now;
			// this is an INSERT - endeavored to use REPLACE and neither Porter or Betsy could get it to function
			$sql = "INSERT INTO bm_reports 
								(
									pidm,
									username,
									lastname,
									firstname,
									bmr_date,
									shift_id,
									shift_start,
									shift_end,
									ar_ir_filed,
									cash_box_turned_in,
									fitness_room_checked,
									salmon_book_checked,
									submitted,
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
									0,
									?,
									?
								)";

		$rs = PSU::db('hub')->Execute($sql,
							array(
									$filer->pidm,
									$data['username'],
									$data['lastname'],
									$data['firstname'],
									$data['idate'],
									$data['sh_id'],
									$data['shift_start'],
									$data['shift_end'],
									$data['ar_ir_report'],
									$data['cash_box'],
									$data['rec_facility_conditions'],
									$data['salmon_sheet'],
									$data['created'],
									$data['modified'],
									));

		$bmr_id = PSU::db('hub')->Insert_ID();
		$data['bmr_id'] = $bmr_id;
		}

	$this->addBMRRound($now, $data);
	}

/**
 * submit the Building Manager Report
 * 
 * @param int $pidm 
 * @access public
 *
 */ 
	function submitBMR($id, $now) 
	{
		$sql = "SELECT SUM( `total` ) FROM `bm_rounds` WHERE bmr_id=?";
		$shift_total = PSU::db('hub')->GetOne($sql, array($id));

		$sql = " UPDATE bm_reports
							SET modified=?,
									submitted=1,
									submittedwhen=?,
									shift_total=?
							WHERE id=? AND submitted=0";

		$rs = PSU::db('hub')->Execute($sql,
						array(
								$now,
								$now,
								$shift_total,
								$id,
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
