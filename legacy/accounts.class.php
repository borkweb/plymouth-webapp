<?php

/*
 * accounts.class.php
 *
 * === Modification History ===
 * 1.0.0  09-oct-2007  [mtb]  original
 */

require_once('PSUPerson.class.php');
require_once('BannerGeneral.class.php');

/**
 * accounts.class.php
 *
 * Account API
 *
 * @version		1.0.0
 * @module		accounts.class.php
 * @author		Matthew Batchelder <mtbatchelder@plymouth.edu>
 * @copyright 2007, Plymouth State University, ITS
 */ 
class accounts
{
	var $banner;
	var $datamart;
	var $userdb;
	var $debug;
	var $msg;
	var $line;

	/**
	 * __construct
	 *
	 * accounts constructor with db connection
	 *
	 * @since		version 1.0.0
	 * @param  		ADOdb $banner ADOdb database connection
	 * @param  		ADOdb $datamart ADOdb database connection
	 * @param  		ADOdb $user_db ADOdb database connection
	 */
	function __construct(&$banner,&$datamart,&$userdb,&$idm,$debug = true,$web = false)
	{
		$this->banner = $banner;
		$this->datamart = $datamart;
		$this->userdb = $userdb;
		$this->banner_general = new BannerGeneral($banner);
		
		$this->idm = $idm;
		
		$this->debug = $debug;
		if($web)
		{
			$this->line = '<br/>'."\n";
			$this->error = ' <span class="error">[ERROR]</span>';
		}//end if
		else
		{
			$this->line = "\n";
			$this->error = ' [ERROR]';
		}//end else
						
		if($this->debug) 
		{
			echo 'Turning debug: ON'.$this->line;
			$this->msg = ' (DEBUG) ';
		}//end if
		else 
		{
			echo 'Turning debug: OFF'.$this->line;
			$this->msg = '';
		}//end else		
	}//end constructor

	/**
	 * clearTempTable
	 *
	 * Clear the temp table used in account creation.
	 *
	 * @since		version 1.0.0
	 */
	function clearTempTable()
	{
		return $this->userdb->Execute("DELETE FROM USER_TEMP WHERE STATUS_FLAG=1");
	}//end clearTempTable
	
	/**
	 * createTempRecord
	 *
	 * Check the Mysql database on pluto, does this person already have an
	 *   active account?   If not, then place a record in the holding table for
	 *   later pickup by the account creation process.
	 *
	 * @since		version 1.0.0
	 * @param		array $person person record
	 */
	function createTempRecord(&$person,$error_helper)
	{
		//get oz username
		if($error_helper == 'ALUMNI') {
			$person->oz_username = $GLOBALS['PLUTO']->GetOne("SELECT user_uname FROM USER_DB WHERE pidm='".$person->pidm."' AND user_alumni = 1");
		} else {
			$person->oz_username = $GLOBALS['PLUTO']->GetOne("SELECT user_uname FROM USER_DB WHERE pidm='".$person->pidm."' AND user_active!=0");
		}//end else
		
		//if no username exists
		if(!$person->oz_username)
		{
			// todo: on duplicate key update
			$sql="INSERT INTO USER_TEMP
				(PIDM, USER_SSN, USER_FOREIGNSSN, USER_CERTNUM, USER_FIRST, USER_MIDDLE, USER_LAST, USER_UNAME, USER_ALT_EMAIL, STATUS_FLAG, CREATE_DATE, UPDATE_DATE)
				VALUES( ?, ?, ?, ?, ?, ?, ?, ?, '', ".($error_helper == 'ALUMNI' ? 3 : 2).", NOW(), NOW() )";

			$args = array(
				'pidm' => $person->pidm,
				'ssn' => $person->ssn ? $person->ssn : ($person->foreign_ssn ? $person->foreign_ssn : $person->certification_number),
				'foreign_ssn' => $person->foreign_ssn ? $person->foreign_ssn : '',
				'certification_number' => $person->certification_number ? $person->certification_number : '',
				'first_name' => $person->first_name,
				'middle_name' => empty($person->middle_name) ? null : substr($person->middle_name, 0, 1),
				'last_name' => $person->last_name,
				'username' => $person->username
			);

			if($args['ssn'] == null) $args['ssn'] = '';

			if( !$args['ssn'] ) {
					echo $this->line.':: [NOTICE] Skipping '.$error_helper.' ('.$person->pidm.').  No unique identifier (SSN, Foreign SSN, Cert Number, etc) provided.'.$this->line;
			} else {
				echo $this->line.':: New '.$error_helper.' User for OZ ('.$person->pidm.')'.$this->line;
		
				if(!$this->userdb->Execute($sql, $args))
				{
					echo $this->line.'::'.$this->error.' Unable to insert '.$error_helper.' record into USER_TEMP for ('.$person->pidm.')'.$this->line;
					echo '::'.$this->error.' ['.$this->userdb->ErrorMsg().']'.$this->line;
					echo '::'.$this->error.' '.$sql.$this->line;
				}//end if
				else
				{
					return true;
				}//end else
			}//end else
		}//end if
	}//end createTempRecord

