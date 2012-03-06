<?php

/**
 *
 * WARNING: this class if depricated (11/12/10 - ZBT) 
 * all code relying on this should be updated to leverage alternate means
 * of accessing the data in here.  This is due to the move away from 
 * Luminis and toward APE and Project Mercury
 *
 */

/**
 * portal.class.php
 *
 * === Modification History ===
 * 0.1.0  11-may-2006  [zbt]  original
 * 0.1.1  13-sep-2006  [mtb&zbt] added additional LDAP attributes
 * 0.2.0  18-sep-2006  [zbt]  added getEmailSettings and checkNonUser
 * 0.3.0  26-may-2007  [zbt]  changed uid to pdsLoginId for LIV compatibility
 * 0.4.0  18-jan-2008  [zbt]  added getAllRoles, changed ldap connection code, and made getUserInfo multi person capable
 * 0.5.0  22-apr-2008  [zbt]  added a bunch more comments, still a ways to go...
 * 0.6.0  2-oct-2009  [zbt]  added a function to get configuration settings and a specific func for all external roles as an array.
 *
 * @package 		Tools
 */

/**
 * portal.class.php
 *
 * Portal function library
 *
 * @version		0.4.0
 * @module		portal.class.php
 * @author		Zachary Tirrell <zbtirrell@plymouth.edu>
 * @copyright 2006, Plymouth State University, ITS
 */ 

class Portal
{
	var $_portal_ds;

	var $_which;

	var $_ldap = array();

	/**
	 * Portal
	 *
	 * constructor, makes LDAP connection automatically
	 *
	 *@param string @which
	 *@return mixed
	 */
	public function __construct($which='prod')
	{
		return $this->connectLDAP($which);
	}//end Portal

	/**
	 * addAttribute
	 *
	 * Adds to an existing attribute without affecting existing values. Ex: adding to pdsRole without affecting existing roles.
	 *
	 *@param string $username
	 *@param array $array
	 *@return mixed
	 */
	public function addAttribute($username,$array)
	{
		$immid = $this->getPortalAttribute('uid',$username);
		$immid = $immid[0];
		// array need to be keyed appropriately
		return ldap_mod_add($this->_portal_ds, "uid=$immid, ".$this->_ldap['root'], $array);
	}//end addAttribute

	/**
	 * changeAttribute
	 *
	 * change an attribute
	 *
	 *@param string $username
	 *@param array $array
	 *@return mixed
	 */
	public function changeAttribute($username,$array)
	{
		// array need to be keyed appropriately
		$immid = $this->getPortalAttribute('uid',$username);
		$immid = $immid[0];
		return ldap_mod_replace($this->_portal_ds, "uid=$immid, ".$this->_ldap['root'], $array);
	}//end changeAtttribute

	/**
	 * changeName
	 *
	 * update a users name in LDAP
	 *
	 *@param string $username
	 *@param string $first_name
	 *@param string $last_name
	 *@return mixed
	 */
	public function changeName($username, $first_name, $last_name)
	{
		$first_name = trim($first_name);
		$last_name = trim($last_name);

		// sn, cn, displayName, givenName
		$array = array();

		$array['givenName'][]=$first_name;
		$array['sn'][]=$last_name;

		$array['displayName'][]=$first_name.' '.$last_name;
		$array['cn'][]=$first_name.' '.$last_name;

		return $this->changeAttribute($username, $array);
	}//end changeName

	/**
	 * checkNonUser
	 *
	 * check the username to see if they are a real user, or a system account
	 * 
	 *@param string $user
	 *@return boolean
	 */
	public function checkNonUser($user)
	{
		$system_users = array('lumadmin', 'system', 'usertemplate', 'fragmentTemplate', 'cpadmina');
		if(in_array($user,$system_users))
		{
			return false;
		}//end if

		// layout owners have -lo in their username, no other usernames have a '-'
		if(strpos($user, '-lo')!==false)
		{
			return false;
		}//end if

		return true; // true denotes a regular username
	}//end checkNonUser

