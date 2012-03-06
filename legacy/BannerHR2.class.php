<?php
/**
 * BannerHR.class.php
 *
 * === Modification History ===<br/>
 * 1.0.0  18-may-2005  [mtb]  original<br/>
 * 2.0.0  01-may-2008  [mtb]  updated to work with the new identity management infrastructure<br/>
 *
 * @package 		PSUBannerAPI
 */

/**
 * BannerHR.class.php
 *
 * Banner API
 *
 * @version		2.0.0
 * @module		BannerHR.class.php
 * @author		Matthew Batchelder <mtbatchelder@plymouth.edu>
 * @copyright 2005, Plymouth State University, ITS
 */ 
require_once('BannerGeneral.class.php');
require_once('IDMObject.class.php');

class BannerHR extends BannerGeneral
{
	/**
	 * BannerHR
	 *
	 * BannerHR constructor with db connection. 
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		ADOdb $adodb ADOdb database connection
	 */
	function BannerHR(&$adodb)
	{
		parent::__construct($adodb);
		
		if($GLOBALS['BannerIDM'])
		{
			$this->idm =& $GLOBALS['BannerIDM'];
		}//end if
		else
		{
			$this->idm = new IDMObject($adodb);
			//$GLOBALS['BannerIDM'] =& $this->idm;
		}//end else
	}//end BannerGeneral

	/**
	 * commonMatching
	 *
	 * performs common matching on a set of PZBLOAD records and returns those records with updated fields
	 *
	 * @since		version 1.0.0
	 * @access	public
	 * @param  	array $records PZBLOAD records
	 * @return	array
	 */
	function commonMatching($records)
	{
		if(is_array($records))
		{
			foreach($records as $key=>$record)
			{
				//does the record have a pidm?  if not, attempt to assign one with common matching
				if(!$record['pidm'])
				{
					$match=$this->commonMatchRecord($record['ssn'],$record['last_name'],$record['first_name'],$record['middle_name']);
					switch ($match['flag'])
					{
						case 'M':
							if($this->_ADOdb->GetOne("SELECT count(*) FROM psu_identity.person_identifiers,spbpers WHERE pid=spbpers_pidm AND spbpers_ssn='{$record['ssn']}' AND '000000000' <> '{$record['ssn']}' AND first_name='{$record['first_name']}' AND last_name='{$record['last_name']}'")>0)
							{	
								$records[$key]['flag']='old';
								$records[$key]['pidm']=$match['pidm'];
								$records[$key]['id']=$this->idm->getIdentifier($match['pidm'],'pid','psu_id');
								$this->_ADOdb->Execute("UPDATE pzbload SET pzbload_pidm={$records[$key]['pidm']},pzbload_id='{$records[$key]['id']}' WHERE pzbload_temp_id={$record['temp_id']}");
							}//end if
							else
						  {
								$records[$key]['flag']='verify';
								$records[$key]['error']='ambiguous';
								$records[$key]['note']=' :: requires manual verification via INB';
							}//end else

							break;
						case 'N':
							$records[$key]['flag']='new';
							break;
						case 'S':
							$records[$key]['flag']='verify';
							$records[$key]['error']='ambiguous';
							$records[$key]['note']=' :: requires manual verification via INB';
							break;
					}//end switch
				}//end if
				$GLOBALS['BannerHR']->updateFlag($records[$key]['temp_id'],$records[$key]['flag'],$records[$key]['error'],$records[$key]['note']);
			}//end foreach
		}//end if
		return $records;
	}//end commonMatching

	/**
	 * checkForSSNDuplicates
	 *
	 * checks the USNH HR view for duplicate ssns
	 *
	 * @since		version 1.0.0
	 * @access	public
	 * @rerturn boolean
	 */
	function checkForSSNDuplicates()
	{
		return $this->idm->db->GetOne("SELECT spbpers_ssn,count(spbpers_ssn) FROM spbpers,pzvpdir WHERE pzvpdir_ssn=spbpers_ssn AND pzvpdir_ssn IS NOT NULL AND spbpers_ssn IS NOT NULL AND pzvpdir_ssn <> '000000000' GROUP BY spbpers_ssn HAVING count(spbpers_ssn)>1");
	}//end checkForSSNDuplicates

