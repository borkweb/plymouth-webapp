<?php
/**
 * BannerGeneral.class.php
 *
 * === Modification History ===<br/>
 * 1.0.0  18-may-2005  [mtb]  original<br/>
 * 1.0.1  18-sep-2006  [zbt]  added getSynchronizedTerms
 * 1.0.2  19-oct-2006  [mtb]  added generatePidm and generateID
 * 1.0.3  30-oct-2006  [mtb]  added getAlternateSSN and generateRandomString
 * 1.0.4  13-jul-2007  [zbt]  added getPinHint
 * 1.0.5  13-aug-2007  [mtb]  added searchByName
 *
 * @package 		PSUBannerAPI
 */

/**
 * BannerGeneral.class.php
 *
 * Banner API
 *
 * @version		1.0.5
 * @module		BannerGeneral.class.php
 * @author		Matthew Batchelder <mtbatchelder@plymouth.edu>
 * @copyright 2005, Plymouth State University, ITS
 */ 
require_once('PSUTools.class.php');

if(!isset($GLOBALS['BannerIDM']))
{
	require_once('IDMObject.class.php');

	$GLOBALS['BannerIDM'] = new IDMObject();
	if(isset($GLOBALS['BANNER']))
		$GLOBALS['BannerIDM']->db = $GLOBALS['BANNER'];
}//end if

class BannerGeneral
{
	var $_ADOdb;
	/**
	 * BannerGeneral
	 *
	 * BannerGeneral constructor with db connection
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		ADOdb $adodb ADOdb database connection
	 */
	function BannerGeneral(&$adodb)
	{
		if($adodb)
		{
			$this->_ADOdb=$adodb;
		}//end if
		else
		{
			echo 'Not Connected To Database.  The BannerGeneral class expected $GLOBALS[\'BANNER\'] variable.';
		}//end else
	}//end BannerGeneral

	/**
	 * addressExists
	 *
	 * addressExists Checks whether an address exists for a given pidm and address type.
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $pid Banner pidm
	 * @param  		string $type Address type
	 * @return		boolean
	 */
	function addressExists($pid,$type)
	{
		$query="DECLARE v_exists VARCHAR2(1); BEGIN :v_exists := gb_address.f_exists_active($pid,'$type',sysdate,sysdate); END;";
		$stmt=$this->_ADOdb->PrepareSP($query);
		$this->_ADOdb->OutParameter($stmt,$exists,'v_exists');
		$this->_ADOdb->Execute($stmt);

		if($exists=='Y') return true;
		else return false;
	}//end addressExists

	/**
	 * cleanKeys
	 *
	 * cleanKeys Strlowers and str_replaces array values returned from Oracle
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		mixed $needle Search terms
	 * @param  		mixed $replace Replace terms
	 * @param  		array  $haystack Source array
	 * @return		array
	 */
	function cleanKeys($needle,$replace,$haystack)
	{
		$keys=array_keys($haystack);
		foreach($keys as $key)
		{
			$stripped_key=str_replace($needle,$replace,strtolower($key));
			$haystack[$stripped_key]=$haystack[$key];
			unset($haystack[$key]);
		}//foreach
		return $haystack;
	}//end cleanKeys

	/**
	 * commonMatchRecord
	 *
	 * commonMatchRecord performs common matching on a given set of values and returns an array of match status and pidm.
	 *
	 * @since		version 1.0.0
	 * @access	public
	 * @param  	string $ssn Person's SSN
	 * @param  	string $last_name Person's Last Name
	 * @param  	string $first_name Person's First Name
	 * @param  	string $middle_name Person's Middle Name
	 * @return	array
	 */
	function commonMatchRecord($ssn,$last_name,$first_name,$middle_name)
	{
		$sql="BEGIN gp_common_matching.p_insert_gotcmme('$last_name','P','$first_name','$middle_name','$ssn'); END;";
		$stmt=$this->_ADOdb->PrepareSP($sql);
		$this->_ADOdb->Execute($stmt);

		$match=array();
		$sql="BEGIN gp_common_matching.p_common_matching('GENERAL_PERSON',:flag,:pidm); END;";
		$stmt=$this->_ADOdb->PrepareSP($sql);
		$this->_ADOdb->OutParameter($stmt,$match['flag'],'flag');
		$this->_ADOdb->OutParameter($stmt,$match['pidm'],'pidm');
		$this->_ADOdb->Execute($stmt);
		return $match;
	}//end commonMatchRecord

	/**
	 * createEmailIfNeeded
	 *
	 * createEmailIfNeeded creates an e-mail address if needed
	 *
	 * @since		version 1.0.0
	 * @access	public
	 * @param  	string $pidm Banner pidm
	 * @param  	string $type Email type
	 */
	function createEmailIfNeeded($pidm,$type='CA')
	{
		$username=$GLOBALS['BannerIDM']->getIdentifier($pidm,'pid','username');
		if($username)
		{
			$sql="BEGIN :v_exists := gb_email.f_exists(p_pidm=>$pidm,p_emal_code=>'$type',p_email_address=>'$username@plymouth.edu'); END;";
			$stmt=$this->_ADOdb->PrepareSP($sql);
			$this->_ADOdb->OutParameter($stmt,$exists,'v_exists');
			$this->_ADOdb->Execute($stmt);

			if($exists=='N')
			{
				$sql="BEGIN gb_email.p_create(p_pidm=>$pidm,p_emal_code=>'$type',p_email_address=>'$username@plymouth.edu',p_preferred_ind=>'Y',p_rowid_out=>:row_id); END;";
				$stmt=$this->_ADOdb->PrepareSP($sql);
				$this->_ADOdb->OutParameter($stmt,$row_id,'row_id');
				$this->_ADOdb->Execute($stmt);
			}//end if
		}//end if
	}//end createEmailIfNeeded