	/**
	 * datamartBuildCount
	 *
	 * returns the number of records within DATA_MART_BUILD
	 *
	 * @since		version 1.0.0
	 * @return		int
	 */
	function datamartBuildCount()
	{
		return $this->datamart->GetOne("SELECT count(*) FROM DATA_MART_BUILD");
	}//end datamartBuildCount

	/**
	 * existsInDatamart
	 *
	 * returns whether or not a user exists in the data_mart_build table
	 *   during execution
	 *
	 * @since		version 1.0.0
	 * @return		boolean
	 */
	function existsInDatamart($pidm)
	{
		return $this->datamart->GetOne("SELECT 1 FROM DATA_MART_BUILD WHERE pidm=$pidm");
	}//end existsInDatamart

	/**
	 * generatePopulations
	 *
	 * creates populations that the account process references for accounts
	 *   during execution
	 *
	 * @since		version 1.0.0
	 * @return		boolean
	 */
	function generatePopulations()
	{
		echo $this->line.":: Retrieving Employees and Non Employees from Banner".$this->line;
		
		//grab employees
		$sql = "SELECT distinct pidm 
		          FROM psu_identity.person_attribute 
		         WHERE attribute in('staff', 'faculty', 'lecturer') 
		           AND type_id = 2
		           AND not exists(SELECT 1 FROM spbpers WHERE spbpers_pidm = pidm AND spbpers_dead_ind = 'Y')";
		
		$this->emps = $this->idm->db->GetAll($sql);

		//grab non employees
		$this->non_emps = $this->idm->db->GetAll("SELECT * FROM v_psu_friend");

		$this->emeritus = psu::db('banner')->GetAll("SELECT distinct pidm FROM v_alumni_emeritus UNION SELECT distinct pidm FROM v_alumni_campus");

		//check counts
		if($this->verifyCounts())
		{
			//all good...continue
			return true;
		}//end if
		else
		{
			//oh noes!  death to us
			return false;
		}//end else
	}//end generatePopulations