	/**
	 * createAddress
	 *
	 * creates addresses based on HR data
	 *
	 * @since		version 1.0.0
	 * @access	public
	 * @param  	array $person PZBLOAD person record
	 * @param  	string $type Address type
	 */
	function createAddress($person,$type,$user='HR Process')
	{
		$user=($user)?$user:strtoupper($_SESSION['username']);
		$user=($user)?$user:'PSU';
		if(!$this->addressExists($person['r_pidm'],$type))
		{
			switch($type)
			{
				case 'CA':
					$params='street_line1='.urlencode($person['r_mail_stop']).'&street_line2=17 High Street&city=Plymouth&stat_code=NH&zip=03264&user='.$user;
					break;
				case 'MA':
					$params='street_line1='.urlencode(PSUTools::cleanOracle($person['r_street_line1'])).'&street_line2='.urlencode(PSUTools::cleanOracle($person['r_street_line2'])).'&city='.urlencode(PSUTools::cleanOracle($person['r_city'])).'&stat_code='.$person['r_stat_code'].'&zip='.$person['r_zip'].'&user='.$user;
					break;
				case 'OF':
					$params='street_line1='.urlencode(PSUTools::cleanOracle($person['r_department'])).'&street_line2='.urlencode(PSUTools::cleanOracle($person['r_building'])).'&city=Plymouth&stat_code=NH&zip=03264&user='.$user;
					break;
			}//end switch
			$this->updateAddress($person['r_pidm'],$type,$params);
		}//end if
	}//end createAddress

	/**
	 * createPhone
	 *
	 * creates an phone address if needed
	 *
	 * @since		version 1.0.0
	 * @access	public
	 * @param  	string $pidm Banner pidm
	 * @param  	string $type Phone type
	 */
	function createPhone($person,$type)
	{
		if(!$this->phoneExists($person['r_pidm'],$type,$type))
		{
			$regexp='/(\(([0-9]{3})\))?([0-9]{3})\-([0-9]{4})/';
			switch($type)
			{
				case 'MA':
					$parsed_phone=preg_match('/(\(([0-9]{3})\))?([0-9]{3})\-([0-9]{4})/',$person['r_home_phone'],$matches);
					$params='phone_area='.$matches[2].'&phone_number='.$matches[3].$matches[4].'&primary_ind=Y&unlist_ind=Y';
					break;
				case 'OF':
					$parsed_phone=preg_match('/(\(([0-9]{3})\))?([0-9]{3})\-*([0-9]{4})/',$person['r_campus_phone'],$matches);
					$params='phone_area=603&phone_number='.$matches[3].$matches[4].'&primary_ind=Y';
					break;
			}//end switch
			$this->updatePhone($person['r_pidm'],$type,$type,$params);
		}//end if
	}//end createPhone

	/**
	 * getNewEmployees
	 *
	 * returns a list of PZBLOAD records that do not appear in PZRIDGP
	 *
	 * @since		version 1.0.0
	 * @access	public
	 * @return	array
	 */
	function getNewEmployees()
	{
		$data=array();

		$sql="SELECT * 
							FROM pzbload
						 WHERE (NOT EXISTS (SELECT 1 
						                      FROM psu_identity.person_attribute a,
						                           psu_identity.attribute_meta m 
						                     WHERE a.pidm=pzbload_pidm
						                       AND a.type_id = 2
						                       AND a.type_id = m.type_id
						                       AND a.attribute = m.attribute
						                       AND m.meta = 'hr')
									 )
								OR pzbload_pidm IS NULL
						 ORDER BY pzbload_last_name,pzbload_first_name,pzbload_mi";
		if($results=$this->_ADOdb->Execute($sql))
		{
			while($row=$results->FetchRow())
			{
				$data[]=PSUTools::cleanKeys('pzbload_','',$row);
			}//end while
		}//end if

		return $data;
	}//end getNewEmployees