	/**
	 * createIDCardIfNeeded
	 *
	 * createIDCardIfNeeded creates/updates an ID Card (SPBCARD) record if needed
	 *
	 * @since		version 1.0.0
	 * @access	public
	 * @param  	string $pidm Banner pidm
	 * @param  	string $group_type Type of ID Card
	 * @param  	string $employee_status Status of employment A=Active, I=Inactive
	 * @param  	string $benefit Benefit status
	 * @param  	string $department Employee's Department
	 */
	function createIDCardIfNeeded($pidm,$group_type,$employee_status='A',$benefit='N',$department='')
	{
		$sql="SELECT * FROM spbcard WHERE spbcard_pidm=$pidm";
		if($results=$this->_ADOdb->Execute($sql))
		{
			if($row=$results->FetchRow())
			{
				$row=$this->cleanKeys('spbcard_','',$row);
				$spriden=$this->getName($pidm);
				if($group_type!=$row['group_type'] || $employee_status!=$row['employee_status'] || $benefit!=$row['benefit_status'] || $department!=$row['department'] || $spriden['r_first_name']!=$row['first_name'] || $spriden['r_mi']!=$row['middle_name'] || $spriden['r_last_name']!=$row['last_name'])
				{
					$sql="UPDATE spbcard SET spbcard_first_name='".$spriden['r_first_name']."',spbcard_middle_name='".$spriden['r_mi']."',spbcard_last_name='".$spriden['r_last_name']."',spbcard_group_type='$group_type',spbcard_employee_status='$employee_status',spbcard_benefit_status='$benefit',spbcard_employee_dept='".PSUTools::cleanOracle($department)."',spbcard_update_date=sysdate WHERE spbcard_pidm=$pidm";
					$sql="UPDATE spbcard SET spbcard_employee_status='$employee_status' WHERE spbcard_pidm=$pidm";
					$this->_ADOdb->Execute($sql);
				}//end if
			}//end if
			else
			{
				$sql="INSERT INTO spbcard (spbcard_pidm,spbcard_id,spbcard_first_name,spbcard_middle_name,spbcard_last_name,spbcard_suffix,spbcard_group_type,spbcard_employee_status,spbcard_benefit_status,spbcard_employee_dept,spbcard_update_date) (SELECT spriden_pidm,spriden_id,spriden_first_name,spriden_mi,spriden_last_name,spbpers_name_suffix,'$group_type','$employee_status','$benefit','".PSUTools::cleanOracle($department)."',sysdate FROM spriden,spbpers WHERE spbpers_pidm=spriden_pidm AND spriden_pidm=$pidm AND spriden_change_ind is null)";
				$this->_ADOdb->Execute($sql);
			}//end else
		}//end if
		else
		{
			$sql="INSERT INTO spbcard (spbcard_pidm,spbcard_id,spbcard_first_name,spbcard_middle_name,spbcard_last_name,spbcard_suffix,spbcard_group_type,spbcard_employee_status,spbcard_benefit_status,spbcard_employee_dept,spbcard_update_date) (SELECT spriden_pidm,spriden_id,spriden_first_name,spriden_mi,spriden_last_name,spbpers_suffix,'$group_type','$employee_status','$benefit','".PSUTools::cleanOracle($department)."',sysdate FROM spriden,spbpers WHERE spbpers_pidm=spriden_pidm AND spriden_pidm=$pidm AND spriden_change_ind is null)";
			$this->_ADOdb->Execute($sql);
		}//end else
	}//end createIDCardIfNeeded

	/**
	 * createUsernameIfNeeded
	 *
	 * createUsernameIfNeeded generates a username if needed
	 *
	 * @since		version 1.0.0
	 * @access	public
	 * @param  	string $pidm Banner pidm
	 */
	function createUsernameIfNeeded($pidm)
	{
		$username=$GLOBALS['BannerIDM']->getIdentifier($pidm,'pid','username');
		if(!$username)
		{
			$sql="BEGIN :v_exists := gb_third_party_access.f_exists($pidm); END;";
			$stmt=$this->_ADOdb->PrepareSP($sql);
			$this->_ADOdb->OutParameter($stmt,$exists,'v_exists');
			$this->_ADOdb->Execute($stmt);

			if($exists=='N')
			{
				$pin=$this->generateRandomString(6);

				$sql="BEGIN gb_third_party_access.p_create(p_pidm=>$pidm,p_pin=>'$pin',p_external_user_inout=>:external_user,p_rowid_out=>:row_id); END;";
				$stmt=$this->_ADOdb->PrepareSP($sql);
				$this->_ADOdb->OutParameter($stmt,$external_user,'external_user');
				$this->_ADOdb->OutParameter($stmt,$row_id,'row_id');
				$this->_ADOdb->Execute($stmt);
			}//end if
			else
			{
				$sql="DECLARE external_user VARCHAR2(30); BEGIN :external_user := goktpty.f_generate_external_user($pidm); END;";
				$stmt=$this->_ADOdb->PrepareSP($sql);
				$this->_ADOdb->OutParameter($stmt,$external_user,'external_user');
				$this->_ADOdb->Execute($stmt);	


				$sql="BEGIN gb_third_party_access.p_update(p_pidm=>$pidm,p_external_user=>'$external_user'); END;";
				$stmt=$this->_ADOdb->PrepareSP($sql);
				$this->_ADOdb->Execute($stmt);
			}//end else
		}//end if
	}//end createUsernameIfNeeded

	/**
	 * currentTerm
	 *
	 * currentTerm retrieves the current Banner term for the given level
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $level Termcode Level
	 * @return		string
	 */
	function currentTerm($levl='UG')
	{
		return $this->_ADOdb->CacheGetOne("SELECT f_get_currentterm('$levl') FROM dual");
	}//end currentTerm

	/**
	 * debug
	 *
	 * debug enables debug mode if _ADOdb is set
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		boolean $bool Debug true/false
	 */
	function debug($bool)
	{
		if($this->_ADOdb)
			$this->_ADOdb->debug=$bool;
	}//end debug

	/**
	 * deleteDepartment
	 *
	 * deletes a given department
	 *
	 * @since		version 1.0.0
	 * @access	public
	 * @param  	string $code Department code
	 */
	function deleteDepartment($code)
	{
		$code=PSUTools::makeSafe($code);
		$sql="DELETE FROM pzvdept WHERE pzvdept_code='$code'";

		if($this->_ADOdb->Execute($sql))
			return true;
		return false;
	}//end deleteDepartment