	/**
	 * getSQL
	 *
	 * returns the appropriate SQL insert/update depending on the parameters passed.
	 *
	 * @since		version 1.0.0
	 * @param			array $person Person data
	 * @param			string $query SQL statement required
	 * @return		boolean
	 */
	function getSQL(&$person,$query)
	{
		$sql='';
		
		switch($query)
		{
			case 'insert_employee':
				$sql = "INSERT INTO DATA_MART_BUILD 
								(timeval,
									pidm,
									id,
									ssn,
									first_name,
									middle_name,
									last_name,
									user_name,
									emp_type,
									emp_title,
									emp_dept,
									emp_msc,
									emp_building,
									emp_room,
									emp_phone,
									flag_pat,
									flag_os,
									flag_faculty,
									flag_pa,
									flag_supervisor,
									flag_lecturer,
									flag_dept_contact,
									flag_dept_chair,
									flag_supplemental,
									flag_emp,
									confident,
									ug_gr
								) 
								VALUES 
								(now(),
								 ".$person->pidm.", 
								 '".$person->id."', 
								 '', 
								 '".addslashes($person->first_name)."',
								 '".addslashes(empty($person->middle_name) ? '' : substr($person->middle_name, 0, 1))."',
								 '".addslashes($person->last_name)."',
								 '".$person->username."', 
								 '".$person->classification."',
								 '".addslashes($person->title)."',
								 '".addslashes($person->department)."',
								 '".$person->msc."',
								 '".addslashes($person->building)."',
								 '".addslashes($person->room)."',
								 '".$person->of_phone."',
								 '".$person->data['flags']['pat']."',
								 '".$person->data['flags']['os']."',
								 '".$person->data['flags']['faculty']."',
								 '".$person->data['flags']['pa']."',
								 '".$person->data['flags']['supervisor']."',
								 '".$person->data['flags']['lecturer']."',
								 '".$person->data['flags']['dept_contact']."',
								 '".$person->data['flags']['chair']."',
								 '".$person->data['flags']['hourly']."',
								 '".$person->data['flags']['emp']."',
								 '".$person->confidential."',
								 '".$person->ug_gr."')";
			break;
			case 'insert_student':
				$sql = "INSERT INTO DATA_MART_BUILD 
								(timeval,
								pidm,
								id,
								ssn,
								first_name,
								middle_name,
								last_name,
								user_name,
								stu_ma_address1,
								stu_ma_address2,
								stu_ma_address3,
								stu_ma_city,
								stu_ma_state,
								stu_ma_zip,
								stu_ma_natn_code,
								stu_ma_phone_area,
								stu_ma_phone_number,
								stu_ca_address1,
								stu_ca_address2,
								stu_ca_address3,
								stu_ca_city,
								stu_ca_state,
								stu_ca_zip,
								stu_ca_phone_area,
								stu_ca_phone_number,
								stu_lo_address1,
								stu_lo_address2,
								stu_lo_address3,
								stu_lo_phone_number,
								stu_lo_atyp_code,
								stu_class_code,
								stu_major,
								flag_student,
								confident,
								ug_gr,
								degree,
								program) 
								VALUES (now(),
								".$person->pidm.",
								'".$person->id."',
								'',
								'".addslashes($person->first_name)."',
								'".addslashes(empty($person->middle_name) ? '' : substr($person->middle_name, 0, 1))."',
								'".addslashes($person->last_name)."',
								'".$person->username."',
								'".addslashes($person->info['ma_address1'])."',
								'".addslashes($person->info['ma_address2'])."',
								'".addslashes($person->info['ma_address3'])."',
								'".addslashes($person->info['ma_city'])."',
								'".$person->info['ma_state']."',
								'".$person->info['ma_zip']."',
								'".$person->info['ma_natn_code']."',
								'".$person->info['ma_phone_area']."',
								'".$person->info['ma_phone_number']."',
								'".addslashes($person->info['ca_address1'])."',
								'".addslashes($person->info['ca_address2'])."',
								'".addslashes($person->info['ca_address3'])."',
								'".addslashes($person->info['ca_city'])."',
								'".$person->info['ca_state']."',
								'".$person->info['ca_zip']."',
								'".$person->info['vm_phone_area']."',
								'".$person->info['vm_phone_number']."',
								'".addslashes($person->info['lo_address1'])."',
								'".addslashes($person->info['lo_address2'])."',
								'".addslashes($person->info['lo_address3'])."',
								'".$person->info['lo_phone']."',
								'".$person->info['lo_atyp_code']."',
								'".$person->info['class_code']."',
								'".addslashes($person->info['major'])."',
								'".$person->data['flags']['student']."',
								'".$person->info['confident']."',
								'".$person->info['ug_gr']."',
								'".$person->info['degree']."',
								 '".$person->info['program']."')";
			break;
			case 'update_employee':
					$sql = "UPDATE DATA_MART_BUILD SET
									timeval=now(),
									id='".$person->id."',
									ssn='',
									first_name='".addslashes($person->first_name)."',
									middle_name='".addslashes(empty($person->middle_name) ? '' : substr($person->middle_name, 0, 1))."',
									last_name='".addslashes($person->last_name)."',
									user_name='".$person->username."',
									emp_type='".$person->classification."',
									emp_title='".addslashes($person->title)."',
									emp_dept='".addslashes($person->department)."',
									emp_msc='".$person->msc."',
									emp_building='".addslashes($person->building)."',
									emp_room='".addslashes($person->room)."',
									emp_phone='".$person->of_phone."',
									flag_pat='".$person->data['flags']['pat']."',
									flag_os='".$person->data['flags']['os']."',
									flag_faculty='".$person->data['flags']['faculty']."',
									flag_pa='".$person->data['flags']['pa']."',
									flag_supervisor='".$person->data['flags']['supervisor']."',
									flag_lecturer='".$person->data['flags']['lecturer']."',
									flag_dept_contact='".$person->data['flags']['debt_contact']."',
									flag_dept_chair='".$person->data['flags']['chair']."',
									flag_supplemental='".$person->data['flags']['hourly']."',
									flag_emp='".$person->data['flags']['emp']."'
									WHERE pidm=".$person->pidm;
			break;
			case 'update_student':
				$sql="UPDATE DATA_MART_BUILD SET 	
								timeval=now(),
								id='".$person->id."',
								ssn='',
								first_name='".addslashes($person->first_name)."',
								middle_name='".addslashes(empty($person->middle_name) ? '' : substr($person->middle_name, 0, 1))."',
								last_name='".addslashes($person->last_name)."',
								user_name='".$person->username."',
								stu_ma_address1='".addslashes($person->info['ma_address1'])."',
								stu_ma_address2='".addslashes($person->info['ma_address2'])."',
								stu_ma_address3='".addslashes($person->info['ma_address3'])."',
								stu_ma_city='".addslashes($person->info['ma_city'])."',
								stu_ma_state='".$person->info['ma_state']."',
								stu_ma_zip='".$person->info['ma_zip']."',
								stu_ma_natn_code='".$person->info['ma_natn_code']."',
								stu_ma_phone_area='".$person->info['ma_phone_area']."',
								stu_ma_phone_number='".$person->info['ma_phone_number']."',
								stu_ca_address1='".addslashes($person->info['ca_address1'])."',
								stu_ca_address2='".addslashes($person->info['ca_address2'])."',
								stu_ca_address3='".addslashes($person->info['ca_address3'])."',
								stu_ca_city='".addslashes($person->info['ca_city'])."',
								stu_ca_state='".$person->info['ca_state']."',
								stu_ca_zip='".$person->info['ca_zip']."',
								stu_ca_phone_area='".$person->info['vm_phone_area']."',
								stu_ca_phone_number='".$person->info['vm_phone_number']."',
								stu_lo_address1='".addslashes($person->info['lo_address1'])."',
								stu_lo_address2='".addslashes($person->info['lo_address2'])."',
								stu_lo_address3='".addslashes($person->info['lo_address3'])."',
								stu_lo_phone_number='".$person->info['lo_phone']."',
								stu_lo_atyp_code='".$person->info['lo_atyp_code']."',
								stu_class_code='".$person->info['class_code']."',
								stu_major='".addslashes($person->info['major'])."',
								flag_student='".$person->data['flags']['student']."',
								confident='".$person->info['confident']."',
								ug_gr='".$person->info['ug_gr']."' ,
								degree = '".$person->info['degree']."',
								program = '".$person->info['program']."'
								WHERE pidm=".$person->pidm;
			break;
		}//end switch
		return $sql;
	}//end getSQL