	/**
	 * getRecords
	 *
	 * returns PZBLOAD person records
	 *
	 * @since		version 1.0.0
	 * @access	public
	 * @param  	string $where PZBLOAD where clause
	 * @return	array
	 */
	function getRecords($where='',$replace='r_')
	{
		$data=array();
		$sql="SELECT * FROM pzbload ".(($where)?'WHERE '.$where:'')." ORDER BY pzbload_last_name";
		if($results=$this->_ADOdb->Execute($sql))
		{
			while($row=$results->FetchRow())
			{
				$data[]=PSUTools::cleanKeys('pzbload_',$replace,$row);
			}//end while
		}//end if
		return $data;
	}//end getRecords

	/**
	 * getSimilarNames
	 *
	 * getSimilarNames returns a list SPRIDEN records of people from Banner that have similar names to the passed in parameters
	 *
	 * @since		version 1.0.0
	 * @access	public
	 * @param  	string $first_name First Name
	 * @param  	string $middle_name Middle Name
	 * @param  	string $last_name Last Name
	 * @return	array
	 */
	function getSimilarNames($first_name,$middle_name,$last_name)
	{
		$data=array();
		$sql="SELECT * 
		        FROM spriden,spbpers 
		       WHERE spriden_pidm=spbpers_pidm 
		         AND spriden_change_ind is null 
		         AND spriden_first_name like '".substr($first_name,0,1)."%' 
		         AND spriden_last_name like '".substr($last_name,0,10)."%' 
		         AND spbpers_ssn IS NULL";
		if($results=$this->_ADOdb->Execute($sql))
		{
			while($row=$results->FetchRow())
			{
				$data[] = PSUTools::cleanKeys('spriden_', 'r_', $row);
			}//end while
		}//end if
		return $data;
	}//end getSimilarNames