	/**
	 * emailExists
	 *
	 * Checks if the given email exists
	 *
	 * @since		version 1.0.2
	 * @access		public
	 * @param       int $pid Person Identifier
	 * @param       string $email_type Email Type
	 * @return		string
	 */
	function emailExists($pid,$email_type='')
	{
		$exists=$this->_ADOdb->GetOne("SELECT 'Y' FROM goremal WHERE goremal_pidm=$pid AND goremal_emal_code".(($email_type)?"='$email_type'":"IS NULL"));

		if($exists=='Y') return true;
		else return false;
	}//end emailExists

	/**
	 * generateID
	 *
	 * generateID Generates an ID
	 *
	 * @since		version 1.0.2
	 * @access		public
	 * @return		string
	 */
	function generateID()
	{
		return $this->_ADOdb->GetOne("SELECT gb_common.f_generate_id FROM dual");
	}//end generateID

	/**
	 * generatePidm
	 *
	 * generatePidm Generates a PIDM
	 *
	 * @since		version 1.0.2
	 * @access		public
	 * @return		int
	 */
	function generatePidm()
	{
		return $this->_ADOdb->GetOne("SELECT gb_common.f_generate_pidm FROM dual");
	}//end generatePidm

	/**
	 * generateRandomString
	 *
	 * generateRandomString generate a random string of characters
	 *
	 * @since		version 1.0.3
	 * @access		public
	 * @param  		int $length desired length of string
	 * @param  		string $possible possible characters to select from
	 * @return		string
	 */
	function generateRandomString($length=6,$possible='')
	{
		//*****************[Generate Random Pin]**********************/
		// start with a blank pin
		$pin = "";

		// define possible characters
		$possible = ($possible)?$possible:"0123456789bcdfghjkmnpqrstvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ#"; 
			
		// set up a counter
		$i = 0; 
			
		// add random characters to $pin until $length is reached
		while($i < $length) 
		{ 
			// pick a random character from the possible ones
			$char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
					
			// we don't want this character if it's already in the password
			if (!strstr($pin, $char)) 
			{ 
				$pin .= $char;
				$i++;
			}//end if
		}//end while
		return $pin;
	}//end generateRandomString

	/**
	 * getAddress
	 *
	 * getAddress retrieves active addresses for the given type and date
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $pid Banner pidm
	 * @param  		string $type Address Type
	 * @param  		string $date Date for checking active addresses at a given point in time
	 * @param  		string $date_format Format for the passed in date
	 * @return		array
	 */
	function getAddress($pid,$type,$date='sysdate',$date_format='YYYY-MM-DD',$where='')
	{
		$bind = array();
		if($pid) $bind['pidm'] = $pid;
		$bind['type'] = $type;

		if(!$date || $date=='sysdate')
		{
			$date=" AND ((sysdate BETWEEN spraddr_from_date AND spraddr_to_date) OR (spraddr_from_date<=sysdate AND spraddr_to_date IS NULL))";
		}//end if
		else
		{
			$bind['date'] = $date;
			$bind['format'] = $format;
			$date=" AND ((TO_DATE(:date,:format) BETWEEN spraddr_from_date AND spraddr_to_date) OR (spraddr_from_date<=TO_DATE(:date,:format) AND spraddr_to_date IS NULL))";
		}//end else

		$addresses=array();
		$query="SELECT *
							FROM spraddr 
						 WHERE ".(($pid)?"spraddr_pidm=:pidm AND ":"")." spraddr_atyp_code=:type
							 AND spraddr_status_ind IS NULL $date $where ORDER BY spraddr_seqno DESC";
		if($results=$this->_ADOdb->Execute($query, $bind))
		{
			while($row=$results->FetchRow())
			{
				$addresses[]=PSUTools::cleanKeys('spraddr_','r_',$row);
			}//end while
		}//end if
		return $addresses;
	}//end getAddress

	/**
	 * getAllAddresses
	 *
	 * getAllAddresses retrieves active addresses for the given type and date
	 *
	 * @since		version 1.0.3
	 * @access		public
	 * @param  		string $pid Banner pidm
	 * @param  		string $type Address Type
	 * @param  		string $date Date for checking active addresses at a given point in time
	 * @param  		string $date_format Format for the passed in date
	 * @return		array
	 */
	function getAllAddresses($pid,$date='sysdate',$date_format='YYYY-MM-DD',$where='')
	{
		$bind = array();
		if($pid) $bind['pidm'] = $pid;
		
		if(!$date || $date=='sysdate')
		{
			$date=" AND ((sysdate BETWEEN spraddr_from_date AND spraddr_to_date) OR (spraddr_from_date<=sysdate AND spraddr_to_date IS NULL))";
		}//end if
		else
		{
			$bind['date'] = $date;
			$bind['format'] = $format;
			$date=" AND ((TO_DATE(:date,:format) BETWEEN spraddr_from_date AND spraddr_to_date) OR (spraddr_from_date<=TO_DATE(:date,:format) AND spraddr_to_date IS NULL))";
		}//end else
		
		$addresses=array();
		$query="SELECT *
							FROM spraddr 
						 WHERE ".(($pid)?"spraddr_pidm=:pidm AND ":"")." spraddr_status_ind IS NULL $date $where ORDER BY spraddr_atyp_code,spraddr_seqno DESC";
		if($results=$this->_ADOdb->Execute($query, $bind))
		{
			while($row=$results->FetchRow())
			{
				$addresses[]=PSUTools::cleanKeys('spraddr_','r_',$row);
			}//end while
		}//end if
		return $addresses;
	}//end getAllAddresses

	/**
	 * getAllEmail
	 *
	 * getEmail retrieves a person's active e-mail addresses of a given type.  If no type is given, all active e-mail addresses are returned.
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $pid Banner pidm
	 * @param  		string $type Email type
	 * @return		array
	 */
	function getAllEmail($pid,$where='')
	{
		$email=array();
		$query="SELECT *
						  FROM goremal 
						 WHERE goremal_pidm=:pidm 
							 AND goremal_status_ind='A'
							 ".(($where)?"AND ".$where:"")."
					ORDER BY goremal_emal_code,goremal_email_address";
					
		$bind = array();
		$bind['pidm'] = $pid;
		
		if($results=$this->_ADOdb->Execute($query, $bind))
		{
			while($row=$results->FetchRow())
			{
				$email[]=PSUTools::cleanKeys('goremal_','r_',$row);
			}//end while
		}//end if
		return $email;
	}//end getAllEmail