	/**
	 * connectLDAP
	 *
	 * make the connection to the LDAP
	 *
	 *@param string $which
	 *@return boolean
	 */
	public function connectLDAP($which)
	{
		// sets up the $this_ldap config vars
		$this->_initConnection($which);

		$this->_portal_ds = ldap_connect($this->_ldap['hostname']);
		if($this->_portal_ds) // check if able to connect ok
		{
			$portal_binded = ldap_bind($this->_portal_ds, $this->_ldap['username'], base64_decode($this->_ldap['password']));
			if($portal_binded)
			{
				return true;
			}//end if
		}//end if

		return false;
	}//end connectLDAP
	
	/**
	 * delAttribute
	 *
	 * delete an attribute
	 *
	 *@param string $username
	 *@param array $array
	 *@return mixed
	 */
	public function delAttribute($username, $array)
	{
		$immid = $this->getPortalAttribute('uid',$username);
		$immid = $immid[0];
		// array need to be keyed appropriately
		return ldap_mod_del($this->_portal_ds, "uid=$immid, ".$this->_ldap['root'], $array);
	}//end delAttribute

	/**
	 * deleteUser
	 *
	 * delete a user from the ldap
	 *
	 *@param string $username
	 *@param array $array
	 *@return mixed
	 */
	public function deleteUser($username)
	{
		$immid = $this->getPortalAttribute('uid',$username);
		$immid = $immid[0];
		// array need to be keyed appropriately
		$deleted = ldap_delete($this->_portal_ds, "uid=$immid, ".$this->_ldap['root']);
		if($deleted)
		{
			$GLOBALS['firephp']->log('Deleted: '.$username);
			return true;
		}//end if
		$GLOBALS['firephp']->log('Not Deleted: '.$username);
		return false;
	}//end delAttribute

	/**
	 * getAllRoles
	 *
	 * get all roles defined in the system
	 *
	 *return mixed
	 */	
	public function getAllRoles()
	{
		$data = array();

		// search the ldap for all defined roles
		$query_ldap = ldap_search($this->_portal_ds, 'ou=AccessGroups, o=plymouth.edu, o=cp','objectClass=*',array('cn'));

		$info = ldap_get_entries($this->_portal_ds, $query_ldap);

		if($info['count']>0)
		{
			for($i=0;$i<$info['count'];$i++)
			{
				if($info[$i]['count']>0)
				{
					$data[] = strtolower($info[$i]['cn'][0]);
				}//end if
			}//end for
			sort($data);
			return $data;
		}//end if

		return false;
	}//end getAliRoles

	/**
	 * getConfigSetting
	 *
	 * get a Luminis configuration setting
	 *
	 *@param string $setting
	 *@return string
	 */
	function getConfigSetting($setting)
	{ 
		// search the ldap for the config value
		$query_ldap = ldap_search($this->_portal_ds, 'ou=site,o=Luminis Configuration', 'cn='.$setting, array('pdsconfigvalue'));
		$info = ldap_get_entries($this->_portal_ds, $query_ldap);
		if($info['count']>0)
		{
			return $info[0]['pdsconfigvalue'][0];
		}//end if

		return false;
	}// end getConfigSetting

	/**
	 * getEmailSettings
	 *
	 * get the email settings for a user
	 * 
	 *@param string $username
	 *@return mixed
	 */
	public function getEmailSettings($username)
	{
		$data = array();

		$search = str_replace('*','',$search);
		$value=$type.'='.$search;


		// search the ldap for the given user
		$query_ldap = ldap_search($this->_portal_ds, $this->_ldap['root'], 'pdsLoginId='.$username, array('pdsemaildefaultaddress', 'pdsemailstockfolderaccount', 'pdsemaildefaultaccount', 'pdsemailaccount'));

		$info = ldap_get_entries($this->_portal_ds, $query_ldap);
		if($info['count']>0)
		{
			$data['pdsemaildefaultaddress'] = $info[0]['pdsemaildefaultaddress'][0];
			$data['pdsemailstockfolderaccount'] = $info[0]['pdsemailstockfolderaccount'][0];
			$data['pdsemaildefaultaccount'] = $info[0]['pdsemaildefaultaccount'][0];
			
			$data['pdsemailaccount'] = $info[0]['pdsemailaccount'];

			return $data;
		}//end if

		return false;
	}//end getEmailSettings