	/**
	 * migrateBuild
	 *
	 * truncates DATA_MART and inserts the contents of DATA_MART_BUILD into
	 *   DATA_MART
	 *
	 * @since		version 1.0.0
	 * @return		boolean
	 */
	function migrateBuild()
	{
		if($this->debug)
		{
			echo $this->line.":: (DEBUG) Truncating DATA_MART Table".$this->line;
			echo $this->line.":: (DEBUG) Copying DATA_MART_BUILD records to DATA_MART Table".$this->line;
		}//end if
		else
		{
			echo $this->line.":: Truncating DATA_MART Table".$this->line;
			if($this->datamart->Execute("TRUNCATE TABLE DATA_MART"))
			{
				echo ":: Copying DATA_MART_BUILD records to DATA_MART Table".$this->line;
				if(!$this->datamart->Execute("INSERT INTO DATA_MART (SELECT * FROM DATA_MART_BUILD)"))
				{
					echo '::'.$this->error.' Copy DATA_MART_BUILD records to DATA_MART Table'.$this->line;
				}//end if
			}//end if
			else
			{
				echo '::'.$this->error.' Failed to truncate DATA_MART Table'.$this->line;
			}//end else
		}//end else		
	}//end migrateBuild

	/**
	 * testConnections
	 *
	 * truncate MySQL DATAMART table
	 *
	 * @since		version 1.0.0
	 * @return		boolean
	 */
	function testConnections()
	{
		echo $this->line.":: Testing Database connections with DATA_MART, BANNER, and PLUTO".$this->line;
		
		$banner_connect = $this->banner->IsConnected();
		$datamart_connect = $this->datamart->IsConnected();
		$userdb_connect = $this->userdb->IsConnected();
		
		if($banner_connect && $datamart_connect && $userdb_connect)
		{
			echo ":: Connections successful!".$this->line;
			return true;
		}//end if
		else
		{
			$fail = array();
			if(!$banner_connect) $fail[] = 'BANNER';
			if(!$datamart_connect) $fail[] = 'DATA_MART';
			if(!$userdb_connect) $fail[] = 'USER_DB';
			
			echo "::".$this->error." Connections FAILED for: ".implode(', ',$fail).$this->line;
			return false;
		}//end else
	}//end testConnections

