<?php 
/**
 * IDMObject.php
 *
 * === Modification History ===<br/>
 * 1.0.0  18-may-2005  [mtb]  original<br/>
 * 1.0.1  10-Oct-2006  [zbt]  fixed a bug with assignPermission
 *
 * @package 		IdentityManagement
 */

/**
 * IDMObject.php
 *
 * Functions related to Identity Management
 *
 * @version		1.0.0
 * @module		IDMObject.php
 * @author		Matthew Batchelder <mtbatchelder@plymouth.edu>
 * @copyright 2005, Plymouth State University, ITS
 */ 
require_once('PSUTools.class.php');

if(!class_exists('IDMObject'))
{
class IDMObject extends PSUTools
{
	var $_ADOdb;
	var $_config_file='idm.inc.php';
	var $_identifiers_view='psu_identity.v_person_identifiers';
	var $_idm_tables=array(
		'role'              =>'psu_identity.idm_role',
		'permission'        =>'psu_identity.idm_permission',
		'role_permission'   =>'psu_identity.idm_role_permission',
		'person_role'       =>'psu_identity.idm_person_role',
		'person_permission' =>'psu_identity.idm_person_permission',
		'person'						=>'psu_identity.person_identifiers'
	);

	/**
	 * IDMObject
	 *
	 * IDMObject constructor with db connection
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $db_hostname database hostname
	 * @param  		string $db_user database user
	 * @param  		string $db_pass database password
	 * @param  		string $db_name database name
	 * @param  		string $db_debug ADOdb debug setting default=true
	 * @param  		string $db_connection_type database connection type default=oci8
	 * @param  		string $db_fetch_type ADOdb fetch type default=ADODB_FETCH_ASSOC
	 * @return  	boolean
	 */
	function IDMObject($adodb='')
	{
		PSUTools::logOldCode('/includes_psu/IDMObject.php');
		if($adodb)
		{
			$this->_ADOdb=$adodb;
			return true;
		}//end if
		else
		{
			return $this->connect();
		}//end else
	}//end APEObject constructor

	/**
	 * getActiveTerms
	 *
	 * returns an array of active LDI terms
	 *
	 * @since		version 1.0.0
	 * @access	public
	 * @return	array
	 */
	function getActiveTerms()
	{
		$data = array();
		$sql = "begin :cursorvar := gb_integ_config.f_query_all(p_sqpr_code => :sqpr_code,p_icsn_code => :icsn_code); end;";
		if($results = $this->_ADOdb->ExecuteCursor($sql, 'cursorvar', array('sqpr_code'=>'ELEARNING','icsn_code'=>'ACTIVE_TERM')))
		{
			while($row = $results->FetchRow())
			{
				$row = PSUTools::cleanKeys('goriccr_','r_',$row);
				
				$data[] = $row['r_value'];
			}//end while
		}//end if
		
		return $data;
	}//end getActiveTerms

	/**
	 * addRole
	 *
	 * Adds a role to a user
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $user_id Person key id
	 * @param  		string $role_id Role id
	 * @return  	string
	 */
	function addRole($user_id,$role_id,$manual=0,$begin_date='',$end_date='')
	{
		return $this->_ADOdb->Execute("INSERT INTO {$this->_idm_tables['person_role']} (pid,role_id,manual_flag,begin_date,end_date,activity_date) VALUES ('$user_id','$role_id','$manual',".(($begin_date)?"('$begin_date')":"NULL").",".(($end_date)?"('$end_date')":"NULL").",sysdate)");
	}//end addRole

	/**
	 * ADOdbGetRow
	 *
	 * Call ADOdb GetRow and lowercase the array keys
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $query
	 * @return  	array
	 */
	function ADOdbGetRow($query)
	{		
		$row=$this->_ADOdb->GetRow($query);
		if(is_array($row))
		{
			$row=array_change_key_case($row,CASE_LOWER);
		}//end if

		return $row;
	}//end ADOdbGetRow

	/**
	 * assignPermission
	 *
	 * Assign a permission to a user
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $user_id Person key id
	 * @param  		string $permission_id Permission id
	 * @return  	array
	 */
	function assignPermission($user_id, $permission_id, $begin_date='NULL', $end_date='NULL', $manual=0, $role_id='1')
	{
		if(!$this->hasPermission($user_id,$permission_id))
		{
			if($begin_date!='NULL')
			{
				$fields=",begin_date";
				$values=",'$begin_date'";
			}//end if

			if($end_date!='NULL')
			{
				$fields.=",end_date";
				$values.=",'$end_date'";
			}//end if

			if(!is_numeric($permission_id))
			{
				$permission=$this->getRoleData($permission_id,'permission_code');
				$permission_id=$permission['permission_id'];
			}//end if
			
			$this->_ADOdb->Execute("INSERT INTO {$this->_idm_tables['person_permission']} (pid, role_id, permission_id, manual_flag, activity_date$fields) VALUES ('$user_id','$role_id', $permission_id, $manual, sysdate$values)");
		}//end if
	}//end assignPermission

	/**
	 * assignRole
	 *
	 * Assign a role to a user
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $user_id Person key id
	 * @param  		string $role_id role id
	 * @return  	array
	 */
	function assignRole($user_id,$role_id,$begin_date='NULL',$end_date='NULL',$manual=0)
	{	
		if(!$this->hasRole($user_id,$role_id))
		{
			if($begin_date!='NULL')
			{
				$fields=",begin_date";
				$values=",'$begin_date'";
			}//end if

			if($end_date!='NULL')
			{
				$fields.=",end_date";
				$values.=",'$end_date'";
			}//end if

			if(!is_numeric($role_id))
			{
				$role=$this->getRoleData($role_id,'role_code');
				$role_id=$role['role_id'];
			}//end if

			$query="INSERT INTO {$this->_idm_tables['person_role']} (pid,role_id,manual_flag,activity_date$fields) VALUES ($user_id,$role_id,$manual,sysdate$values)";

			if($this->_ADOdb->Execute($query))
			{
				$this->synchUser($user_id);
			}//end if
		}//end if
	}//end assignRole

	/**
	 * connect
	 *
	 * ADOdb connection
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $connection connection string
	 * @return  	boolean
	 */
	function connect($connection='oracle/psc1_psu')
	{
		require_once('PSUDatabase.class.php');
		
		$this->_ADOdb = PSUDatabase::connect($connection);
		return $this->_ADOdb->IsConnected();
	}//end connect

	function debugLDI($username)
	{
		return $this->_ADOdb->GetOne("SELECT nvl(psu.f_debug_ldi('$username'),'ZOMG No Record Found...Sure this is a valid username, n00b?') FROM dual");
	}//end debugLDI

	/**
	 * disconnect
	 *
	 * ADOdb object destructor
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		null
	 * @return  	boolean
	 */
	function disconnect()
	{
		unset($this->_ADOdb);
		return true;
	}//end disconnect

	/**
	 * countUsers
	 *
	 * Users count based on search criteria
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $criteria Search criteria
	 * @param  		string $field Search field
	 * @return  	int
	 */
	function countUsers($criteria,$field)
	{	
		//retrieve user count
		return $this->_ADOdb->GetOne("SELECT count(*) FROM {$this->_idm_tables['person']} WHERE $field LIKE '$criteria'");
	}//end countUsers

	/**
	 * countUsersByRole
	 *
	 * Users count based on role
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $role_id Role Id
	 * @return  	int
	 */
	function countUsersByRole($role_id)
	{	
		//retrieve user count
		return $this->_ADOdb->GetOne("SELECT count(*) FROM {$this->_idm_tables['person_role']} WHERE role_id=$role_id");
	}//end countUsersByRole

	/**
	 * findUsers
	 *
	 * Search for users
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $criteria Search criteria
	 * @param  		string $field Search field
	 * @param  		string $start_at_row Row to start pulling from
	 * @param  		string $end_at_row Row to stop pulling from
	 * @return  	array
	 */
	function findUsers($criteria,$field,$start_at_row='',$end_at_row='')
	{	
		$users=array();

		//retrieve permissions from table
		$query="SELECT pid, 
		               psu_id, 
									 ssn, 
									 username, 
									 sourced_id, 
									 first_name, 
									 middle_name, 
									 last_name, 
									 name_prefix, 
									 name_suffix 
							FROM {$this->_idm_tables['person']} WHERE $field LIKE '$criteria' ORDER BY last_name,first_name,middle_name";

		if($start_at_row && $end_at_row)
		{
			$query="SELECT pid, 
										 psu_id, 
										 ssn, 
										 username, 
										 sourced_id, 
										 first_name, 
										 middle_name, 
										 last_name, 
										 name_prefix, 
										 name_suffix,
										 RowNum rn 
								FROM ($query) WHERE RowNum <= $end_at_row";
			$query="SELECT * FROM ($query) WHERE rn >= $start_at_row";
		}//end if
		elseif($start_at_row)
		{
			$query="SELECT * FROM ($query) WHERE RowNum>=$start_at_row";
		}//end if
		elseif($end_at_row)
		{
			$query="SELECT * FROM ($query) WHERE RowNum<=$end_at_row";
		}//end if

		if($result=$this->_ADOdb->Execute($query))
		{
			while($row=$result->FetchRow())
			{
				$row=array_change_key_case($row,CASE_LOWER);
				//add results to permissions array
				$users[]=$row;
			}//end while
		}//end if

		if(sizeof($users)>0)
		{
			//data found
			return $users;
		}//end if
		else
		{
			//no data found
			return false;
		}//end else	
	}//end findUsers

	/**
	 * generateUsernamesByRole
	 *
	 * Generates missing GOBTPAC_EXTERNAL_USER fields for the given intcomp role
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $role_group gorirol role group
	 * @param  		string $role gorirol role
	 * @param  		boolean $create create gobtpac record if it doesn't exist?
	 * @return  	boolean
	 */
	function generateUsernamesByRole($role_group,$role,$create=false)
	{
		$sql="
			DECLARE
				v_username VARCHAR2(30);
				v_pin VARCHAR2(6);
				s_result_out VARCHAR2(20);
			
				CURSOR c_null_usernames IS
						SELECT gorirol.* 
						  FROM gorirol 
						 WHERE gorirol_role_group='".$role_group."'
						   AND gorirol_role='".$role."' 
						   AND not exists(SELECT 1 FROM gobtpac WHERE gobtpac_pidm = gorirol_pidm AND gobtpac_external_user IS NOT NULL);
			BEGIN
			
				FOR person IN c_null_usernames LOOP
					v_username := NULL;
					
					v_username := lower(goktpty.f_generate_external_user(person.gorirol_pidm));
					IF(gb_third_party_access.f_exists(p_pidm => person.gorirol_pidm) = 'Y')
					THEN
						IF(v_username IS NOT NULL)
						THEN
							gb_third_party_access.p_update(p_pidm => person.gorirol_pidm, p_external_user => v_username);
						END IF;
					ELSE ";
		
		if($create)
		{
			$sql .="goktpty.p_insert_gobtpac(person.gorirol_pidm,s_result_out);";
		}//end if
		else
		{
			$sql .="NULL; ";
		}//end else
		
		$sql .="
					END IF;
				END LOOP;
				gb_common.p_commit();
			END;";
		$stmt=$this->_ADOdb->PrepareSP($sql);
		return $this->_ADOdb->Execute($stmt);
	}//end generateUsernamesByRole

	/**
	 * getAll
	 *
	 * Retrieve all permissions
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $table Table
	 * @return  	array
	 */
	function getAll($table)
	{	
		$data=array();

		//retrieve permissions from table
		if($result=$this->_ADOdb->Execute("SELECT * FROM {$this->_idm_tables[$table]} ORDER BY {$table}_name"))
		{
			while($row=$result->FetchRow())
			{
				
				$row=array_change_key_case($row,CASE_LOWER);
				//add results to permissions array
				$data[]=$row;
			}//end while
		}//end if

		if(sizeof($data)>0)
		{
			//data found
			return $data;
		}//end if
		else
		{
			//no data found
			return false;
		}//end else	
	}//end getAll

	function getAllBannerRoles($pidm)
	{
		$sql="
		DECLARE 
			roles gokisql.rule_tabtype; 
			v_roles varchar2(500);
		BEGIN
			roles := gokisql.f_get_roles('INTCOMP',:pidm);
			v_roles:='';
			FOR role_index in 1 .. nvl (roles.LAST, 0) LOOP
				v_roles:=v_roles||'|'||roles(role_index);
			END LOOP;
			:roles:=v_roles;
		END;";
		$stmt=$this->_ADOdb->PrepareSP($sql);
		$this->_ADOdb->InParameter($stmt,$pidm,'pidm');
		$this->_ADOdb->OutParameter($stmt,$roles,'roles');
		if($this->_ADOdb->Execute($stmt))
		{	
			$roles=explode('|',strtolower($roles));
			if(!$roles[0]) unset($roles[0]);
			return $roles;
		}//end if
		return false;
	}//end getAllBannerRoles

	/**
	 * getAllPermissions
	 *
	 * Retrieve all permissions
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @return  	array
	 */
	function getAllPermissions()
	{	
		return $this->getAll('permission');
	}//end getAllPermissions

	/**
	 * getAllRoles
	 *
	 * Retrieve all roles
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @return  	array
	 */
	function getAllRoles()
	{	
		return $this->getAll('role');
	}//end getAllRoles

	function getAllUserIdentifiers($input_id,$input_type)
	{	
		return PSUTools::cleanKeys('','',$this->_ADOdb->GetRow("SELECT * FROM {$this->_idm_tables['person']} WHERE $input_type='$input_id'"));
	}//end getAllUserIdentifiers

	/**
	 * getIdentifier
	 *
	 * Retrieve a user identifier by sending in a different identifier
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $input_id Input identifier
	 * @param  		string $input_type Input identifier type
	 * @param  		string $output_type Output identifier type
	 * @return  	string or array
	 */
	function getIdentifier($input_id,$input_type,$output_type,$table_query=true)
	{
		/*
			types:
				pid
				psu_id
				username
				login_name
				sourced_id
				ssn (should not be used)

			Note: 
				pid is pidm
				psu_id is the id# on id cards
		*/
		if($input_type=='id') $input_type='psu_id';
		if($input_type=='pidm') $input_type='pid';
		if($output_type=='id') $output_type='psu_id';
		if($output_type=='pidm') $output_type='pid';

		$view = ($table_query)?$this->_idm_tables['person']:$this->_identifiers_view;

		if($output_type=='all' || $output_type=='*')
		{
			$output_type='*';
			$output_id=$this->ADOdbGetRow("SELECT $output_type FROM {$view} WHERE $input_type='$input_id'");
		}//end if
		else
		{
			$output_id=$this->_ADOdb->GetOne("SELECT $output_type FROM {$view} WHERE $input_type='$input_id'");
		}//end else

		return $output_id;
	}//end getIdentifier

	/**
	 * getName
	 *
	 * Retrieve the formatted name of a user
	 *   f=first
	 *   m=middle
	 *   i=middle initial
	 *   l=last
	 *   p=prefix
	 *   s=suffix
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $input_id Input identifier
	 * @param  		string $name_format Name format 
	 * @return  	string
	 */
	function getName($pid,$name_format='l, f m')
	{
		$name_format=strtolower($name_format);

		$name_row=$this->ADOdbGetRow("SELECT first_name,middle_name,last_name,name_prefix,name_suffix FROM {$this->_idm_tables['person']} WHERE pid='$pid'");

		//assign name information to name codes
		$name=array(
			'f'=>$name_row['first_name'],
			'm'=>$name_row['middle_name'],
			'i'=>substr($name_row['middle_name'],0,1),
			'l'=>$name_row['last_name'],
			'p'=>$name_row['name_prefix'],
			's'=>$name_row['name_suffix']
		);

		//replace name codes with name parts
		$name_format=chunk_split($name_format,1,'|||');
		$name_format=explode('|||',$name_format);
		foreach($name_format as $key=>$chunk)
		{
			if(array_key_exists($chunk,$name))
			{
				$name_format[$key]=$name[$chunk];
			}//end if
		}//end foreach
		$formatted_name=implode('',$name_format);
		
		return $formatted_name;
	}//end getName

	function getNumUsersInQueue()
	{
		return $this->_ADOdb->GetOne("SELECT count(*) FROM idm_queue WHERE pid_processed is null");
	}//end getNumUsersInQueue

	/**
	 * getPermissionData
	 *
	 * Retrieve data on a permission given either:
	 *    - permission_id
	 *    - permission_desc
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $permission Permission
	 * @param  		string $permission_column Column you are using to retrieve permission
	 * @return  	string
	 */
	function getPermissionData($permission,$permission_column)
	{		
		$permission=$this->ADOdbGetRow("SELECT * FROM {$this->_idm_tables['permission']} WHERE $permission_column='$permission'");

		return $permission;
	}//end getPermissionData
	/**
	 * getRoleData
	 *
	 * Retrieve data on a role given either:
	 *    - role_id
	 *    - role_desc
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $role Role
	 * @param  		string $role_column Column you are using to retrieve role
	 * @return  	string
	 */
	function getRoleData($role,$role_column)
	{		
		$role=$this->ADOdbGetRow("SELECT * FROM {$this->_idm_tables['role']} WHERE $role_column='$role'");

		return $role;
	}//end getRoleData

	function getRolePermissions($role_id,$user_id='')
	{
		$data=array();

		$query="SELECT p.permission_id permission_id,
		               p.permission_name permission_name,
									 0 as permission_manual 
							FROM {$this->_idm_tables['role_permission']} r,
							     {$this->_idm_tables['permission']} p
						 WHERE r.permission_id=p.permission_id 
						   AND r.role_id=$role_id
						 ORDER BY p.permission_name";

		//retrieve permissions from table
		if($result=$this->_ADOdb->Execute($query))
		{
			while($row=$result->FetchRow())
			{
				$row=array_change_key_case($row,CASE_LOWER);
				//add results to permissions array
				$data[]=$row;
			}//end while
		}//end if

		if($user_id)
		{
			$query="SELECT p.permission_id permission_id,
										 p.permission_name permission_name, 
										 1 as permission_manual 
								FROM {$this->_idm_tables['permission']} p,
										 {$this->_idm_tables['person_permission']} pr
							 WHERE pr.role_id=$role_id 
								 AND pr.permission_id=p.permission_id
								 AND pr.pid=$user_id
							 ORDER BY p.permission_name";

			//retrieve permissions from table
			if($result=$this->_ADOdb->Execute($query))
			{
				while($row=$result->FetchRow())
				{
					$row=array_change_key_case($row,CASE_LOWER);
					//add results to permissions array
					$data[]=$row;
				}//end while
			}//end if
		}//end if

		return $data;
	}//end getRolePermissions

	/**
	 * getUserPermissions
	 *
	 * Retrieve a user's permissions of a given type:
	 *    - all
	 *    - manual
	 *    - auto
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $user_id Person key id
	 * @param  		string $type Permission type
	 * @return  	string
	 */
	function getUserPermissions($user_id,$type='all')
	{	
		$type=strtolower($type);
		$permissions=array();

		if($type=='all' || $type=='auto')
		{
			//retrieve permissions from role_permission table
			$query="SELECT distinct(p.permission_id) permission_id,
										 p.permission_name permission_name,
										 0 as permission_manual 
								FROM {$this->_idm_tables['permission']} p,
										 {$this->_idm_tables['person_role']} pr,
										 {$this->_idm_tables['role_permission']} r 
							 WHERE r.role_id=pr.role_id 
								 AND pr.pid='$user_id'
								 AND p.permission_id=r.permission_id";
			if($result=$this->_ADOdb->Execute($query))
			{
				while($row=$result->FetchRow())
				{
					$row=array_change_key_case($row,CASE_LOWER);
					//add results to permissions array
					$permissions[]=$row;
				}//end while
			}//end if
		}//end if
		
		if($type=='all' || $type=='manual')
		{
			//retrieve permissions from person_permission table (the manually entered permissions)
			$query="SELECT distinct(p.permission_id) permission_id,
										 p.permission_name permission_name,
										 1 as permission_manual  
								FROM {$this->_idm_tables['permission']} p,
										 {$this->_idm_tables['person_permission']} pp 
							 WHERE pp.pid='$user_id'
								 AND p.permission_id=pp.permission_id";
			if($result=$this->_ADOdb->Execute($query))
			{
				while($row=$result->FetchRow())
				{
					$row=array_change_key_case($row,CASE_LOWER);
					//if permissions do not already exist in the permissions array, add them
					if(!in_array($row,$permissions))
					{
						$permissions[]=$row;
					}//end if
				}//end while
			}//end if
		}//end if

		if(sizeof($permissions)>0)
		{
			//permissions found
			return $permissions;
		}//end if
		else
		{
			//no permissions found
			return false;
		}//end else
	
	}//end getUserPermissions

	/**
	 * getUserRoles
	 *
	 * Retrieve a user's roles
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $user_id Person key id
	 * @param  		string $type Role type
	 * @return  	string
	 */
	function getUserRoles($user_id,$type='all')
	{	
		$roles=array();

		if(strtolower($type)=='auto')
		{
			$manual_flag='AND manual_flag=0 ';
		}//end if
		elseif(strtolower($type)=='manual')
		{
			$manual_flag='AND manual_flag=1 ';
		}//end else

		if($result=$this->_ADOdb->Execute("SELECT r.role_id role_id,r.role_name role_name FROM {$this->_idm_tables['person_role']} p,{$this->_idm_tables['role']} r WHERE p.role_id=r.role_id AND pid='$user_id' $manual_flag ORDER BY r.role_name"))
		{
			while($row=$result->FetchRow())
			{
				$row=array_change_key_case($row,CASE_LOWER);
				$roles[]=$row;
			}//end while

			if(sizeof($roles)>0)
			{
				return $roles;
			}//end if
			else
			{
				return false;
			}//end else
		}//end if
		else
		{
			return false;
		}//end else		
	}//end getUserRoles

	function getUsersByBannerRole($role , $return = 'pid')
	{
		if(!is_array($role))
		{
			$role = array($role);
		}//end if
		
		$role = array_map('strtoupper',$role);
		
		switch($return)
		{
			case 'pidm':
				$return = 'pid';
			break;
			case 'ssn':
				$return = 'pid';
			break;
			case 'id':
				$return = 'psu_id';
			break;
		}//end switch
		$sql = "SELECT $return FROM gorirol,{$this->_idm_tables['person']} p WHERE gorirol_role in ('".implode("','",$role)."') AND pid=gorirol_pidm";
		$data = array();
		
		if($results = $this->_ADOdb->Execute($sql))
		{
			while($row = $results->FetchRow())
			{
				$row=array_change_key_case($row,CASE_LOWER);
				$data[]=$row[$return];
			}//end while
		}//end if
		
		return $data;
	}//end getUsersByBannerRole

	/**
	 * getUsersByPermission
	 *
	 * Retrieve all users by permission
	 *
	 * @since		version 1.1.0
	 * @access		public
	 * @param  		string $permission_id Permission id
	 * @return  	array
	 */

	function getUsersByPermission($permission_id)
	{	
		$users=array();

		//retrieve permissions from table
		$query="SELECT p.* FROM {$this->_idm_tables['person_permission']} m, {$this->_idm_tables['person']} p WHERE p.pid=m.pid AND r.role_id=$role_id ORDER BY p.last_name, p.first_name, p.middle_name";

		if($result=$this->_ADOdb->Execute($query))
		{
			while($row=$result->FetchRow())
			{
				$row=array_change_key_case($row,CASE_LOWER);
				//add results to permissions array
				$users[]=$row;
			}//end while
		}//end if

		return $users;

	}//end getUsersByPermission


	/**
	 * getUsersByRole
	 *
	 * Retrieve all users by role
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $role_id Role id
	 * @param  		string $start_at_row Row to start pulling from
	 * @param  		string $end_at_row Row to stop pulling from
	 * @return  	array
	 */
	function getUsersByRole($role_id,$start_at_row='',$end_at_row='')
	{	
		$users=array();

		//retrieve permissions from table
		$query="SELECT p.* FROM {$this->_idm_tables['person_role']} r,{$this->_idm_tables['person']} p WHERE p.pid=r.pid AND r.role_id=$role_id ORDER BY p.last_name,p.first_name,p.middle_name";

		if($start_at_row && $end_at_row)
		{
			$query="SELECT pid, 
										 psu_id, 
										 ssn, 
										 username, 
										 sourced_id, 
										 first_name, 
										 middle_name, 
										 last_name, 
										 name_prefix, 
										 name_suffix,
										 RowNum rn 
								FROM ($query) WHERE RowNum <= $end_at_row";
			$query="SELECT * FROM ($query) WHERE rn >= $start_at_row";
		}//end if
		elseif($start_at_row)
		{
			$query="SELECT * FROM ($query) WHERE RowNum>=$start_at_row";
		}//end if
		elseif($end_at_row)
		{
			$query="SELECT * FROM ($query) WHERE RowNum<=$end_at_row";
		}//end if

		if($result=$this->_ADOdb->Execute($query))
		{
			while($row=$result->FetchRow())
			{
				$row=array_change_key_case($row,CASE_LOWER);
				//add results to permissions array
				$users[]=$row;
			}//end while
		}//end if

		if(sizeof($users)>0)
		{
			//data found
			return $users;
		}//end if
		else
		{
			//no data found
			return false;
		}//end else	
	}//end getUsersByRole

	function getUsersInQueue($num=50)
	{
		$users=array();
		if($num>0)
			$query="SELECT pid FROM (SELECT pid FROM idm_queue WHERE pid_processed is null ORDER BY activity_date ASC) WHERE RowNum<=$num";
		else
			$query="SELECT pid FROM idm_queue WHERE pid_processed is null ORDER BY activity_date ASC";
		if($result=$this->_ADOdb->Execute($query))
		{
			while($row=$result->FetchRow())
			{
				$row=array_change_key_case($row,CASE_LOWER);
				//add results to permissions array
				$users[]=$row['pid'];
			}//end while
		}//end if
		return $users;
	}//end getUsersInQueue

	function hasBannerRole($pidm,$role,$roles=false)
	{
		$roles=(!$roles)?$this->getAllBannerRoles($pidm):$roles;
		if(in_array(strtolower($role),$roles))
			return true;
		else
			return false;
	}//end hasBannerRole

	/**
	 * hasPermission
	 *
	 * Does user have given permissions?
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $user_id Input identifier
	 * @param  		array $permissions Permissions (can be a string)
	 * @return  	boolean
	 */
	function hasPermission($user_id,$permissions)
	{
		//if $permissions isn't an array, make it one
		if(!is_array($permissions))
		{
			$permissions=array($permissions);
		}//end if

		//assume user has permissions
		$has_permissions=true;

		//iterate over passed in permissions
		foreach($permissions as $permission)
		{
			//get permission data
			$permission=$this->getPermissionData($permission,(is_numeric($permission))?'permission_id':'permission_code');

			//count how many times user has permission
			$permission_count=$this->_ADOdb->GetOne("SELECT 1 FROM {$this->_idm_tables['person_permission']} WHERE pid='$user_id' AND permission_id={$permission['permission_id']}");

			//count how many times user has permission
			$permission_count+=$this->_ADOdb->GetOne("SELECT 1 FROM {$this->_idm_tables['person_role']} p,{$this->_idm_tables['role_permission']} r WHERE p.pid='$user_id' AND p.role_id=r.role_id AND r.permission_id={$permission['permission_id']}");
			
			if($permission_count<=0)
			{
				//user doesn't have permission
				$has_permissions=false;
			}//end if
			unset($permission_count);
		}//end foreach

		return $has_permissions;
	}//end hasPermissions

	/**
	 * hasRole
	 *
	 * Does user have given roles?
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $user_id Input identifier
	 * @param  		array $roles Roles (can be a string)
	 * @return  	boolean
	 */
	function hasRole($user_id,$roles)
	{
		//if $roles isn't an array, make it one
		if(!is_array($roles))
		{
			$roles=array($roles);
		}//end if

		//assume user has roles
		$has_roles=true;

		//iterate over passed in roles
		foreach($roles as $role)
		{
			//get role data
			$role=$this->getRoleData($role,(is_numeric($role))?'role_id':'role_code');

			//count how many times user has role
			$role_count=$this->_ADOdb->GetOne("SELECT 1 FROM {$this->_idm_tables['person_role']} WHERE pid='$user_id' AND role_id={$role['role_id']}");
			if($role_count<=0)
			{
				//user doesn't have role
				$has_roles=false;
			}//end if
			unset($role_count);
		}//end foreach
		return $has_roles;
	}//end hasRoles
	
	function LDISync($pidm,$type='person',&$banner_object=false)
	{
		switch(strtolower($type))
		{
			case 'all':
				$p = $this->LDISyncPerson($pidm);
				$a = $this->LDISyncAssignment($pidm);
				$e = $this->LDISyncEnrollment($pidm);
				
				if($p && $a && $e)
				{
					return true;
				}//end if
				else
				{
					return false;
				}//end else
			break;
			case 'assignment':
				return $this->LDISyncAssignment($pidm,$banner_object);
			break;
			case 'enrollment':
				return $this->LDISyncEnrollment($pidm,$banner_object);
			break;
			case 'person':
				return $this->LDISyncPerson($pidm);
			break;
		}//end switch
		return false;
	}//end LDISync

	function LDISyncAssignment($pidm,&$banner_object=false)
	{
		if(!$banner_object)
		{
			require_once('BannerFaculty.class.php');
			$banner_object = new BannerFaculty($this->_ADOdb);
		}//end if
	
		if(!is_array($this->active_terms)) $this->active_terms = $this->getActiveTerms();
		
		if(count($this->active_terms)<=0) return false;
		
		foreach($this->active_terms as $term)
		{
			$schedule = $banner_object->getSchedule($pidm, $term);
				
			foreach($schedule[$term] as $course)
			{
				$sql="
					BEGIN
						icsikldi.p_save_assignment(term_code_in => '".$term."', crn_in => '".$course['r_crn']."', pidm_in => ".$pidm.", action_in => 'ENROLL');
						icsikldi.p_send_assignment();
						gb_common.p_commit();
					END;";
				$stmt=$this->_ADOdb->PrepareSP($sql);
				if(!$this->_ADOdb->Execute($stmt))
				{
					return false;
				}//end if
			}//end foreach
		}//end foreach
		
		return true;
	}//end LDISyncEnrollment

	function LDISyncEnrollment($pidm,&$banner_object=false)
	{
		if(!$banner_object)
		{
			require_once('BannerStudent.class.php');
			$banner_object = new BannerStudent($this->_ADOdb);
		}//end if
		
		if(!is_array($this->active_terms)) $this->active_terms = $this->getActiveTerms();
		
		if(count($this->active_terms)<=0) return false;
		
		foreach($this->active_terms as $term)
		{
			$schedule = $banner_object->getSchedule($pidm, $term);
				
			foreach($schedule[$term] as $course)
			{
				$sql="
					BEGIN
						icsfkldi.p_save_enrollment(term_code_in => '".$term."', crn_in => '".$course['r_crn']."', pidm_in => ".$pidm.", action_in => 'ENROLL');
						icsfkldi.p_send_enrollment();
						gb_common.p_commit();
					END;";
				$stmt=$this->_ADOdb->PrepareSP($sql);
				if(!$this->_ADOdb->Execute($stmt))
				{
					return false;
				}//end if
			}//end foreach
		}//end foreach
		
		return true;
	}//end LDISyncEnrollment
	
	function LDISyncPerson($pidm)
	{
		$sql="
			BEGIN
				psu.pkg_roles.p_sync_user(".$pidm.");
			END;";

		$stmt=$this->_ADOdb->PrepareSP($sql);
		return $this->_ADOdb->Execute($stmt);
	}//end LDISyncPerson

	function maintainBannerRoles($pidm)
	{
		$sql="
		DECLARE 
			roles gokisql.rule_tabtype; 
		BEGIN
			gb_institution_role.p_maintain_roles($pidm,'INTCOMP');
			gb_common.p_commit();
		END;";
		$stmt=$this->_ADOdb->PrepareSP($sql);
		return $this->_ADOdb->Execute($stmt);
	}//end maintainBannerRoles


	function revokeRole($user_id,$role_id)
	{
		$this->_ADOdb->Execute("DELETE FROM {$this->_idm_tables['person_permission']} WHERE pid='$user_id' AND role_id='$role_id'");
		$this->_ADOdb->Execute("DELETE FROM {$this->_idm_tables['person_role']} WHERE pid='$user_id' AND role_id='$role_id'");
		$this->synchUser($user_id);
	}//end revokeRole

	function setQueuedUserAsProcessed($pid)
	{
		$this->_ADOdb->Execute("UPDATE idm_queue SET pid_processed=1 WHERE pid=$pid");
	}//end setQueuedUserAsProcessed

	function setQueuedUsersAsUnprocessed()
	{
		$this->_ADOdb->Execute("UPDATE idm_queue SET pid_processed=null");
	}//end setQueuedUsersAsUnprocessed

	function syncAdmitPin($pid='')
	{
		if($pid)
		{
			if(!$this->_ADOdb->GetOne("SELECT count(*) FROM gobtpac WHERE gobtpac_pidm=$pid"))
			{
				$sql="INSERT INTO gobtpac (
					gobtpac_pidm,
					gobtpac_pin_disabled_ind,
					gobtpac_usage_accept_ind,
					gobtpac_activity_date,
					gobtpac_user,
					gobtpac_pin
				) VALUES (
					$pid,
					'N',
					'Y',
					sysdate,
					'IDMObject->syncAdmitPin',
					'123123'
				)";
				$this->_ADOdb->Execute($sql);
			}//end if
			
			$sql="SELECT sabnstu_pin pin
  			  FROM sabnstu,sabiden i1 
  			 WHERE sabnstu_aidm = i1.sabiden_aidm 
  			   AND i1.sabiden_pidm=$pid
  			   AND i1.sabiden_aidm = (SELECT max(i2.sabiden_aidm) FROM sabiden i2 WHERE i2.sabiden_pidm = i1.sabiden_pidm)
  			   AND gokisql.f_check_role('INTCOMP','UG_APP',sabiden_pidm)='Y'";
			
			if($row = $this->_ADOdb->GetRow($sql))
			{
			
				//$sql="UPDATE gobtpac SET gobtpac_ldap_user = '".$row['LDAP_USER']."',gobtpac_pin='".$row['PIN']."',gobtpac_pin_exp_date=add_months(sysdate,500) WHERE gobtpac_pidm=$pid";
				$sql="UPDATE gobtpac SET gobtpac_pin = (
															 SELECT sabnstu_pin
													FROM sabnstu,sabiden i1 
												 WHERE sabnstu_aidm = i1.sabiden_aidm 
													 AND i1.sabiden_pidm=gobtpac_pidm
													 AND i1.sabiden_aidm = (SELECT max(i2.sabiden_aidm) FROM sabiden i2 WHERE i2.sabiden_pidm = i1.sabiden_pidm)),
										gobtpac_pin_exp_date = add_months(sysdate,500)
						 WHERE gokisql.f_check_role('INTCOMP','UG_APP',gobtpac_pidm)='Y'
						   AND gobtpac_pidm=$pid";
						   
				/*
				 NEED TO generate a username if UG_APP and STUDENT_ACCOUNT_ACTIVE
				
				select psu_id from gorirol r1,psu_identity.person_identifiers
where exists(select 1 from gobtpac where gobtpac_external_user is null and gobtpac_pidm=r1.gorirol_pidm)
and r1.gorirol_role='UG_APP' and exists(select 1 from gorirol r2 where r2.gorirol_pidm=r1.gorirol_pidm and r2.gorirol_role='STUDENT_ACCOUNT_ACTIVE')
and pid=gorirol_pidm;*/
			}//end if
			else
			{
				$sql="SELECT 1 FROM dual";
			}//end else
		}//end if
		else
		{
			$sql="UPDATE gobtpac SET gobtpac_pin = (
														 SELECT sabnstu_pin
												FROM sabnstu,sabiden i1 
											 WHERE sabnstu_aidm = i1.sabiden_aidm 
												 AND i1.sabiden_pidm=gobtpac_pidm
												 AND i1.sabiden_aidm = (SELECT max(i2.sabiden_aidm) FROM sabiden i2 WHERE i2.sabiden_pidm = i1.sabiden_pidm)),
										gobtpac_pin_exp_date = add_months(sysdate,500)
  			   WHERE gokisql.f_check_role('INTCOMP','UG_APP',gobtpac_pidm)='Y'";
		}//end else
		
		return $this->_ADOdb->Execute($sql);
	}//end syncAdmitPin

	function synchUser($pid)
	{
		if(!$this->_ADOdb->Execute("INSERT INTO idm_queue (pid,activity_date) VALUES ($pid,sysdate)"))
		{
			$this->_ADOdb->Execute("UPDATE idm_queue SET pid_processed=null WHERE pid=$pid");
		}//end if
	}//end synchUser

	function userInQueue($pid)
	{
		return $this->_ADOdb->GetOne("SELECT count(*) FROM idm_queue WHERE pid=$pid AND pid_processed is null");
	}//end userInQueue

	function validIdentifier($input_id,$input_type)
	{
		return $this->_ADOdb->GetOne("SELECT count(*) FROM {$this->_idm_tables['person']} WHERE $input_type='$input_id'");
	}//end validIdentifier
}//end class IDMObject
}//end if
?>