	/**
	 * getImmID
	 *
	 * search for an immutable id (uid)
	 *
	 * @param string $search
	 * @param string $attr
	 * @return string
	 */
	function getImmID($search,$attr='pdsLoginId')
	{
		$arr = $this->getPortalAttribute('uid',$search,$attr);
		return $arr[0];
	}//end getImmUID

	/**
	 * getpdsLoginId
	 *
	 * search for a pdsLoginId
	 *
	 * @param string $search
	 * @param string $attr
	 * @return string
	 */
	function getpdsLoginId($search,$attr='uid')
	{
		$arr = $this->getPortalAttribute('pdsloginid',$search,$attr);
		return $arr[0];
	}//end getpdsLoginId

	/**
	 * getPortalAttribute
	 *
	 * get an attribute
	 *
	 * @param          string $attr the attribute to find
	 * @param          string $search the value to search for
	 * @param          string $type the field to serch on
	 * @param          bool $limit_search
	 */
	public function getPortalAttribute($attr, $search, $type='pdsLoginId', $limit_search=true)
	{
		$data = array();

		if($limit_search)
			$search = str_replace('*','',$search);
		$value=$type.'='.$search;


		// search the ldap for the given user
		$query_ldap = ldap_search($this->_portal_ds, $this->_ldap['root'],$value,array($attr));
		$info = ldap_get_entries($this->_portal_ds, $query_ldap);
		if($info['count']>0)
		{
			//$attr = strtolower($attr); // prob. right thing to do, but will wait (djb & zbt)
			for($i=0;$i<$info[0][$attr]['count'];$i++)
			{
				$data[] = $info[0][$attr][$i];
			}//end for
			return $data;
		}//end if

		return false;
	}//end getPortalAttribute

	/**
	 * get roles for a user
	 *
	 * @deprecated Portal roles are deprecated, use IDMObject::getAllBannerRoles()
	 *
	 * @param string $search
	 * @param string $type
	 * @return array
	 */
	function getRoles($search, $type='pdsLoginId')
	{
		trigger_error( 'portal::getRoles() is deprecated, use IDMObject', E_USER_DEPRECATED);

		$person = PSUPerson::get( $search );

		// if possible, return current user's banner roles
		if( isset( $_SESSION['wp_id'] ) &&
			$person->wp_id == $_SESSION['wp_id'] &&
			isset( $_SESSION['AUTHZ'] ) &&
			isset( $_SESSION['AUTHZ']['banner'] )
		) {
			return array_values( $_SESSION['AUTHZ']['banner'] );
		}

		return PSU::get('idmobject')->getAllBannerRoles($search);
	}//end getRoles


	/**
	 * getRolesExternal
	 *
	 * get all roles that sync from Banner
	 *
	 *@return array
	 */
	function getRolesExternal()
	{
		$roles = $this->getConfigSetting('pds.roles.external');
		if($roles)
		{
			$roles = explode(',', $roles);
			array_walk($roles, create_function('&$a,$b', '$a = trim($a);'));
			return $roles;
		}
		
		return false;
	}//end getRolesExternal


	/**
	 * getTermDetail
	 *
	 * get information about a given term code
	 *
	 *@param string $term_code
	 *@return array
	 */
	public function getTermDetail($term_code)
	{
		$term = array();
		if($term_code=='')
		{
			$term['description'] = 'General WebCT';
			$term['begin_date'] = 0;
			$term['end_date'] = time();
			$term['active'] = true;
		}//end if
		else
		{
			$res=ldap_search($this->_portal_ds,str_replace('ou=People, ','',$this->_ldap['root']),'cn='.$term_code);
			$info = ldap_get_entries($this->_portal_ds, $res);
			$term['description'] = $info[0]['pdstermdescription'][0];
			$term['begin_date'] = strtotime(str_replace('.0000Z','',$info[0]['pdstermbegindate'][0]));
			$term['end_date'] = strtotime(str_replace('.0000Z','',$info[0]['pdstermenddate'][0]));
			$term['active'] = $info[0]['pdstermisactive'][0];
		}//end else
		return $term;
	}//end getTermDetail