	/**
	 * truncateBuild
	 *
	 * truncate MySQL DATAMART table
	 *
	 * @since		version 1.0.0
	 * @return		boolean
	 */
	function truncateBuild()
	{
		echo $this->line.":: ".$this->msg."Truncating DATA_MART_BUILD Table".$this->line;
		
		if(!$this->debug)
		{
			return $GLOBALS['DATA_MART']->Execute("TRUNCATE TABLE DATA_MART_BUILD");
		}//end if
		else
		{
			return true;
		}//end else
		return false;
	}//end truncateBuild
	
	/**
	 * updateDatamart
	 *
	 * update the data_mart table depending on what type of person is being passed.
	 *   Depending on what is needed, different queries will be retrieved/executed.
	 *
	 * @since		version 1.0.0
	 * @param			array $person Person data
	 * @param			boolean $update Is an update needed? true = update, false = insert
	 * @param			boolean $student Is this a student?
	 */
	function updateDatamart(&$person,$update=true,$student=false)
	{
		if($this->existsInDatamart($person->pidm))
		{
			if($update)
			{
				if($student)
				{
					$sql = $this->getSQL($person,'update_student');
				}//end if
				else
				{
					$sql = $this->getSQL($person,'update_employee');
				}//end else
				
				if($this->debug)
				{
					echo $this->line.':: (DEBUG) DATA_MART update for (PIDM: '.$person->pidm.'): '.$sql.$this->line;
				}//end if
				else
				{
					if(!$this->datamart->Execute($sql))
					{
						echo $this->line.'::'.$this->error.' Unable to update record in DATA_MART for (PIDM: '.$person->pidm.')'.$this->line;
						echo '::'.$this->error.' ['.$this->datamart->ErrorMsg().']'.$this->line;
						echo '::'.$this->error.' '.$sql.$this->line;
					}//end if
				}//end if
			}//end if
		}//end if
		elseif($person->username)
		{
			if($student)
			{
				$sql = $this->getSQL($person,'insert_student');
			}//end if
			else
			{
				$sql = $this->getSQL($person,'insert_employee');
			}//end else

			if($this->debug)
			{
				echo $this->line.':: (DEBUG) DATA_MART insert for (PIDM: '.$person->pidm.'): '.$sql.$this->line;
			}//end if
			else
			{
				if(!$this->datamart->Execute($sql))
				{
					echo $this->line.'::'.$this->error.' Unable to insert record into DATA_MART for (PIDM: '.$person->pidm.')'.$this->line;
					echo '::'.$this->error.' ['.$this->datamart->ErrorMsg().']'.$this->line;
					echo '::'.$this->error.' '.$sql.$this->line;
				}//end if
			}//end else
		}//end elseif
		else
		{
			echo $this->line.'::'.$this->error.' NO USERNAME FOR: '.$person->pidm.$this->line;
		}//end else
	}//end updateDatamart