	/**
	 * getAllPhones
	 *
	 * getAllPhones retrieves active phones for the given person and type
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $pid Banner pidm
	 * @param  		string $type Phone Type
	 * @return		array
	 */
	function getAllPhones($pid)
	{

		$phone=array();
		$query="SELECT *
		          FROM sprtele 
		         WHERE sprtele_pidm=:pidm 
		           AND sprtele_status_ind IS NULL ORDER BY sprtele_tele_code ASC,sprtele_addr_seqno DESC";

		$bind = array();
		$bind['pidm'] = $pid;

		if($results=$this->_ADOdb->Execute($query, $bind))
		{
			while($row=$results->FetchRow())
			{
				$phone[]=PSUTools::cleanKeys('sprtele_','r_',$row);
			}//end while
		}//end if
		return $phone;
	
	}//end getAllPhones

	/**
	 * getAlternateSSN
	 *
	 * retrieves a users alternate ssn
	 *
	 * @since		version 1.0.3
	 * @access		public
	 * @param  		string $pid Banner pidm
	 * @return		array
	 */
	function getAlternateSSN($pid)
	{
		$row = $GLOBALS['BANNER']->GetRow("SELECT gobintl_cert_number,gobintl_foreign_ssn FROM gobintl WHERE gobintl_pidm = $identifier");
		$row = PSUTools::cleanKeys('gobintl_','r_',$row);
		if(strlen($row['r_cert_number'])>=9)
			return $row['r_cert_number'];
		elseif(strlen($row['r_foreign_ssn'])>0)
			return $row['r_foreign_ssn'];
		else
			return false;
	}//end getAlternateSSN

	/**
	 * getBio
	 *
	 * getBio retrieves SPBPERS (Biographical) data.
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $pid Banner pidm
	 * @return		array
	 */
	function getBio($pid)
	{
		$query="SELECT spbpers.*,
		               to_char(spbpers_birth_date, 'YYYY-MM-DD') spbpers_birth_date_unix,
		               to_char(spbpers_activity_date, 'YYYY-MM-DD') spbpers_activity_date_unix
		          FROM spbpers
		         WHERE spbpers_pidm=:pidm";
		$result = PSUTools::cleanKeys('spbpers_','r_',$this->_ADOdb->GetRow($query, array('pidm' => $pid)));

		$result['r_activity_date_unix'] = strtotime($result['r_activity_date_unix']);
		$result['r_birth_date_unix'] = strtotime($result['r_birth_date_unix']);

		return $result;
	}//end getBio

	/**
	 * getDepartments
	 *
	 * getDepartments returns a list of departments
	 *
	 * @since		version 1.0.0
	 * @access	public
	 * @param  	string $where Optional where statement
	 * @return	string
	 */
	function getDepartments($where='')
	{
		$data=array();
		if($result=$this->_ADOdb->Execute("SELECT * FROM pzvdept ".(($where)?"WHERE ".$where:"")." ORDER BY pzvdept_desc"))
		{
			while($row=$result->FetchRow())
			{
				$data[]=$this->cleanKeys('pzvdept_','r_',$row);
			}//end while
		}//end if
		return $data;
	}//end getDepartments

	/**
	 * getDesc
	 *
	 * getDesc retrieves the description field in the given validation table with the given code
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $table Validation table
	 * @param  		string $value Code
	 * @param  		string $where Additional WHERE clause values for the validation table
	 * @return		string
	 */
	function getDesc($table,$value,$where='')
	{
		$query="SELECT {$table}_desc FROM $table WHERE {$table}_code='$value' ".(($where)?' AND '.$where:'');
		return $this->_ADOdb->CacheGetOne($query);
	}//end getDesc

	/**
	 * getEmail
	 *
	 * getEmail retrieves a person's active e-mail addresses of a given type.  If no type is given, all active e-mail addresses are returned.
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $pid Banner pidm
	 * @param  		string $type Email type
	 * @return		array
	 */
	function getEmail($pid,$type='',$where='')
	{
		$type=($type)?" AND goremal_emal_code='$type'":'';
		$email=array();
		$query="SELECT *
						  FROM goremal 
						 WHERE goremal_pidm=$pid 
							 AND goremal_status_ind='A' $type 
							 ".(($where)?"AND ".$where:"")."
					ORDER BY goremal_emal_code,goremal_email_address";
		if($results=$this->_ADOdb->Execute($query))
		{
			while($row=$results->FetchRow())
			{
				$email[]=PSUTools::cleanKeys('goremal_','r_',$row);
			}//end while
		}//end if
		return $email;
	}//end getEmail

	/**
	 * getGender
	 *
	 * retrieves a person's gender
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $pid Banner pidm
	 * @return		array
	 */
	function getGender($pid)
	{
		$data = $this->getBio($pid);
		
		return $data['r_sex'];
	}//end getGender