	/**
	 * getUserInfo
	 *
	 * get info about user
	 *
	 *@param string $search
	 *@param string $type
	 *@param boolean $multi
	 *@return mixed
	 */
	public function getUserInfo($search, $type='pdsLoginId', $multi=false)
	{
		$info = array();
		$info[$type] = $search;

		if(!$multi)
			$search = str_replace('*','',$search);
		$value=$type.'='.$search;

		// search the ldap for the given user
		$query_ldap = ldap_search($this->_portal_ds, $this->_ldap['root'],$value,array('pdsRole','sn','givenName','pdsEmailDefaultAddress','pdsLoginId','title','telephonenumber','physicalDeliveryOfficeName','o','ou','postalAddress','uid','cn','pdsacademicmajor'));

		$data = ldap_get_entries($this->_portal_ds, $query_ldap);

		if($data['count']>0)
		{
			for($i=0;$i<$data['count'];$i++)
			{
				$info[$i]['username'] = $data[$i]['pdsloginid'][0];

				// do attribute cleanup as needed
				$change = array();
				$to_clean = array('telephonenumber','physicaldeliveryofficename','o','ou','postaladdress');

				foreach($to_clean as $field)
				{
					if($data[$i][$field]['count']>1)
					{
						if($data[$i][$field][0])
						{
							$change[$field]=$data[$i][$field][0];
						}//end if
						else
						{
							$change[$field]=$data[$i][$field][1];
							$data[$i][$field][0]=$data[$i][$field][1];
						}//end else
					}//end if
				}//end foreach

				if(count($change))
				{
					$this->changeAttribute($info[$i]['username'],$change);
				}//end if
				// end of attribute cleanup

				$info[$i]['immid'] = $data[$i]['uid'][0];

				$info[$i]['full_name'] = $data[$i]['cn'][0];
				$info[$i]['last_name'] = $data[$i]['sn'][0];
				$info[$i]['first_name'] = $data[$i]['givenname'][0];
				$info[$i]['email'] = $data[$i]['pdsemaildefaultaddress'][0];
				
				$info[$i]['title'] = $data[$i]['title'][0];

				preg_match('/cn\=(.*)\,ou.*/',$data[$i]['pdsacademicmajor'][0],$matches);
				$info[$i]['major'] = $matches[1];

				$info[$i]['phone'] = $data[$i]['telephonenumber'][0];
				$info[$i]['building'] = $data[$i]['physicaldeliveryofficename'][0];
				$info[$i]['location'] = $data[$i]['o'][0];
				$info[$i]['department'] = $data[$i]['ou'][0];
				$info[$i]['campus_address'] = $data[$i]['postaladdress'][0];
				$info[$i]['mail_stop'] = $data[$i]['postaladdress'][0];

				$info[$i]['type'] = 'person';

				// get role info
				$info[$i]['pdsrole'] = $data[$i]['pdsrole'];
				$info[$i]['roles'] = array();
				for($j=0;$j<$data[$i]['pdsrole']['count'];$j++)
				{
					$info[$i]['roles'][] = strtolower($data[$i]['pdsrole'][$j]);
				}//end for
			}//end for

			if($multi)
				return $info;
			else
				return $info[0];
		}//end if

		return false;
	}//end getUserInfo	

	/**
	 * searchPortalUsers
	 *
	 * search for users
	 *@param string $search
	 *@param string $attributes
	 *@return mixed
	 */
	public function searchPortalUsers($search,$attributes)
	{
		// search the ldap for the given user
		$query_ldap = ldap_search($this->_portal_ds, $this->_ldap['root'],$search,$attributes);

		$info = ldap_get_entries($this->_portal_ds, $query_ldap);

		if($info['count']>0)
		{
			return $info;
		}//end if

		return false;
	}//end searchPortalUsers

	/**
	 * _initConnection
	 *
	 * setup configuration parameters to initiate the LDAP connection
	 *
	 *@param string $which
	 */
	private function _initConnection($which)
	{
		$this->which = '_'.$which;
		
		// gets the needed configuration variables for connecting to the portal ldap
		// includes hostname, password, and username
		require_once 'PSUDatabase.class.php';
		
		if($this->which == '_prod')
		{
			$conf = PSUDatabase::connect('ldap/portal','return');
		}//end if
		else
		{
			$conf = PSUDatabase::connect('ldap/portal'.$this->which,'return');
		}//end else

		$this->_ldap = $conf;
	}//end _initConection

}//end class
?>