	/**
	 * updateEmployees
	 *
	 * loops over employees generated by generatePopulations() and
	 *   modifies/updates those people within the DATA_MART
	 *
	 * @since		version 1.0.0
	 */
	function updateEmployees($emeritus = false)
	{
		$person_type = $emeritus ? 'Emeriti' : 'Employees';
		echo $this->line.":: Updating $person_type (count: ".$this->count['emps'].")".$this->line;
		$i=0;
		$people = $emeritus ? $this->emeritus : $this->emps;
		foreach($people as $row)
		{
			$person = new PSUPerson($row['pidm']);
			if($person->pidm)
			{
				$person->_load_address();
				$person->_load_phone();

				$classifications = $this->idm->getPersonAttributesByMeta($person->pidm, 'classification');
				
				$person->classification = $emeritus ? 'RM' : key($classifications['role']);
				$person->_load_ssn('//');
				
				$person->attributes = $this->idm->getPersonAttributes($person->pidm);

				$person->data['flags'] = array();
				$person->data['flags']['emp'] = (isset($person->attributes['permission']['employee_list'])) ? 1 : '';
				$person->data['flags']['pat'] = (isset($person->attributes['permission']['pat_list'])) ? 1 : '';
				$person->data['flags']['os'] = (isset($person->attributes['permission']['os_list'])) ? 1 : '';
				$person->data['flags']['faculty'] = (isset($person->attributes['permission']['faculty_list'])) ? 1 : '';
				$person->data['flags']['pa'] = (isset($person->attributes['permission']['pa_list'])) ? 1 : '';
				$person->data['flags']['lecturer'] = (isset($person->attributes['permission']['lecturer_list'])) ? 1 : '';
				$person->data['flags']['hourly'] = (isset($person->attributes['permission']['hourly_list'])) ? 1 : '';
				$person->data['flags']['supervisor'] = (isset($person->attributes['permission']['supervisor_list'])) ? 1 : '';
				$person->data['flags']['chair'] = (isset($person->attributes['permission']['chair_list'])) ? 1 : '';
				$person->data['flags']['dept_contact'] = (isset($person->attributes['permission']['dept_contact_list'])) ? 1 : '';

				$person->department = @key($person->attributes['department']);
				$person->title = @key($person->attributes['display_title']);

				$of_address = $this->banner_general->getAddress($person->pidm, 'OF');
				$ca_address = $this->banner_general->getAddress($person->pidm, 'CA');

				$of_phone = $this->banner_general->getPhone($person->pidm, 'OF');

				$of_address = @current($of_address);
				$ca_address = @current($ca_address);
				$of_phone = @current($of_phone);

				$of_building_room = explode('Rm', $of_address['r_street_line2']);
				
				$person->msc = addslashes(trim($ca_address['r_street_line1']));
				$person->building = addslashes(trim($of_building_room[0]));
				$person->room = addslashes(trim($of_building_room[1]));
				
				$person->of_phone = preg_replace('/^535/', '', $of_phone['r_phone_number']);
				
				$person->ug_gr = 'NA';
				$person->confidential = 'NA';
				
				if($emeritus) $this->createTempRecord($person, 'ALUMNI');
				else $this->createTempRecord($person,'EMP');
				$this->updateDatamart($person);
			}//end if

			unset($person);
			$person = null;

			$i++;

			if( $i % 10 == 9 ) {
				gc_collect_cycles();
			}//end if
		}//end foreach
		
		echo $this->line.":: Finished Updating $person_type".$this->line;
	}//end updateEmployees