	/**
	 * getRoomData
	 *
	 * returns an associative array for a phone number based on building and room info
	 *
	 * @since	version 1.0.0
	 * @access	public
	 * @param  	string $bldg_code Banner building code from SLRRASG_BLDG_CODE
	 * @param  	string $room_number Banner room number from SLRRASG_ROOM_NUMBER
	 */
	function getRoomData($bldg_code, $room_number) 
	{
		$data = array();
		$query = "SELECT * FROM saturn.slbrdef WHERE slbrdef_bldg_code = '".$bldg_code."' and slbrdef_room_number = '".$room_number."' AND slbrdef_term_code_eff = (SELECT max(slbrdef_term_code_eff) FROM saturn.slbrdef WHERE slbrdef_bldg_code = '".$bldg_code."' AND slbrdef_room_number = '".$room_number."')";
		if ($results=$this->_ADOdb->Execute($query))
		{
			while($row=$results->FetchRow())
			{
				$data=PSUTools::cleanKeys('slbrdef_','r_',$row);
			} // end for		
		} // end if
		return $data;
	} //end getRoomData


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
		$sql="SELECT * FROM spriden,spbpers WHERE spriden_pidm=spbpers_pidm AND spriden_change_ind is null AND spriden_first_name like '".substr($first_name,0,1)."%' AND spriden_last_name like '".substr($last_name,0,10)."%' AND spbpers_ssn IS NULL";
		if($results=$this->_ADOdb->Execute($sql))
		{
			while($row=$results->FetchRow())
			{
				$data[]=$this->cleanKeys('spriden_','r_',$row);
			}//end while
		}//end if
		return $data;
	}//end getSimilarNames

	/**
	 * getTerm
	 *
	 * retrieves the current term for the given level
	 *
	 * TODO: implement date based retrieval
	 *
	 * @since		version 1.0.0
	 * @access	public
	 * @param  	string $levl_code Level Code
	 * @return	string
	 */
	function getTerm($levl_code)
	{
		return $this->_ADOdb->GetOne("SELECT f_get_currentterm('{$levl_code}') FROM dual");
	}//end getTerm

	/**
	 * getTermDetail
	 *
	 * retrieves date, descriptio, and activity status for a given term code
	 *
	 * @since		version 1.0.0
	 * @access	public
	 * @param  	string $term_code Term Code
	 * @return	array
	 */
	function getTermDetail($term_code='201010')
	{
		if(strlen($term_code) !=6) $term_code = $this->getTerm('UG');

		$sql = "SELECT * FROM stvterm WHERE stvterm_code = '".$term_code."'";
		if($row=$this->_ADOdb->GetRow($sql))
		{
			$data = $this->cleanKeys('stvterm_','r_',$row);
			$data['name'] = $data['r_desc'];
			$data['r_active_term'] = ($this->_ADOdb->GetOne("SELECT 'Y' FROM goriccr where goriccr_sqpr_code = 'ELEARNING' and goriccr_icsn_code = 'ACTIVE_TERM' and goriccr_value = '".$data['r_code']."'") == 'Y')?true:false;
			return $data;
		}//end if
		return false;
	}//end getTermDetail

	/**
	 * getIDCardRecord
	 *
	 * getIDCardRecord returns an employee's ID Card record
	 *
	 * @since		version 1.0.0
	 * @access	public
	 * @param  	string $pidm Banner pidm
	 * @return	array
	 */
	function getIDCardRecord($pidm)
	{
		return $this->cleanKeys('spbcard_','r_',$this->_ADOdb->GetRow("SELECT * FROM spbcard WHERE spbcard_pidm=$pidm"));
	}//end getIDCardRecord

	/**
	 * getIdentifier
	 *
	 * getIdentifier retrieves an identifier for the given person based on a passed in idetifier.
	 *	<br/><b>Available identifier types</b>:
	 *  - pid
	 *	- psu_id
	 *	- sourced_id
	 *	- ssn
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $identifier_in Some identifier
	 * @param  		string $identifier_in_type Type of identifier
	 * @param  		string $identifier_out_type Type of desired output identifier
	 * @return		string
	 */
	function getIdentifier($identifier_in,$identifier_in_type,$identifier_out_type)
	{
		return $GLOBALS['BannerIDM']->getIdentifier($identifier_in,$identifier_in_type,$identifier_out_type);
	}//end getIdentifier

	/**
	 * getName
	 *
	 * getName retrieves the active SPRIDEN record for the given person
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $pidm Banner pidm
	 * @return		array
	 */
	function getName($pidm)
	{
		return $this->getPersonData($pidm);
	}//end getName

	/**
	 * getPersonData
	 *
	 * retrieves the active SPRIDEN record for the given person
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $pidm Banner pidm
	 * @return		array
	 */
	function getPersonData($pidm)
	{
		$query="SELECT * FROM spriden WHERE spriden_pidm=:pidm AND spriden_change_ind IS NULL";
		$row=$this->_ADOdb->GetRow($query, array('pidm' => $pidm));
		if($row)
			return PSUTools::cleanKeys('spriden_','r_',$row);
		return false;
	}//end getPersonData

	/**
	 * getPhone
	 *
	 * getPhone retrieves active phones for the given person and type
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $pid Banner pidm
	 * @param  		string $type Phone Type
	 * @return		array
	 */
	function getPhone($pid,$type)
	{
		$bind = array();
		if($pid) $bind['pidm'] = $pid;
		$bind['type'] = $type;
		
		$phone=array();
		$query="SELECT *
		          FROM sprtele WHERE sprtele_pidm=:pidm AND sprtele_tele_code=:type AND sprtele_status_ind IS NULL ORDER BY sprtele_addr_seqno DESC";
		if($results=$this->_ADOdb->Execute($query, $bind))
		{
			while($row=$results->FetchRow())
			{
				$phone[]=PSUTools::cleanKeys('sprtele_','r_',$row);
			}//end while
		}//end if
		return $phone;
	
	}//end getPhone

	/**
	 * getPin
	 *
	 * getPin retrieves person's Banner pin
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $pid Banner pidm
	 * @return		string
	 */
	function getPin($pid)
	{
		return $this->_ADOdb->GetOne("SELECT gobtpac_pin FROM gobtpac WHERE gobtpac_pidm=$pid");
	}//end getPin

	function getPinHint($pid)
	{
		$hint = $this->_ADOdb->GetRow("SELECT gobtpac_question as question, gobtpac_response as response FROM gobtpac WHERE gobtpac_pidm=$pid");
		if(is_array($hint)) $hint = PSUTools::cleanKeys('','',$hint);
		return $hint;
	}//end getPinHint

	/**
	 * getSynchronizedTerms
	 *
	 * getSynchronizedTerms retrieves term codes set with "synchronize partner systems" enabled in SOATERM INB form
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @return		array
	 */
	function getSynchronizedTerms()
	{
		$query = "SELECT sobterm_term_code FROM sobterm WHERE sobterm_profile_send_ind='Y'";
		if($results=$this->_ADOdb->Execute($query))
		{
			while($row=$results->FetchRow())
			{
				$row = PSUTools::cleanKeys('sobterm_','r_',$row);
				$term[]=$row['r_term_code'];
			}//end while
		}//end if
		return $term;
	}//end getSynchronizedTerms

	/**
	 * getValidation
	 *
	 * getValidation retrieves validation table records
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $table Validation table
	 * @param  		string $where WHERE clause values for the validation table
	 * @return		array
	 */
	function getValidation($table,$where='',$select='*',$method='')
	{
		if($where)
		{
			$where=" WHERE ".$where;
		}//end else

		$query="SELECT ".(($select)?$select:'*')."
							FROM $table $where
						 ORDER BY ".$table."_desc";
						 
		if($method=='row')
		{
			$data=$this->_ADOdb->GetRow($query);
			$data=PSUTools::cleanKeys($table.'_','r_',$row);
		}//end elseif
		elseif($method=='value')
		{
			$data=$this->_ADOdb->GetOne($query);
		}//end elseif
		else
		{
			$data=array();
			if($results=$this->_ADOdb->Execute($query))
			{
				while($row=$results->FetchRow())
				{
					$row=PSUTools::cleanKeys($table.'_','r_',$row);
					if($row['r_code'])
						$data[$row['r_code']]=$row;
					else
						$data[]=$row;
				}//end while
			}//end if
		}//end else
		return $data;
	}//end getValidation

	function hasRecord($pidm,$table,$where='',$include_table=true)
	{
		return $this->_ADOdb->GetOne("SELECT count(".(($include_table)?$table.'_':'')."pidm) FROM $table WHERE ".(($include_table)?$table.'_':'')."pidm=$pidm $where");
	}//end hasRecord

	/**
	 * isActiveFaculty
	 *
	 * isActiveFaculty returns whether or not the given person is an active faculty member
	 *
	 * @since		version 1.0.0
	 * @access	public
	 * @param  	string $pid Person identifier
	 */
	function isActiveFaculty($pid)
	{
		$sql="SELECT s1.sibinst_fcst_code FROM sibinst s1 WHERE s1.sibinst_pidm=$pid AND s1.sibinst_term_code_eff = (SELECT max(s2.sibinst_term_code_eff) from sibinst s2 WHERE s2.sibinst_pidm=s1.sibinst_pidm)";
		$status=$this->_ADOdb->GetOne($sql);
		if($status=='AC')
			return true;
		else
			return false;
	}//end isActiveFaculty

	/**
	 * isValidIDCard
	 *
	 * isValidIDCard returns whether or not a given id has a valid id card
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $id PSU Id
	 * @return		boolean
	 */
	function isValidIDCard($id)
	{
		$exists=$this->_ADOdb->GetOne("SELECT 'Y' FROM spbcard WHERE spbcard_id='$id' AND (spbcard_student_status='AS' OR spbcard_employee_status='A' OR spbcard_group_type = 'Retired')");
		if($exists=='Y')
			return true;
		return false;
	}//end isValidIDCard

	/**
	 * isValidPidm
	 *
	 * Determines if a given pidm is valid
	 *
	 * @since		version 1.0.2
	 * @access		public
	 * @param       int $pidm Person Identifier
	 * @param       string $entity Entity Indicator (P = Person, C = Non-Person)
	 * @return		boolean
	 */
	function isValidPidm($pidm, $entity = 'P')
	{
		return $this->_ADOdb->GetOne("SELECT 1 FROM spriden WHERE spriden_pidm = $pidm AND spriden_entity_ind = '$entity'");
	}//end isValidPidm

	/**
	 * phoneExists
	 *
	 * phoneExists returns whether or not a given phone exists for the given person
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $pid Banner pidm
	 * @param  		string $tele_type Phone Type
	 * @param  		string $addr_type Address Type
	 * @return		boolean
	 */
	function phoneExists($pid,$tele_type,$addr_type='')
	{
		/*
		$query="DECLARE v_exists VARCHAR2(1); BEGIN :v_exists := gb_telephone.f_exists($pid,'$type',1); END;";
		$stmt=$this->_ADOdb->PrepareSP($query);
		$this->_ADOdb->OutParameter($stmt,$exists,'v_exists');
		$this->_ADOdb->Execute($stmt);*/

		$exists=$this->_ADOdb->GetOne("SELECT 'Y' FROM sprtele WHERE sprtele_pidm=$pid AND sprtele_tele_code='$tele_type' AND sprtele_atyp_code".(($addr_type)?"='$addr_type'":" is NULL"));

		if($exists=='Y') return true;
		else return false;
	}//end phoneExists

	/**
	 * searchByName
	 *
	 * searchByName returns matching persons by a given name
	 *
	 * @since		version 1.0.5
	 * @access		public
	 * @param  		string $name Name to be searched
	 * @return		array
	 */
	function searchByName($name)
	{
		return $this->search($name);
	}//end searchByName

	/**
	 * search
	 *
	 * returns matching persons by a given name
	 *
	 * @since		version 1.0.5
	 * @access		public
	 * @param  		string $name Name to be searched
	 * @param     string $type The type of search to conduct
	 * @return		array
	 */
	function search($name, $type = 'name')
	{
		$joins = ''; // no additional joins or selects by default
		$select = '';

		$name = stripslashes($name);
		$people = array();
		if(preg_match('/[0-9]{9}/',$name))
		{
			$where=" s1.spriden_id='$name'";
		}//end if
		elseif( is_int($name) || ctype_digit($name) ) {
			$name = (int)$name;
			$where = "s1.spriden_pidm = $name";
		}
		elseif( $type == 'email' ) {
			$name = strtolower($name);

			$joins = 'LEFT JOIN goremal ON s1.spriden_pidm = goremal_pidm';
			$where = "goremal_email_address = " . $this->_ADOdb->qstr($name) . " AND goremal_status_ind = 'A'";
			$select = 'goremal_email_address,goremal_emal_code';
		}
		else
		{
			$name = preg_replace("/[^A-Za-z0-9\.%,_]/", "", $name);
			$name = str_replace('*','%',$name);
			$name = strtoupper($name);
			$name = explode(',', $name);

			$where=" s1.spriden_search_last_name LIKE ".$this->_ADOdb->qstr($name[0]);
			$additional = "SELECT 1 FROM spriden s2 WHERE s2.spriden_pidm = s1.spriden_pidm AND s2.spriden_search_last_name LIKE ".$this->_ADOdb->qstr($name[0]);	

			if(count($name)>1)
			{
				$where.=" AND s1.spriden_search_first_name like ".$this->_ADOdb->qstr($name[1]);
				$additional.=" AND s2.spriden_search_first_name like ".$this->_ADOdb->qstr($name[1]);

				$where = "((" . $where .") OR EXISTS(".$additional."))";
			}
			else
			{
				$where="((".$where.") OR EXISTS(".$additional.") OR (LOWER(gobtpac_ldap_user) like ".strtolower($this->_ADOdb->qstr($name[0]))." OR LOWER(gobtpac_ldap_user) like ".strtolower($this->_ADOdb->qstr('app.'.$name[0]))."))";
			}//end else
		}//end else
		$name_count=0;

		if( $select ) {
			$select = ',' . $select; // prepend comma for column list
		}
		$query="SELECT s1.spriden_pidm,s1.spriden_id,s1.spriden_first_name,s1.spriden_mi,s1.spriden_last_name,gobtpac_external_user,gobtpac_ldap_user $select FROM spriden s1 LEFT OUTER JOIN gobtpac ON gobtpac_pidm=s1.spriden_pidm $joins WHERE s1.spriden_change_ind is NULL AND $where ORDER BY s1.spriden_last_name,s1.spriden_first_name,s1.spriden_mi,s1.spriden_id";
		if($results=$this->_ADOdb->Execute($query))
		{
			while($row=$results->FetchRow())
			{
				$row = PSUTools::cleanKeys(array('spriden_','gobtpac_','goremal_'),'r_',$row);
				$sql = "SELECT DISTINCT spriden_last_name FROM spriden WHERE spriden_pidm = :pidm AND spriden_last_name <> :last_name";
				$row['last_names'] = $this->_ADOdb->GetCol( $sql, array( 'pidm' => $row['r_pidm'], 'last_name' => $row['r_last_name'] ) ); 
				$people[]=$row;
			}//end while
		}//end if
		return $people;
	}//end search

	/**
	 * ssnExists
	 *
	 * ssnExists returns whether or not a given SSN exists in Banner
	 *
	 * @since		version 1.0.0
	 * @access	public
	 * @param  	string $pid Person identifier
	 */
	function ssnExists($ssn)
	{
		if($this->_ADOdb->GetOne("SELECT 1 FROM spbpers WHERE spbpers_ssn='$ssn'")==1)
			return true;
		return false;
	}//end ssnExists

	/**
	 * updateAddress
	 *
	 * updateAddress updates/inserts addresses for a person. Portions of this method have been copied into PSUAddress::save().
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $pid Banner pidm
	 * @param  		string $type Address type to insert/update
	 * @param  		string $params Additional parameters to be passed in.  These parameters are formatted as a GET string
	 */
	function updateAddress($pid,$type,$params)
	{
		if(!is_array($params))
		{
			parse_str($params,$params);
		}//end if

		if($this->addressExists($pid,$type))
		{
			$address=$this->getAddress($pid,$type);

			if(!$address[0]['r_seqno'])
			{
				$address[0]['r_seqno']=$this->_ADOdb->GetOne("SELECT max(spraddr_seqno) as seqno FROM spraddr WHERE spraddr_pidm=$pid AND spraddr_atyp_code='$type'");
			}//end if

			$query="DECLARE v_row gb_common.internal_record_id_type; BEGIN gb_address.p_lock(p_pidm=>:p_pidm, p_atyp_code=>:p_atyp_code, p_seqno=>:p_seqno,p_status_ind=>NULL,p_rowid_inout=>:v_row); END;";
			$stmt=$this->_ADOdb->PrepareSP($query);
			$this->_ADOdb->OutParameter($stmt,$row_id,'v_row');
			$this->_ADOdb->InParameter($stmt,$pid,'p_pidm');
			$this->_ADOdb->InParameter($stmt,$type,'p_atyp_code');
			$this->_ADOdb->InParameter($stmt,$address[0]['r_seqno'],'p_seqno');
			$this->_ADOdb->Execute($stmt);

			//ADOdb transOff being True....means that transactions are on
			$query="BEGIN gb_address.p_update( p_pidm=>:p_pidm, p_atyp_code=>:p_atyp_code,p_to_date=>sysdate,p_seqno=>:p_seqno,p_status_ind=>'I',p_rowid=>:p_rowid); ".(($this->_ADOdb->transOff)?"":"gb_common.p_commit();")." END;";
			$stmt=$this->_ADOdb->PrepareSP($query);
			$this->_ADOdb->InParameter($stmt,$pid,'p_pidm');
			$this->_ADOdb->InParameter($stmt,$type,'p_atyp_code');
			$this->_ADOdb->InParameter($stmt,$address[0]['r_seqno'],'p_seqno');
			$this->_ADOdb->InParameter($stmt,$row_id,'p_rowid');
			$this->_ADOdb->Execute($stmt);
		}//end if

		$query="DECLARE insert_seqno spraddr.spraddr_seqno%TYPE; insert_rowid gb_common.internal_record_id_type; BEGIN gb_address.p_create( p_pidm=>$pid, p_atyp_code=>'$type'".((isset($params['from_date']) && strlen($params['from_date'])>0)?"":", p_from_date=>sysdate");
		foreach($params as $key=>$param)
		{
			if($key=='stat_code')
				$param=substr(strtoupper(trim($param)),0,2);
			if($param)
				$query.=",p_".$key."=>'".urldecode($param)."'";
		}//end foreach
		//ADOdb transOff being True....means that transactions are on
		$query .=",p_seqno_inout=>:insert_seqno,p_rowid_out=>:insert_rowid); ".(($this->_ADOdb->transOff)?"":"gb_common.p_commit();")." END;";

		$stmt=$this->_ADOdb->PrepareSP($query);
		$this->_ADOdb->OutParameter($stmt,$insert_seqno,'insert_seqno');
		$this->_ADOdb->OutParameter($stmt,$insert_rowid,'insert_rowid');
		$this->_ADOdb->Execute($stmt);
	}//end updateAddress

	/**
	 * updateDepartment
	 *
	 * updateDepartment updates a department's description identified by a given code
	 *
	 * @since		version 1.0.0
	 * @access	public
	 * @param  	string $code Department identifier
	 * @param   string $desc Department description
	 */
	function updateDepartment($code,$desc)
	{
		$code=PSUTools::makeSafe($code);
		$desc=PSUTools::makeSafe($desc);
		$sql="UPDATE pzvdept SET
						pzvdept_desc='$desc'
					WHERE pzvdept_code='$code'";

		if($this->_ADOdb->Execute($sql))
			return $desc;
		return false;
	}//end updateDepartment

	/**
	 * updateEmail
	 *
	 * updates a user's email address of the given type
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $pid Banner pidm
	 * @param  		string $email_type Phone type to insert/update
	 * @param  		string $params Additional parameters to be passed in.  These parameters are formatted as a GET string
	 */
	function updateEmail($pid,$email_type='',$params)
	{
		if(!is_array($params))
		{
			parse_str($params,$params);
		}//end if
		
		if(!$params['user_id'])
		{
			$params['user_id']='Script: '.$_SESSION['username'];
		}//end if
		$params['user_id']=substr($params['user_id'],0,30);

		if($this->emailExists($pid,$email_type)) 
		{
			//$this->_ADOdb->Execute("UPDATE goremal SET goremal_status_ind='I' WHERE goremal_pidm=$pid AND goremal_emal_code='$email_type'");
			$email=$this->getEmail($pid,$email_type);

			$query="DECLARE v_row gb_common.internal_record_id_type; BEGIN gb_email.p_lock(p_pidm=>$pid, p_emal_code=>'$email_type',p_email_address=>'".$email[0]['r_email_address']."',p_rowid_inout=>:v_row); END;";
			$stmt=$this->_ADOdb->PrepareSP($query);
			$this->_ADOdb->OutParameter($stmt,$row_id,'v_row');
			$this->_ADOdb->Execute($stmt);

			if(!$params['email_address'])
				$params['email_address']=$email[0]['r_email_address'];

			$query="BEGIN gb_email.p_update( p_pidm=>$pid, p_emal_code=>'$email_type'";
			foreach($params as $key=>$param)
			{
				if($param && !in_array($key,array('emal_code','rowid','pidm')))
					$query.=",p_".$key."=>'".urldecode($param)."'";
			}//end foreach
			//ADOdb transOff being True....means that transactions are on
			$query.=",p_rowid=>'$row_id'); ".(($this->_ADOdb->transOff)?"":"gb_common.p_commit();")." END;";
			$stmt=$this->_ADOdb->PrepareSP($query);
			$this->_ADOdb->Execute($stmt);
		}//end if
		else
		{
			$query="BEGIN gb_email.p_create( p_pidm=>$pid, p_emal_code=>'$email_type'";
			foreach($params as $key=>$param)
			{
				if($param && !in_array($key,array('emal_code','rowid','pidm')))
					$query.=",p_".$key."=>'".urldecode($param)."'";
			}//end foreach
			//ADOdb transOff being True....means that transactions are on
			$query.=",p_rowid_out=>:insert_rowid); ".(($this->_ADOdb->transOff)?"":"gb_common.p_commit();")." END;";
			$stmt=$this->_ADOdb->PrepareSP($query);
			$this->_ADOdb->OutParameter($stmt,$insert_rowid,'insert_rowid');
			$this->_ADOdb->Execute($stmt);
		}//end else

		//$email=$this->getEmail($pid,$email_type);

	}//end updateEmail

	/**
	 * updatePhone
	 *
	 * updatePhone updates/inserts phones for a person
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $pid Banner pidm
	 * @param  		string $tele_type Phone type to insert/update
	 * @param  		string $addr_type Address type to associate phone to
	 * @param  		string $params Additional parameters to be passed in.  These parameters are formatted as a GET string
	 */
	function updatePhone($pid,$tele_type,$addr_type='',$params)
	{
		if(!is_array($params))
		{
			parse_str($params,$params);
		}//end if

		if($this->phoneExists($pid,$tele_type,$addr_type))
		{
			$this->_ADOdb->Execute("UPDATE sprtele SET sprtele_status_ind='I' WHERE sprtele_pidm=$pid AND sprtele_tele_code='$tele_type' AND sprtele_atyp_code".(($addr_type)?"='$addr_type'":" IS NULL"));
		}//end if

		$address=$this->getAddress($pid,$addr_type);

		$query="DECLARE insert_seqno sprtele.sprtele_seqno%TYPE; insert_rowid gb_common.internal_record_id_type; BEGIN gb_telephone.p_create( p_pidm=>$pid, p_tele_code=>'$tele_type', p_atyp_code=>".(($address[0]['r_atyp_code'])?"'".$address[0]['r_atyp_code']."'":"NULL").", p_addr_seqno=>".(($address[0]['r_seqno'])?$address[0]['r_seqno']:"NULL");
		foreach($params as $key=>$param)
		{
			//$param=explode('=',$param);
			if($param)
				$query.=",p_".$key."=>'".urldecode($param)."'";
		}//end foreach
		//ADOdb transOff being True....means that transactions are on
		$query .=",p_seqno_out=>:insert_seqno,p_rowid_out=>:insert_rowid); ".(($this->_ADOdb->transOff)?"":"gb_common.p_commit();")." END;";

		$stmt=$this->_ADOdb->PrepareSP($query);
		$this->_ADOdb->OutParameter($stmt,$insert_seqno,'insert_seqno');
		$this->_ADOdb->OutParameter($stmt,$insert_rowid,'insert_rowid');
		$this->_ADOdb->Execute($stmt);

	}//end updatePhone
}//end BannerGeneral

?>