	/**
	 * getUpdatedRecords
	 *
	 * returns a list PZBLOAD records that have been marked as 'updated'.  Updated
	 * means that the display title, job title, or department have changed.
	 *
	 * @since		version 1.0.0
	 * @access	public
	 * @return	array
	 */
	function getUpdatedRecords()
	{
		$where = "(
							 (EXISTS (SELECT 1 FROM psu_identity.person_attribute WHERE pidm = pzbload_pidm AND type_id = 1 AND attribute = 'directory')
							  AND
							  (EXISTS(SELECT 1 FROM psu_identity.person_attribute WHERE pidm = pzbload_pidm AND type_id = 3 AND attribute <> pzbload_display_title)
							   OR
							   EXISTS(SELECT 1 FROM psu_identity.person_attribute WHERE pidm = pzbload_pidm AND type_id = 6 AND attribute <> pzbload_job_title)
							   OR
							   EXISTS(SELECT 1 FROM psu_identity.person_attribute WHERE pidm = pzbload_pidm AND type_id = 4 AND attribute <> pzbload_department)
							  )
							 )
							 OR
							 (NOT EXISTS(SELECT 1 FROM psu_identity.person_attribute WHERE pidm = pzbload_pidm AND type_id = 2 AND attribute = pzbload_idgp_code)
							  AND
							  EXISTS(SELECT 1 FROM psu_identity.person_attribute WHERE pidm = pzbload_pidm AND type_id = 2 AND attribute = 'employee')
							 )
		          )";
		return $this->getRecords($where);
	}//end getUpdatedRecords

	/**
	 * inactivateIDCardIfNeeded
	 *
	 * inactivates the ID Card if necessary
	 *
	 * @since		version 1.0.0
	 * @access	public
	 */
	function inactivateIDCardIfNeeded($pidm)
	{
		//get attributes
		$attributes = $this->idm->getPersonAttributes($pidm);
		
		//get permissions by grabbing the permission keys
		$permissions = array_keys($attributes['permission']);
		
		//does this person still need an id card (from the HR perspective)?
		$needs_id_card = $this->_ADOdb->GetOne("SELECT 1 FROM psu_identity.attribute_meta WHERE meta = 'idcard' AND attribute IN('".implode("','",$permissions)."')");
		
		if(!$needs_id_card)
		{
			$idcard=$this->getIDCardRecord($pidm);
			if(strtolower($idcard['r_group_type'])!='student')
			{
				$this->_ADOdb->Execute("UPDATE spbcard SET spbcard_employee_status='I' WHERE spbcard_pidm=".$pidm);
			}//end if
		}//end if
	}//end inactivateIDCardIfNeeded

	/**
	 * initializeTempTable
	 *
	 * prepares PZBLOAD with new records; fixes: names, addresses, titles; and marks errors
	 *
	 * @since		version 1.0.0
	 * @access	public
	 */
	function initializeTempTable()
	{
		$search = array(
			'( |\-)(i+[IVX]|[IVX]i+|Psu|Jr|Sr)( |\-|^)',
			'([- ])Psu$',
			'([- ])(I|V)i(-PSU)?$',
			'([- ])(I|V)ii(-PSU)?$',
			'([- ])Iv(-PSU)?$',
			'([- ])It([- ])'
		);
		$replace = array(
			'\1\2\3',
			'\1PSU',
			'\1\2I\3',
			'\1\2II\3',
			'\1IV\3',
			'\1IT\2'
		);
		
		$regex = "";
		foreach($search as $key => $val)
		{
			$regex_pre = $regex_pre . "regexp_replace(";
			$regex_post .= ", '{$search[$key]}', '{$replace[$key]}') ";
		}//end foreach

		//load people from feed file
		$sql="INSERT INTO pzbload (
						pzbload_pidm,
						pzbload_id,
						pzbload_usnh_id,
						pzbload_ssn, 
						pzbload_last_name, 
						pzbload_first_name, 
						pzbload_mi, 
						pzbload_job_title, 
						pzbload_department, 
						pzbload_building, 
						pzbload_campus_phone, 
						pzbload_street_line1, 
						pzbload_street_line2, 
						pzbload_city, 
						pzbload_stat_code, 
						pzbload_zip, 
						pzbload_home_phone, 
						pzbload_email, 
						pzbload_student_ind, 
						pzbload_mail_stop, 
						pzbload_idgp_code, 
						pzbload_display_title,
						pzbload_flag,
						pzbload_error
					) (
						SELECT (SELECT spbpers_pidm 
						          FROM spbpers 
						         WHERE spbpers_ssn = p.pzvpdir_ssn 
						           AND spbpers_ssn is not null),
			 	  				 (SELECT spriden_id 
			 	  				    FROM spriden,spbpers 
			 	  				   WHERE spbpers_pidm = spriden_pidm 
			 	  				     AND spbpers_ssn = p.pzvpdir_id 
			 	  				     AND spriden_change_ind is null),
									 p.pzvpdir_id, 
									 p.pzvpdir_ssn, 
						       ".$regex_pre." initcap(p.pzvpdir_lname) ".$regex_post.", 
						       initcap(p.pzvpdir_fname), 
						       initcap(p.pzvpdir_mi), 
						       ".$regex_pre." initcap(nvl(p.pzvpdir_jobtit, 'Unknown')) ".$regex_post.", 
						       p.pzvpdir_caddr1, 
						       p.pzvpdir_caddr2, 
						       p.pzvpdir_cphone, 
						       regexp_replace(p.pzvpdir_haddr1, '[;:,\.#]', ''), 
						       regexp_replace(p.pzvpdir_haddr2, '[;:,\.#]', ''), 
						       p.pzvpdir_city, 
						       p.pzvpdir_state, 
						       p.pzvpdir_zip, 
						       p.pzvpdir_hphone, 
						       p.pzvpdir_email, 
						       p.pzvpdir_stud, 
						       p.pzvpdir_mstop, 
						       p.pzvpdir_ecls_code, 
						       nvl(p.pzvpdir_dirtit, 'Unknown'),
						       CASE
						         WHEN p.pzvpdir_caddr1 IS NULL THEN 'verify'
						         WHEN p.pzvpdir_ecls_code IS NULL THEN 'verify'
						         ELSE NULL
						       END,
						       CASE
						         WHEN p.pzvpdir_caddr1 IS NULL THEN 'department'
						         WHEN p.pzvpdir_ecls_code IS NULL THEN 'classification'
						         ELSE NULL
						       END
						  FROM pzvpdir p 
						 WHERE NOT EXISTS (SELECT 1 FROM pzbload WHERE pzbload.pzbload_usnh_id = p.pzvpdir_id)
							 AND pzvpdir_id <> '000000000'
					)";
		$this->_ADOdb->Execute($sql);

		$sql="UPDATE pzbload 
		         SET pzbload_flag='old' 
		       WHERE pzbload_pidm IS NOT NULL 
		         AND exists(SELECT 1 
		                      FROM psu_identity.person_attribute 
		                     WHERE pidm = pzbload_pidm 
		                       AND type_id = 2 
		                       AND attribute = pzbload_idgp_code)";
		$this->_ADOdb->Execute($sql);
	}//end initializeTempTable

	/**
	 * markMissingFields
	 *
	 * if department or classifcations are missing in PZBLOAD, mark those records
	 *
	 * @since		version 1.0.0
	 * @access	public
	 */
	function markMissingFields()
	{
		$sql="UPDATE pzbload SET pzbload_flag='verify',pzbload_error='department' WHERE pzbload_idgp_code <> 'X0' AND pzbload_department is null";
		$this->_ADOdb->Execute($sql);

		$sql="UPDATE pzbload SET pzbload_flag='verify',pzbload_error='classification' WHERE pzbload_idgp_code is null";
		$this->_ADOdb->Execute($sql);
	}//end markMissingFields

	/**
	 * markReclassify
	 *
	 * if department or classifcations have changed, mark those records as updated
	 *
	 * @since		version 1.0.0
	 * @access	public
	 */
	function markReclassify()
	{
		$sql="UPDATE pzbload 
		         SET pzbload_flag='updated' 
		       WHERE pzbload_pidm IS NOT NULL 
		         AND (
							 (EXISTS (SELECT 1 FROM psu_identity.person_attribute WHERE pidm = pzbload_pidm AND type_id = 1 AND attribute = 'directory')
							  AND
							  (EXISTS(SELECT 1 FROM psu_identity.person_attribute WHERE pidm = pzbload_pidm AND type_id = 3 AND attribute <> pzbload_display_title)
							   OR
							   EXISTS(SELECT 1 FROM psu_identity.person_attribute WHERE pidm = pzbload_pidm AND type_id = 6 AND attribute <> pzbload_job_title)
							   OR
							   EXISTS(SELECT 1 FROM psu_identity.person_attribute WHERE pidm = pzbload_pidm AND type_id = 4 AND attribute <> pzbload_department)
							  )
							 )
							 OR
							 (NOT EXISTS(SELECT 1 FROM psu_identity.person_attribute WHERE pidm = pzbload_pidm AND type_id = 2 AND attribute = pzbload_idgp_code)
							  AND
							  EXISTS(SELECT 1 FROM psu_identity.person_attribute WHERE pidm = pzbload_pidm AND type_id = 2 AND attribute = 'employee')
							 )
		          )";
		$this->_ADOdb->Execute($sql);
	}//end markReclassify

	/**
	 * processRecord
	 *
	 * Assigns roles and attributes for the given person.  Also, triggers the creation/updating
	 * of usernames, addresses, phone numbers, id cards, etc where needed.
	 *
	 * @since		version 1.0.0
	 * @param   array $person Person record from PZBLOAD
	 * @access	public
	 */
	function processRecord($person)
	{
		//retrieve a list of all classifcations
		$classifications = $this->idm->getAttributesByMeta('classification');
		$classifications = $classifications['role'];
		
		$role_log = $this->idm->getLogs($person['r_pidm'],"source='hr' AND attribute = '".$person['r_idgp_code']."'");
		
		if(empty($role_log['role']))
		{
			$this->idm->addAttribute($person['r_pidm'],'role',$person['r_idgp_code'],'hr','granted_by=HR Process');
		}//end if
		
		$this->createUsernameIfNeeded($person['r_pidm']);

		//grab the user's current attributes
		$attributes = $this->idm->getPersonAttributes($person['r_pidm']);

		//get the user's hr classification role log entries
		$role_log = $this->idm->getLogs($person['r_pidm'],"source='hr' AND attribute <> '".$person['r_idgp_code']."' AND attribute IN('".implode("','",$classifications)."')");

		if(!empty($role_log['role']))
		{
			$altered_attributes = true;
			foreach($role_log['role'] as $role)
			{
				foreach($role as $role_id => $role_data)
				{
					$this->idm->removeAttribute($person['r_pidm'],$role_id);
				}//end foreach
			}//end foreach
		}//end if			
		
		//were any roles revoked that would cause attributes to change?
		if($altered_attributes)
		{
			//yup!  retrieve the new attributes
			$attributes = $this->idm->getPersonAttributes($person['r_pidm']);
			$altered_attributes = false;
		}//end if

		if(isset($attributes['permission']['email'])) 
			$this->createEmailIfNeeded($person['r_pidm']);

		if(isset($attributes['permission']['directory']))
		{
			if($attributes['department']['attribute'] != $person['r_department'])
			{
				$this->idm->setAttribute($person['r_pidm'],'department',$person['r_department'],'hr',false,'granted_by=HR Process');
			}//end if
			
			if($attributes['display_title']['attribute'] != $person['r_display_title'])
			{
				$this->idm->setAttribute($person['r_pidm'],'display_title',$person['r_display_title'],'hr',false,'granted_by=HR Process');
			}//end if
			
			if($attributes['job_title']['attribute'] != $person['r_job_title'])
			{
				$this->idm->setAttribute($person['r_pidm'],'job_title',$person['r_job_title'],'hr',false,'granted_by=HR Process');
			}//end if
		}//end if

		if(isset($attributes['permission']['ca_addr']))
		{
			$this->createAddress($person,'CA');
		}//end if

		if(isset($attributes['permission']['of_addr']))
		{
			$this->createAddress($person,'OF');
			$this->createPhone($person,'OF');
		}//end if

		if(isset($attributes['permission']['ma_addr']))
		{
			$this->createAddress($person,'MA');
			$this->createPhone($person,'MA');
		}//end if

		$group_type=false;
		if(isset($attributes['permission']['guest_idcard'])) $group_type='Guest';
		elseif(isset($attributes['permission']['facstaff_idcard'])) $group_type='Faculty/Staff';
		elseif(isset($attributes['permission']['student_idcard'])) $group_type='Student';
		elseif(isset($attributes['permission']['retired_idcard'])) $group_type='Retired';
		elseif(isset($attributes['permission']['faculty_idcard'])) $group_type='Faculty';
		elseif(isset($attributes['permission']['nonemp_idcard'])) $group_type='Non PSU';
		
		if($group_type)
		{
			$this->createIDCardIfNeeded($person['r_pidm'],$group_type,'A',$benefitted,$person['r_department']);
		}//end if

		$this->_ADOdb->Execute("UPDATE pzbload SET pzbload_flag='old' WHERE pzbload_pidm={$person['r_pidm']}");
	}//end processRecord

	/**
	 * processRecordByTempId
	 *
	 * Calls processRecord and accepts a PZBLOAD temp_id
	 *
	 * @since		version 1.0.0
	 * @param   int $temp_id PZBLOAD temp id
	 * @access	public
	 */
	function processRecordByTempId($temp_id)
	{
		$records=$this->getRecords('pzbload_temp_id='.$temp_id);

		$name = $records[0]['r_last_name'] . ', ' . $records[0]['r_first_name'];

		if( '000000000' == $records[0]['r_ssn'] ) {
			throw new Exception("Name: {$name}");
		} elseif($records[0]['r_pidm']) {
			$this->processRecord($records[0]);
		} else {
			throw new HRException(HRException::NO_PIDM_FOUND, " Name: {$name}");
		}//end else
	}//end processRecordByTempId

	/**
	 * processRecordsByClassification
	 *
	 * Calls processRecord for all people with the given classification(s)
	 *
	 * @since		version 1.0.0
	 * @param   mixed $classification Employee Classification
	 * @access	public
	 */
	function processRecordsByClassification($classification)
	{
		if(!is_array($classification))
		{
			$classification = array($classification);
		}//end if

		$records = $this->getRecords("pzbload_flag = 'updated' AND pzbload_pidm IS NOT NULL AND pzbload_idgp_code in ('".implode("','",$classification)."')");
		if(is_array($records))
		{
			foreach($records as $record)
			{
				$this->processRecord($record);
			}//end foreach
		}//end if
	}//end processRecordsByClassification

	/**
	 * removeRecord
	 *
	 * Removes HR data on a given person
	 *
	 * @since		version 1.0.0
	 * @param   int $pidm Person identifier
	 * @return  boolean
	 * @access	public
	 */
	function removeRecord($pidm)
	{
		return removeRecords($pidm);		
	}//end removeRecord

	/**
	 * removeRecord
	 *
	 * Removes HR data a person/group of people.  PZBLOAD must be fully populated
	 * prior to group removal
	 *
	 * @since		version 1.0.0
	 * @param   int $pidm Person identifier
	 * @return  boolean
	 * @access	public
	 */
	function removeRecords($pidm = false)
	{
		if($this->_ADOdb->GetOne("SELECT count(*) FROM pzbload WHERE pzbload_pidm IS NOT NULL")<=0) 
		{
			throw new HRException(HRException::HR_VIEW);
		}//end if
		
		$sql="SELECT a.pidm,l.id
						FROM psu_identity.person_attribute a,
								 psu_identity.attribute_meta m,
								 psu_identity.attribute_type at,
								 psu_identity.person_attribute_log l
					 WHERE a.type_id = at.id
						 AND at.name = 'role'
						 AND NOT EXISTS(SELECT 1 FROM pzbload WHERE pzbload_pidm=a.pidm)
						 AND m.type_id = a.type_id 
						 AND m.attribute = a.attribute 
						 AND m.meta = 'classification'
						 AND a.pidm = l.pidm
						 AND l.source = 'hr'
						 AND l.origin_id IS NULL
						 ".(($pidm)?"AND a.pidm = ".$pidm:"")."
					 ORDER BY a.pidm,l.id";
		if($results=$this->_ADOdb->Execute($sql))
		{
			$pidm = 0;

			while($row=$results->FetchRow())
			{			
				$row = psu::cleanKeys('','',$row);
				if($pidm != $row['pidm'])
				{
					//pidm is switching to a new person

					$active_faculty = $this->idm->db->GetOne("SELECT 1 FROM v_faculty WHERE pidm = :pidm", array('pidm' => $row['pidm']));
					
					//inactivate the person's ID Card if needed
					if($pidm) $this->inactivateIDCardIfNeeded($pidm);
					
					//switch to the new person
					$pidm = $row['pidm'];
				}//end if
				
				if(!$active_faculty) $this->idm->removeAttribute($row['pidm'],$row['id']);
			}//end while
			//inactivate the ID Card of the last person we checked
			if($pidm) $this->inactivateIDCardIfNeeded($pidm);
		}//end if
		
		return true;
	}//end removeRecords

	/**
	 * setFlag
	 *
	 * Sets a PZBLOAD flag based on SSN
	 *
	 * @since		version 1.0.0
	 * @param   string $ssn SSN
	 * @param   string $flag Flag to be set
	 * @access	public
	 */
	function setFlag($ssn,$flag)
	{
		$clear=($flag=='new')?",pzbload_pidm=NULL,pzbload_id=NULL":"";
		$flag=($flag)?"'$flag'":"NULL";
		$sql="UPDATE pzbload SET pzbload_flag=$flag $clear WHERE pzbload_ssn='$ssn'";
		$this->_ADOdb->Execute($sql);
	}//end setFlag

	/**
	 * ssnExists
	 *
	 * Checks to see if a given SSN exists in SPBPERS
	 *
	 * @since		version 1.0.0
	 * @param   string $ssn SSN
	 * @return boolean
	 * @access	public
	 */
	function ssnExists($ssn)
	{
		return $this->_ADOdb->GetOne("SELECT 1 FROM spbpers WHERE spbpers_ssn='$ssn'");
	}//end ssnExists

	/**
	 * truncateTempTable
	 *
	 * Truncates the PZBLOAD table
	 *
	 * @since		version 1.0.0
	 * @access	public
	 */
	function truncateTempTable()
	{
		if($this->_ADOdb->Execute("SELECT count(*) FROM pzvpdir@BPRD.world"))
		{
			// this logic is from here: http://asktom.oracle.com/pls/asktom/f?p=100:11:0::::P11_QUESTION_ID:951269671592
			$this->_ADOdb->Execute("TRUNCATE TABLE pzbload");
			return true;
		}//end if
		else
		{
			throw new HRException(HRException::HR_VIEW);
		}//end else
	}//end truncateTempTable

	/**
	 * updateDepartment
	 *
	 * Updates the PZVDEPT table
	 *
	 * @since		version 1.0.0
	 * @access	public
	 * @param   string $code Department Code
	 * @param   string $desc Department Description/Name
	 * @return  boolean
	 */
	function updateDepartment($code,$desc)
	{
		$code=PSUTools::makeSafe($code);
		$desc=PSUTools::makeSafe($desc);
		$sql="UPDATE pzvdept SET
						pzvdept_desc=".$this->_ADOdb->qstr($desc)."
					WHERE pzvdept_code=".$this->_ADOdb->qstr($code);

		if($this->_ADOdb->Execute($sql))
			return $desc;
		return false;
	}//end updateDepartment

	function updateFlag($temp_id,$flag,$error=false,$note='')
	{
		$this->_ADOdb->Execute("UPDATE pzbload SET pzbload_flag='$flag'".(($error!==false)?",pzbload_error='$error'":'')."".(($note)?",pzbload_note='$note'":'')." WHERE pzbload_temp_id=$temp_id");
	}//end updateFlag
}//end class BannerHR

/**
 * HRException
 *
 * Provides an exception class for use with IDMObject.
 *
 * @package			Banner
 */
require_once('PSUException.class.php');
class HRException extends PSUException {
	const HR_VIEW = 1; // HR View is not accessible 
	const EMPTY_LOAD = 2; // HR Load table is empty
	const NO_PIDM_FOUND = 3; //No pidm can be found for this person
	const INVALID_SSN = 4; 

	private static $_msgs = array(
		self::HR_VIEW => 'USNH HR View is not reachable.',
		self::EMPTY_LOAD => 'HR Load table (PZBLOAD) is currently empty.',
		self::NO_PIDM_FOUND => 'No pidm can be found for this person.  Manual intervention is required to ensure data integrity. ',
		self::INVALID_SSN => 'This person\'s SSN is invalid (000-00-0000 no worky). ',
	);

	/**
	 * Wrapper construct so PSUException gets our message array.
	 */
	function __construct($code, $append=null)
	{
		parent::__construct($code, $append, self::$_msgs);
	}//end constructor
}//end class HRException