	/**
	 * updateNonEmployees
	 *
	 * loops over non-employees generated by generatePopulations() and
	 *   modifies/updates those people within the DATA_MART
	 *
	 * @since		version 1.0.0
	 */
	function updateNonEmployees()
	{
		echo $this->line.":: Updating Non-Employees (count: ".$this->count['non_emps'].")".$this->line;
		
		$i = 0;
		foreach($this->non_emps as $row) {
			$person = new PSUPerson($row['pidm']);
			if($person->pidm)
			{
				$classifications = $this->idm->getPersonAttributesByMeta($person->pidm, 'classification');
				
				$person->classification = key($classifications['role']);
				$person->_load_ssn('//');
				
				$person->attributes = $this->idm->getPersonAttributes($person->pidm);
				$person->data['flags'] = array();
				$person->data['flags']['emp'] = (isset($person->attributes['permission']['employee_list'])) ? 1 : '';
				$person->data['flags']['pat'] = (isset($person->attributes['permission']['pat_list'])) ? 1 : '';
				$person->data['flags']['os'] = (isset($person->attributes['permission']['os_list'])) ? 1 : '';
				$person->data['flags']['faculty'] = (isset($person->attributes['permission']['faculty_list'])) ? 1 : '';
				$person->data['flags']['pa'] = (isset($person->attributes['permission']['pa_list'])) ? 1 : '';
				$person->data['flags']['lecturer'] = (isset($person->attributes['permission']['lecturer_list'])) ? 1 : '';
				$person->data['flags']['hourly'] = (isset($person->attributes['permission']['hourly_list'])) ? 1 : '';
				$person->data['flags']['supervisor'] = (isset($person->attributes['permission']['supervisor_list'])) ? 1 : '';
				$person->data['flags']['chair'] = (isset($person->attributes['permission']['chair_list'])) ? 1 : '';
				$person->data['flags']['dept_contact'] = (isset($person->attributes['permission']['dept_contact_list'])) ? 1 : '';
		
				$this->createTempRecord($person, 'NON-EMP');
				
				$this->updateDatamart($person,false); //false == don't update datamart
			}//end if
			unset($person);
			$person = null;

			$i++;

			if( $i % 10 == 9 ) {
				gc_collect_cycles();
			}//end if
		}//end foreach
		
		echo $this->line.":: Finished Updating Non-Employees".$this->line;
	}//end updateNonEmployees

	/**
	 * updateStudents
	 *
	 * loops over students within student demographics and
	 *   modifies/updates those people within the DATA_MART
	 * -----------------------------------------------------
	 * The student feed.  This feed is for the datamart only, student accounts
	 * (status of their creation) is determined in accounts.php.  This
	 * process of determining which students get created is much more complex
	 * and time sensitive so it was broken out into a separate process.
	 * -----------------------------------------------------
	 *
	 * @since		version 1.0.0
	 */
	function updateStudents()
	{
		 echo $this->line.":: Updating Students (count: ".$this->count['students'].")".$this->line;

		 $sql = "SELECT	pidm,
						ssn,
						id,
						first_name,
						middle_name,
						last_name,
						ca_email,
						ma_address1,
						ma_address2,
						ma_address3,
						ma_city,
						ma_state,
						ma_zip,
						ma_natn_code,
						ma_phone_area,
						ma_phone_number,
						ca_address1,
						ca_address2,
						ca_address3,
						ca_city,
						ca_state,
						ca_zip,
						vm_phone_area,
						vm_phone_number,
						lo_address1,
						lo_address2,
						lo_address3,
						lo_phone_number,
						lo_atyp_code,
						class_code,
						major,
						DECODE(confident,null,'N','N','N','Y') confident,
						ug_gr,
						gobtpac_external_user r_username,
						degree,
						program
				 FROM ps_as_student_demographics,gobtpac 
				WHERE pidm=gobtpac_pidm 
					AND NOT EXISTS (SELECT 1 
									 FROM sgbstdn 
									WHERE sgbstdn_styp_code IN('X') 
										AND sgbstdn_pidm=pidm 
										AND sgbstdn_term_code_eff = (SELECT MAX(sgbstdn_term_code_eff) FROM sgbstdn WHERE sgbstdn_pidm = pidm))";
		if($results=$this->banner->Execute($sql))
		{
			while($row=$results->FetchRow())
			{
				$person = new stdClass;
				$person->pidm = $row['pidm'];
				$person->id = $row['id'];
				$person->first_name = $row['first_name'];
				$person->middle_name = $row['middle_name'];
				$person->last_name = $row['last_name'];
				$person->username = $row['r_username'];
				$person->info = array();
				$person->info = $row;
				
				$person->data['flags']['student'] = 1;
							
				$this->updateDatamart($person,true,true);

				unset($person);
			}//end while
			
			echo $this->line.":: Finished Updating Students".$this->line;
		}//end if
	}//end updateStudents

	/**
	 * verifyCounts
	 *
	 * generate counts and make sure the they are reasonable
	 *
	 * @since		version 1.0.0
	 * @return		boolean
	 */
	function verifyCounts()
	{
		//generate counts
		$this->count = array();
		$this->count['status'] = 0;
		$this->count['emps'] = count($this->emps);
		$this->count['non_emps'] = count($this->non_emps);
		$this->count['students'] = $this->banner->GetOne("SELECT count(*) FROM ps_as_student_demographics,gobtpac WHERE pidm=gobtpac_pidm AND NOT EXISTS (SELECT 1 FROM sgbstdn WHERE sgbstdn_styp_code IN('X') AND sgbstdn_pidm=pidm AND sgbstdn_term_code_eff = (SELECT MAX(sgbstdn_term_code_eff) FROM sgbstdn  WHERE sgbstdn_pidm = pidm))");

		//output some stuff
		echo ':: Employee record count: '.$this->count['emps'].$this->line;
		echo ':: Non Employee record count: '.$this->count['non_emps'].$this->line;
		echo ':: Student record count: '.$this->count['students'].$this->line;

		if(($this->count['emps']+$this->count['non_emps'])<900)
		{
			echo $this->line."::".$this->error." number of employee/non-employee records returned by Banner is fewer than expected (count: ".$this->count['emps'].")".$this->line;
			
			if($this->count['emps']==0)
			{
				echo '::'.$this->error.' Employee list is empty!'.$this->line;
			}//end if
			
			if($this->count['non_emps']==0)
			{
				echo '::'.$this->error.' Non Employee list is empty!'.$this->line;
			}//end if
			
			return false;
		}//end if
		elseif($this->count['students']<3000)
		{
			echo $this->line."::".$this->error." number of student records returned by Banner is fewer expected (count: ".$this->count['students'].")".$this->line;
		}//end elseif
		else
		{
			return true;
		}//end else
	}//end verifyCounts
	
	function webFooter()
	{
		echo '</body></html>';
	}//end webFooter
	
	function webHeader()
	{
		echo '<html><head><title>Account Datamart Update</title>
		<style>
			body{font-size: 90%;}
			.error{color:red;}
		</style>
		</head>
		<body>';
	}//end webHeader
}//end class accounts

?>
