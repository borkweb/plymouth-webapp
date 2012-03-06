<?php 

/**
 * IDMObject.class.php
 *
 * === Modification History ===<br/>
 * 1.0.0  18-may-2005  [mtb]  original<br/>
 * 1.0.1  10-Oct-2006  [zbt]  fixed a bug with assignPermission<br/>
 * 1.1.0  22-apr-2008  [amb]  finished updates to use new attribute schema
 *
 * @package 		IdentityManagement
 */

/**
 * IDMObject.php
 *
 * Functions related to Identity Management
 *
 * @version		1.1.0
 * @module		IDMObject.php
 * @author		Matthew Batchelder <mtbatchelder@plymouth.edu>
 * @copyright	2005, Plymouth State University, ITS
 */ 
require_once('PSUTools.class.php');

class IDMObject
{
	public $db;
	public $_identifiers_view = 'psu_identity.person_identifiers';
	public $table = array(
		'attribute_description' => 'psu_identity.attribute_description',
		'attribute_group'       => 'psu_identity.attribute_group',
		'attribute_type'        => 'psu_identity.attribute_type',
		'person_attribute'      => 'psu_identity.person_attribute',
		'person'                => 'psu_identity.person_identifiers',
		'meta'                  => 'psu_identity.attribute_meta',
		'log'                   => 'psu_identity.person_attribute_log'
	);

	// filtering constants
	const IDM_INCLUDE = 0;
	const IDM_EXCLUDE = 1;

	/**
	 * Cache of Attribute table.
	 * @name		$_attributes
	 * @since		version 1.1.0
	 */
	private $_attributes;

	/**
	 * Cache of Description table.
	 * @name    $_descriptions
	 */
	private $_descriptions;

	/**
	 * Cache of attribute_log table.
	 * @name    $_logs
	 */
	private $_logs;

	/**
	 * IDMObject constructor with db connection
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		ADOdb $db database object
	 * @return  	boolean
	 */
	public function __construct( $db = false )
	{
		$result = null;

		if($db)
		{
			$this->db = $db;
			$result = true;
		}//end if
		else
		{
			$result = $this->connect();
		}//end else

		if($result)
		{
			$this->_cacheAttributes();
		}//end if
		
		$this->_descriptions = $this->initTypeArray();
		$this->_logs = array();

		return $result;
	}//end IDMObject constructor

	/**
	 * Returns TRUE if the user has an active systems account in Active Directory.
	 */
	function ad_active( $pidm )
	{
		$sql = "SELECT ad FROM psu_identity.person_identifiers WHERE pid = :pidm";

		$active = (bool)PSU::db('idm')->GetOne($sql, array('pidm' => $pidm));

		if( PSU::db('idm')->ErrorNo() > 0 ) {
			throw new IDMException( IDMException::AD_ACTIVE_FAIL, PSU::db('idm')->ErrorMsg() );
		}

		return $active;
	}//end ad_active

	/**
	 * addAttribute
	 *
	 * Inserts a person attribute after verifying the parent exists. This function
	 * will add the parent if it is missing, and there is only one possible parent
	 * for the specified attribute.
	 *
	 * @since		version 1.1.0
	 * @access		public
	 * @param  		int $pidm Person identifier
	 * @param     int|string $type Attribute type
	 * @param     string $attribute Attribute identifier/name
	 * @param  		mixed $params Attribute data to be inserted
	 * @return  	boolean
	 */
	function addAttribute($pidm,$type,$attribute,$source,$params='')
	{
		// $params:
		//		granted_by (string)
		//		start_date (date)
		//		end_date (date)
		//		activity_date (date)
		//    reason (string)
		//		origin_id (int)
		
		if(!is_array($params))
		{
			parse_str(stripslashes($params), $params);
		}//end if

		list($type_id, $type, $custom) = $this->any2type($type);

		if($type == 'role')
		{
			$result = $this->_assignRole($pidm, $attribute, $source, $params);
			$this->db->CacheFlush();
			return $result;
		}

		// non-custom attributes must exist in the description table
		if($custom !== true)
		{
			if(!$this->getAttribute($type_id, $attribute))
			{
				throw new IDMException(IDMException::INVALID_ATTRIBUTE);
			}
		}

		// ******** Is a parent role required for this attribute?
		// ********

		$parents = $this->getParentRole($type_id, $attribute);
		$parents = $parents['role'];

		if(count($parents) > 0)
		{
			// the attribute has one or more parents. does this user have one
			// of those roles?
			$current_roles = $this->getPersonAttributes($pidm, 'role');
			$current_roles = array_keys($current_roles['role']);

			$matching_roles = array_intersect($parents, $current_roles);

			if(count($matching_roles) == 0)
			{
				// a parent was required, but not present.
				if(count($parents) == 1)
				{
					// there was a single parent. add it.
					$result = $this->_assignRole($pidm, $parents[0], $source, $params);
					if($result === false)
					{
						$this->db->FailTrans();
						throw new IDMException(IDMException::ASSIGN_ROLE_FAILED);
						return false;
					}
				}//end if
				else
				{
					// there are multiple parents. one must be added explicitly by the caller
					$this->db->FailTrans();
					throw new IDMException(IDMException::PARENT_MISSING, htmlentities($attribute));
					return false;
				}//end else
			}//end if
		}//end if

		$result = $this->_doAddAttribute($pidm, $type_id, $attribute, $source, $params);
		$this->db->CacheFlush();
		return $result;
	}//end addAttribute

	/**
	 * addDefaultChildren
	 *
	 * Add the default children for the specified role ID.
	 *
	 * @access		public
	 * @param			int $pidm the user's pidm
	 * @param			int $id the source role's id
	 * @return		array a type array of the added attributes
	 */
	function addDefaultChildren($pidm, $id, $source, $role_data='')
	{
		$role = $this->getLog($id);

		if(!is_array($role_data))
		{
			parse_str($role_data, $role_data);
		}

		list($type_id, $type) = $this->any2type($role['type_id']);

		// only proceed for roles
		if($type != 'role')
		{
			throw new IDMException(IDMException::ADD_CHILDREN_NON_ROLE, htmlentities($role['attribute']));
		}

		// makes sure attribute knows who its parent is
		$role_data['parent_id'] = $id;
		
		// loop through all children for this role and add them
		$attributes = $this->getChildAttributes($role['attribute'], 'role', self::IDM_EXCLUDE);
		foreach($attributes as $type => $subattributes)
		{
			list($type_id, $type) = $this->any2type($type);

			foreach($subattributes as $subattribute => $default)
			{
				// skip non-defaults
				if(!$default)
				{
					continue;
				}

				if($this->_doAddAttribute($pidm, $type_id, $subattribute, $source, $role_data))
				{
					$added_attributes[$type][$this->last_origin_id] = $subattribute;
				}//end if
			}
		}
	}//end addDefaultChildren

	function adminDataDictionary()
	{
		$sql = "SELECT * FROM (
		       SELECT t1.name parent_type_name,
									g.parent_type_id,
									g.parent_attribute,
									d1.name parent_name,
									d1.description parent_description,
									t2.name child_type_name,
									g.child_type_id,
									g.child_attribute,
									d2.name child_name,
									d2.description child_description,
									decode(is_default, 'Y', 'default', 'manual') manual_default
						 FROM psu_identity.attribute_group g,
									psu_identity.attribute_description d1,
									psu_identity.attribute_description d2,
									psu_identity.attribute_type t1,
									psu_identity.attribute_type t2
						WHERE g.parent_type_id = 2
							AND g.child_type_id = 1
							AND g.parent_type_id = t1.id
							AND g.child_type_id = t2.id
							AND g.parent_attribute = d1.attribute
							AND g.parent_type_id = d1.type_id
							AND g.child_attribute = d2.attribute
							AND g.child_type_id = d2.type_id
		        UNION
		       SELECT 'role' parent_type_name, 2 parent_type_id, d3.attribute parent_attribute, d3.name parent_name,
		              d3.description parent_description, null child_type_name, null child_type_id, null child_attribute, null child_name, null child_description, 'manual' manual_default
		         FROM psu_identity.attribute_description d3
		        WHERE NOT EXISTS (SELECT 1 FROM psu_identity.attribute_group g2 WHERE d3.attribute = g2.parent_attribute)
		          AND d3.type_id = 2
		              )
		     ORDER BY LOWER(parent_name), parent_type_name, LOWER(child_name), child_type_name
		";

		$data_dictionary = array();
		if($results = PSU::db('idm')->Execute($sql))
		{
			foreach($results as $row)
			{
				$p = $row['parent_attribute'];
				$c = $row['child_attribute'];
				if(!isset($data_dictionary[$p]))
				{
					$data_dictionary[$p] = array(
						'name' => $row['parent_name'],
						'attribute' => $row['parent_attribute'],
						'type' => $row['parent_type_id'],
						'type_name' => $row['parent_type_name'],
						'description' => $row['parent_description'],
						'children' => array()
					);
				}//end if

				$data_dictionary[$p]['children'][$c] = array(
					'name' => $row['child_name'],
					'attribute' => $row['child_attribute'],
					'type' => $row['child_type_id'],
					'type_name' => $row['child_type_name'],
					'description' => $row['child_description'],
					'manual_default' => $row['manual_default']
				);
			}//end foreach
		}//end if

		// what attributes can this user administer?
		$attributes = $_SESSION['AUTHZ']['admin'];
		$admin_attributes = $this->initTypeArray();
		foreach($attributes as &$attribute)
		{
			$info = $this->getAttribute($attribute['attribute']);
			list(,$type) = $this->any2type($info['type_id']);
			$admin_attributes[$type][$attribute['attribute']] = $info;
		}

		foreach($data_dictionary as $role)
		{
			if(isset($admin_attributes['role'][$role['attribute']]))
			{
				$data_dictionary[$role['attribute']]['admin'] = true;
			}//end if

			foreach($role['children'] as $perm)
			{
				if(!$data_dictionary[$role['attribute']]['admin'] && $admin_attributes[$perm['type_name']][$perm['attribute']])
				{
					$data_dictionary[$role['attribute']]['child_admin'] = true;
				}//end if

				$data_dictionary[$role['attribute']]['children'][$perm['attribute']]['admin'] = $admin_attributes[$perm['type_name']][$perm['attribute']] ? true : false;
			}//end foreach
		}//end foreach

		return $data_dictionary;
	}//end adminDataDictionary

	/**
	 * any2type
	 *
	 * Pass in a type or id, and receive the id/type pair.
	 *
	 * @param		int|string $input the type or id
	 * @return		array an array containing the id, then the type name
	 */
	function any2type($input)
	{
		if(ctype_digit($input))
		{
			$id = $input;
			$type = $this->_attributes[$input]['name']; 
		}//end if
		else
		{
			$type = $input;
			$id = $this->getAttributeId($type);
		}//end else

		// was this a valid attribute id?
		if(!isset($this->_attributes[$id]))
		{
			//throw new IDMException(IDMException::INVALID_TYPE, htmlentities($input));
		}

		$custom = $this->_attributes[$id]['custom_value'] == null ? false : true;

		return array($id, $type, $custom);
	}//end any2type

	/**
	 * authN
	 *
	 * Calls authentication script
	 *
	 * @since		version 1.0.1
	 * @access		public
	 * @param  		boolean $common_override override 
	 * @return  	string
	 */
	static function authN($params = '')
	{
		$default_params = array(
			'host' => null,
			'dir' => 'cas/',
			'authz' => true
		);
	
		if(!is_array($params))
		{
			parse_str($params, $params);
		}//end if
		
		$params = array_merge($default_params, $params);

		if( $params['host'] == false ) {
			$params['host'] = PSU::isdev() ? 'connect.dev' : 'connect';
		}

		if(!$_SESSION['username'] || isset($_REQUEST['logout']))
		{
			self::setupCAS($params['host'],$params['dir']);

			// TODO: handle certs
			phpCAS::setNoCasServerValidation();
		
			// check CAS authentication
			phpCAS::forceAuthentication();
		
	    // logout if desired. redirects + destroys session, then exits.
		  if (isset($_REQUEST['logout'])) {
			  phpCAS::logout($_REQUEST['logout']);
			}

			// at this step, the user has been authenticated by the CAS server
			// and the user's login name can be read with phpCAS::getUser().
			$_SESSION['username'] = $username = phpCAS::getUser();
		}
		else
		{
			$username = $_SESSION['username'];
		}//end else

		if( 'dlmf_' === substr($_SESSION['username'], 0, 5) ||
			'-lo' === substr($_SESSION['username'], -3) ||
			'training0' === substr($_SESSION['username'], 0, 9) ||
			'lumadmin' === $_SESSION['username'] ) {
			$_SESSION['username'] = $username = 'djbramer';
		} elseif ( 'guest' === $_SESSION['username'] ) {
			$_SESSION['username'] = $username = 'j_pseudo';
		}

	  if($params['authz'])
	  {
			if($username)
			{
				// login from a non-banner user
				if( PSU::is_wpid($username) ) {
					if($pidm = PSU::get('idmobject')->getIdentifier($username, 'wp_id','pid')) {
						$_SESSION['pidm'] = $pidm;
						PSU::get('idmobject')->loadAuthZ($pidm);
					}//end if

					$_SESSION['wp_id'] = $username;
				}

				elseif($pidm = PSU::get('idmobject')->getIdentifier($username, 'login_name','pid'))
				{
					$_SESSION['pidm'] = $pidm;
					PSU::get('idmobject')->loadAuthZ($pidm);

					if($wp_id = PSU::get('idmobject')->getIdentifier($username, 'login_name', 'wp_id'))
					{
						$_SESSION['wp_id'] = $wp_id;
					}//end if
				}//end if
			}//end if
		}//end if
		
	  return $username;
	}//end authN
	
	/**
	 *
	 */
	static function authZ($type, $attribute='')
	{
		if(!$_SESSION['AUTHZ'] && $_SESSION['pidm'])
		{
			$_SESSION['AUTHZ'] = PSU::get('idmobject')->loadAuthZ($_SESSION['pidm']);
		}//end if	
		
		if($type == 'all')
		{
			// user might not be logged in
			return isset($_SESSION['AUTHZ']) ? $_SESSION['AUTHZ'] : array();
		}//end if
		else
		{
			return isset($_SESSION['AUTHZ'][$type][$attribute]);
		}//end else
	}//end authZ

	/**
	 * checkCAS
	 *
	 * checks if a CAS Session is established, without forcing authentication
	 *
	 * @since               version 1.0.1
	 * @access              public
	 * @return      string/boolean
	 */
	static function checkCAS($host='my', $dir='cas/')
	{
		self::setupCAS($host,$dir);
	
		// check CAS authentication
		$auth = phpCAS::checkAuthentication();
	
		if($auth)
			return phpCAS::getUser();
		else
			return false;
	}//end checkCAS

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
	function connect($connection='idm')
	{
		$this->db = PSU::db($connection);
		return $this->db->IsConnected();
	}//end connect

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
		return $this->db->GetOne("SELECT count(*) FROM {$this->table['person']} WHERE $field LIKE '$criteria'");
	}//end countUsers

	/**
	 * countUsersByAttribute
	 *
	 * Users count based on role
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $attr_value Attribute value
	 * @return  	int
	 */
	function countUsersByAttribute($attr_value)
	{	
		//retrieve user count
		return $this->db->GetOne("SELECT count(*) FROM {$this->table['person_attribute']} WHERE attribute=:attribute", array('attribute' => $attr_value));
	}//end countUsersByRole
	
	function createBlankGobtpac($pidm='',$source='Script',$role='')
	{
		$pin = PSU::password();
		$hashed_pin = $this->encryptPin($pin);

		if($pidm)
		{
			//generate gobtpac records for population
			$sql="INSERT INTO gobtpac (
				gobtpac_pidm,
				gobtpac_pin_disabled_ind,
				gobtpac_usage_accept_ind,
				gobtpac_activity_date,
				gobtpac_user,
				gobtpac_pin,
				gobtpac_salt
			) VALUES (
				$pidm,
				'N',
				'Y',
				sysdate,
				'$source',
				'".$hashed_pin['hash']."',
				'".$hashed_pin['salt']."'
			)";
			$this->db->Execute($sql);
		}//end if
		else
		{
			//generate gobtpac records for population
			$sql="INSERT INTO gobtpac (
				gobtpac_pidm,
				gobtpac_pin_disabled_ind,
				gobtpac_usage_accept_ind,
				gobtpac_activity_date,
				gobtpac_user,
				gobtpac_pin,
				gobtpac_salt
			) (
				SELECT gorirol_pidm,
				'N',
				'Y',
				sysdate,
				'$source',
				'".$hashed_pin['hash']."',
				'".$hashed_pin['salt']."'
				FROM gorirol
				WHERE gorirol_role='$role'
					AND not exists(SELECT 1 FROM gobtpac WHERE gobtpac_pidm = gorirol_pidm)
			)";
			$this->db->Execute($sql);
		}//end else
	}//end createBlankGobtpac

	/**
	 * debugLDI
	 *
	 * Returns LDI debugging information.
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param		string $username
	 * @return		array
	 */
	function debugLDI($username)
	{
		return $this->db->GetOne("SELECT nvl(psu.f_debug_ldi('$username'),'ZOMG No Record Found...Sure this is a valid username, n00b?') FROM dual");
	}//end debugLDI

	/**
	 * Encrypts a pin using the banner encrypting logic
	 */
	function encryptPin($pin, $out = 'full', $salt = null)
	{
		if( !$salt ) {
			$sql = "
				DECLARE
						lv_hashed_pin     gobtpac.gobtpac_PIN%TYPE;
						lv_salt           gobtpac.gobtpac_SALT%TYPE;
				BEGIN
					lv_salt := gspcrpt.f_get_salt(length(:pin));
					:salt := lv_salt;
				END;
			";
			$stmt=PSU::db('banner')->PrepareSP($sql);
			PSU::db('banner')->InParameter($stmt,$pin,'pin');
			PSU::db('banner')->OutParameter($stmt,$salt,'salt');
			PSU::db('banner')->Execute($stmt);
		}//end if

		$sql = "
			DECLARE
		      lv_hashed_pin     gobtpac.gobtpac_PIN%TYPE;
		      lv_salt           gobtpac.gobtpac_SALT%TYPE;
			BEGIN
				gspcrpt.p_saltedhash(:pin,:salt,:hashed_pin);
			END;
		";
		$stmt=PSU::db('banner')->PrepareSP($sql);
		PSU::db('banner')->InParameter($stmt,$pin,'pin');
		PSU::db('banner')->InParameter($stmt,$salt,'salt');
		PSU::db('banner')->OutParameter($stmt,$hashed_pin,'hashed_pin');
		PSU::db('banner')->Execute($stmt);

		switch($out)
		{
			case 'pin': return $hashed_pin; break;
			case 'salt': return $salt; break;
			default: return array('hash' => $hashed_pin, 'salt' => $salt); break;
		}//end switch
	}//end encryptPin

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
		ini_set('memory_limit', '-1');

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
							FROM {$this->table['person']} WHERE $field LIKE '$criteria' ORDER BY last_name,first_name,middle_name";

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

		if($result=$this->db->Execute($query))
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
		$stmt=$this->db->PrepareSP($sql);
		return $this->db->Execute($stmt);
	}//end generateUsernamesByRole

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
		if($results = $this->db->ExecuteCursor($sql, 'cursorvar', array('sqpr_code'=>'ELEARNING','icsn_code'=>'ACTIVE_TERM')))
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
	 * Get all active users, as defined in v_account_active.
	 */
	function getActiveUsers($return = 'pidm, username')
	{
		$sql = "SELECT $return FROM v_account_active";
		if(ctype_alpha($return))
		{
			return $this->db->GetCol($sql);
		}
		else
		{
			return $this->db->GetAll($sql);
		}
	}

	/**
	 * getAllBannerRoles
	 *
	 * Retrieve a user's Banner roles
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		int $pidm Person identifier
	 * @return  	array
	 */
	function getAllBannerRoles($pidm) {

		$sql = "SELECT gorirol_role
			        FROM gorirol
						 WHERE gorirol_pidm = :pidm";

		if( $roles = PSU::db('idm')->GetCol( $sql, array('pidm' => $pidm) ) ) {
			foreach($roles as &$role ) {
				$role = strtolower($role);
			}//end foreach 
		}//end if

		return $roles;
	}//end getAllBannerRoles

	/**
	 * getAllOracleRoles
	 *
	 * Retrieve a user's Oracle roles
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		int $pidm Person identifier
	 * @return  	array
	 */
	function getAllOracleRoles($pidm)
	{
		// wpid has no oracle roles
		if( PSU::is_wpid($pidm) ) {
			return array();
		}

		$roles = array();
		$sql="SELECT granted_role FROM  dba_role_privs,gobtpac WHERE lower(grantee) = lower(gobtpac_external_user) AND gobtpac_pidm = :pidm";
		if($result = $this->db->Execute($sql, array('pidm' => $pidm)))
		{
			while($row = $result->FetchRow())
			{
				$roles[strtolower($row['granted_role'])] = strtolower($row['granted_role']);
			}//end while
			return $roles;
		}//end if
		return false;
	}//end getAllOracleRoles

	/**
	 * getAttribute
	 *
	 * Get an attribute from the description table.
	 *
	 * @param		string $attribute the attribute name
	 */
	function getAttribute($attribute, $old_attribute_arg = false)
	{
		if($old_attribute_arg !== false)
		{
			$attribute = $old_attribute_arg;
		}

		// fetch into cache
		if(!isset($this->_descriptions[$type_id][$attribute]))
		{
			$sql = "SELECT *
								FROM {$this->table['attribute_description']}
							 WHERE attribute = :attribute";
			
			$this->_descriptions[$type_id][$attribute] = $this->db->CacheGetRow($sql, array('attribute' => $attribute));
		}

		return $this->_descriptions[$type_id][$attribute];
	}//end getAttribute

	/**
	 * getAttributeMeta
	 *
	 * Fetch the meta values for an attribute.
	 *
	 * @param		int|string $type the type as a string or id
	 * @param		string $attribute the attribute name
	 * @return		string the matching meta tag
	 */
	function getAttributeMeta($type, $attribute)
	{
		if(is_numeric($type))
		{
			$type_id = $type;
		}//end if
		else
		{
			list($type_id, $type) = $this->any2type($type);
		}//end else
	
		$sql = "SELECT meta
							FROM {$this->table['meta']}
						 WHERE type_id = :type_id 
							 AND attribute = :attribute";

		return $this->db->CacheGetAll($sql, array('type_id' => $type_id, 'attribute' => $attribute));
	}//end getAttributeMeta

	/**
	 * getAttributesByMeta
	 *
	 * Fetch all attributes that match a specified meta.
	 *
	 * @param		$meta the meta description to match
	 * @return		a type array containing the matched attributes
	 */
	function getAttributesByMeta($meta)
	{
		$data = $this->initTypeArray();

		$sql = "SELECT m.attribute, t.name AS type_name
							FROM {$this->table['meta']} m, 
									 {$this->table['attribute_type']} t
							WHERE m.meta = :meta 
								AND m.type_id = t.id";

		if($result = $this->db->CacheExecute($sql, array('meta' => $meta)))
		{
			// massage the data
			while($row = $result->FetchRow())
			{
				$data[$row['type_name']][] = $row['attribute'];
			}//end while
			return $data;
		}//end if
		
		return false;
	}//end getAttributesByMeta

	/**
	 * Get the type id of an attribute.
	 */
	function getAttributeType($attribute)
	{
		return $this->db->CacheGetOne("SELECT type_id FROM {$this->table['attribute_description']} WHERE attribute = :attribute", array('attribute' => $attribute));
	}

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
	function getIdentifier($input_id, $input_type, $output_type, $table_query = true)
	{
		/*
			types:
				login_name
				pid
				psu_id
				username
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

		if(isset($this)) {
			$db = $this->db;
		} else {
			$idm = new IDMObject();
			$db = $idm->db;
		}//end if

		$where_sql = '';
		$view = 'psu_identity.person_identifiers';
		$sql = "SELECT * FROM {$view} WHERE ";

		if($input_type=='login_name' || $input_type=='username') {
			$where_sql = 'login_name = LOWER(:value) UNION '.$sql.' username = LOWER(:value)';
			$input_id = strtoupper($input_id);
		} else {
			$where_sql = "$input_type = :value";
		}

		$output_id = $db->GetRow($sql.$where_sql, array('value' => $input_id));

		if( ! $output_id ) {
			return false;
		}

		if($output_type=='all' || $output_type=='*') {
			return $output_id;
		}//end if

		return $output_id[$output_type];
	}//end getIdentifier

	/**
	 * getLog
	 *
	 * Retrieve an attribute from the log by id number.
	 *
	 * @access		public
	 * @param			int $pidm the person identifier
	 * @param			int $id the attribute id number
	 */
	function getLog($id)
	{
		$id = (int)$id;

		// if possible, get from the cache
		if(!array_key_exists($id, $this->_logs))
		{
			$sql = "
				SELECT {$this->table['log']}.*,
							 TO_CHAR(start_date, 'YYYY-MM-DD') start_date_ymd,
							 TO_CHAR(end_date, 'YYYY-MM-DD') end_date_ymd
					FROM {$this->table['log']}
				 WHERE id = :id
			";

			if(! $d = $this->db->GetRow($sql, array('id' => $id)))
			{
				throw new IDMException(IDMException::BAD_LOG_ID, $id);
			}

			$d['start_date'] = PSUTools::translateDateString($d['start_date_ymd']);
			$d['end_date'] = PSUTools::translateDateString($d['end_date_ymd']);

			$this->_logs[$id] = $d;
		}

		return $this->_logs[$id];
	}//end getLog

	/**
	 * getLogs
	 *
	 * Retrieve all attribute from the log by pidm and some where statement
	 *
	 * @access		public
	 * @param			int $pidm the person identifier
	 * @param			string $where the where statement
	 */
	function getLogs($pidm, $where = '')
	{
		// array to hold our massaged query set
		$data = $this->initTypeArray();
		
		$sql = "
			SELECT *
			  FROM {$this->table['log']}
			 WHERE pidm = :pidm ".(($where)?" AND (".$where.")":"")."
       ORDER BY origin_id DESC
		";

		$result = $this->db->GetAll($sql, array('pidm' => $pidm));
		if($result === false)
		{
			throw new IDMException(IDMException::SQL_ERROR, htmlentities($sql));
		}//end if

		// sql succeeded, update
		foreach($result as $row)
		{
			$attribute_type = $this->getAttributeName($row['type_id']);
			$row['start_date'] = strtotime($row['start_date']);
			$row['end_date'] = $row['end_date'] === null ? null : strtotime($row['end_date']);
			$data[$attribute_type][$row['attribute']][$row['id']] = $row;
			unset($data[$row['attribute']]['attribute']);
		}//end foreach

		return $data;
	}//end getLogs

	/**
	 * getLogChildren
	 *
	 * Return log entries by origin_id.
	 *
	 * @param      int $pidm owner pidm
	 * @param      int $id the origin_id
	 */
	function getLogChildren($id)
	{
		$sql = "
			SELECT *
			  FROM {$this->table['log']}
		 	 WHERE origin_id = :origin_id
		";

		$result = $this->db->GetAll($sql, array('origin_id' => $id));
		$return = array();
		while($row = array_pop($result))
		{
			$return[$row['id']] = $row;
		}

		return $return;
	}

	/**
	 * getLogChildrenByParent
	 * Return log entries by origin_id.
	 *
	 * @param      int $pidm owner pidm
	 * @param      int $id the origin_id
	 */
	function getLogChildrenByParent($parent_id)
	{
		$sql = "
			SELECT *
			  FROM {$this->table['log']}
		 	 WHERE parent_id = :parent_id
		";

		$result = $this->db->GetAll($sql, array('parent_id' => $parent_id));
		$return = array();
		while($row = array_pop($result))
		{
			$return[$row['id']] = $row;
		}

		return $return;
	}

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

		$name_row=$this->db->GetRow("SELECT first_name,middle_name,last_name,name_prefix,name_suffix FROM {$this->_identifiers_view} WHERE pid=:pidm", array('pidm' => $pid));

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

	/**
	 * getAttributeId
	 *
	 * Fetch the attribute ID for a named attribute.
	 *
	 * @access      public
	 * @since       version 1.1.0
	 * @param       string $find_attr the name or description of the attribute
	 * @return      int|bool numeric id of the attribute, or false if no match
	 */
	function getAttributeId($find_attr)
	{
		// for simplicity, allow the calling function to call us without type checking
		if(is_int($find_attr))
		{
			return $find_attr;
		}//end if

		foreach($this->_attributes as $attribute)
		{
			if($attribute['name'] == $find_attr || $attribute['description'] == $find_attr)
			{
				return (int)$attribute['id'];
			}//end if
		}//end foreach

		return false;
	}//end getAttributeId

	/**
	 * getAttributeName
	 *
	 * Fetch the attribute name for a specified attribute.
	 *
	 * @access		public
	 * @since		version 1.1.0
	 * @param		$find_attr the id of the attribute
	 * @return		int|bool name of the attribute, or false if no match
	 */
	function getAttributeName($find_attr)
	{
		return $this->_attributes[$find_attr]['name'];
	}//end getAttributeName

	/**
	 * getAttributes
	 *
	 * Retrieve the hierarchy of attributes related to a specified attribute,
	 * including all children (excluding roles) and the children of all parents
	 * (again, excluding roles). Useful when determining if a role or its parent(s)
	 * includes another role, ie. does any HR classification allow mailing_list.
	 *
	 * @since		version 1.0.0
	 * @param  		int $attribute Attribute identfier
	 * @return  	array
	 */
	function getAttributes($attribute)
	{
		$attributes = array();

		$roles = $this->getRoleCollection($attribute);

		// add non-role children for each role in the hierarchy
		foreach($roles as $role)
		{
			$role_attributes = $this->getChildAttributes($role, 'role');

			foreach($role_attributes as $type => $subattributes)
			{
				if(!isset($attributes[$type]))
				{
					// didn't have this key, just add it
					$attributes[$type] = $subattributes;
				}//end if
				else
				{
					// had this $type, update subattributes
					foreach($subattributes as $subattribute => $default)
					{
						if($default || !isset($attributes[$type][$subattribute]))
						{
							$attributes[$type][$subattribute] = $default;
						}//end if
					}//end foreach
				}//end else
			}//end foreach
		}//end foreach

		return $attributes;

		// initialize list that will hold condensed attribute list
		$final_attributes = $this->initTypeArray();

		// remove duplicates from attribute lists
		foreach($attributes as $type => $list)
		{
			$seen = array(); // maintains a list of seen attributes
			$index = -1; // used to maintain consistent indexes between $seen and $final_attributes

			// loop over all attributes
			foreach($list as $attribute)
			{
				if(!isset($seen[$attribute['attribute']]))
				{
					// was not in the array, so add it
					$seen[$attribute['attribute']] = ++$index;
					$final_attributes[$type][$index] = $attribute;
				}//end if
				elseif($attribute['default'] == true)
				{
					$search_index = $seen[$attribute['attribute']];
					$final_attributes[$type][$search_index]['default'] = true;
				}//end elseif

				// condition "seen, but this default is false" is not handled
			}//end foreach
		}//end foreach

		return $final_attributes;
	}//end getAttributes

	/**
	 * getAttributesWhere
	 *
	 * Get attributes matching a specific clause.
	 *
	 * @access      public
	 * @param       string $where the where clause
	 */
	function getAttributesWhere($where_sql)
	{
		$sql = "SELECT *
		          FROM {$this->table['attribute_description']}
		         WHERE $where_sql
		";

		$result = $this->db->Execute($sql);

		if($result === false)
		{
			throw new IDMException(IDMException::SQL_ERROR);
		}

		return $result->GetRows();
	}

	/**
	 * getChildAttributes
	 *
	 * Retrieve a list of attributes of a given type by parent OR all attributes by parent
	 *
	 * @since         version 1.0.0
	 * @access        public
	 * @param         string $parent the parent attribute
	 * @param         array $attributes the list
	 * @param         boolean $filter_type whether the specified attributes should be included, or excluded (the default). use IDM_EXCLUDE and IDM_INCLUDE
	 * @return        array
	 */
	function getChildAttributes($parent, $attributes='', $filter_type=self::IDM_EXCLUDE)
	{
		$data = $this->initTypeArray();

		$args = compact('parent');

		// ensure attributes is an array. could be a single attribute, or a comma-separated list
		if(!is_array($attributes) && $attributes != '')
		{
			$attributes = explode(',', $attributes);
		}//end if
		
		// if necessary, generate the sql for including/excluding attributes types
		$attributes_sql = '';
		if(is_array($attributes))
		{
			foreach($attributes as $key => $value)
			{
				$value = trim($value);
				$attributes[$key] = $this->getAttributeId($value);
			}//end foreach

			if($filter_type == self::IDM_EXCLUDE)
			{
				$not_sql = 'NOT';
			}// end if
			else
			{
				$not_sql = '';
			}//end else

			$attributes_sql = "AND g.child_type_id $not_sql IN (" . implode(',', $attributes) . ")";
		}//end if

		$sql = "SELECT g.*
					FROM {$this->table['attribute_group']} g
				 WHERE parent_attribute = :parent
							 $attributes_sql
				 ORDER BY g.child_attribute";

		if($results = $this->db->CacheExecute($sql, $args))
		{
			while($row = $results->FetchRow())
			{
				$child_type_name = $this->getAttributeName($row['child_type_id']);

				$child_attribute= $row['child_attribute'];
				$is_default = ($row['is_default'] == 'Y') ? true : false;
				
				if(!isset($data[$child_type_name][$child_attribute]) || $is_default == true)
				{
					// wasn't present, or can be updated safely (not overwriting a default)
					$data[$child_type_name][$child_attribute] = $is_default;
				}//end if
			}//end while
		}//end if	
		
		return $data;
	}//end getChildAttributes

	/**
	 * getParentRole
	 *
	 * Find the parent role for an attribute.
	 *
	 * @param		string $attribute the attribute name
	 * @return		string the parent role name
	 */
	function getParentRole($type, $attribute)
	{
		list($type_id, $type) = $this->any2type($type);

		$parent_type_id = $this->getAttributeId('role');

		$data = $this->initTypeArray();

		$sql = "SELECT parent_attribute
							FROM {$this->table['attribute_group']}
						 WHERE parent_type_id = :parent_type_id 
							 AND child_attribute = :child_attribute
		           AND child_type_id = :type_id";

		if($results = $this->db->CacheExecute($sql, array('parent_type_id' => $parent_type_id, 'child_attribute' => $attribute, 'type_id' => $type_id)))
		{
			while($row = $results->FetchRow())
			{
				$data['role'][] = $row['parent_attribute'];
			}//end while
			return $data;
		}//end if

		return false;
	}//end getParentRole

	/**
	 * getPersonAttribute
	 *
	 * Get a single attribute for a person.
	 *
	 * @since			version 1.1.1
	 * @access		public
	 * @param			int $pidm Person identifier
	 * @param			int $type Attribute type
	 * @param			int $attribute Attribute identifier/name
	 * @param			bool $current whether or not only current attributes should be returned
	 */
	function getPersonAttribute($pidm, $type, $attribute, $current = true)
	{
		$data = $this->initTypeArray(); // what we'll be returning

		list($type_id, $type) = $this->any2type($type);

		$current_sql = '';
		if($current)
		{
			$current_sql = 'AND sysdate >= start_date AND (sysdate <= (end_date+1) OR end_date IS NULL)';
		}//end if

		$sql = "SELECT *
							FROM {$this->table['person_attribute']}
						 WHERE pidm = :pidm 
							 AND type_id = :type_id 
							 AND attribute = :attribute
									$current_sql";

		if($row = $this->db->GetRow($sql, array('pidm' => $pidm, 'type_id' => $type_id, 'attribute' => $attribute)))
		{
			$row['start_date'] = strtotime($row['start_date']);
			$row['activity_date'] = strtotime($row['activity_date']);
			$row['end_date'] = ($row['end_date'] == null) ? null : strtotime($row['end_date']);

			$data[$type][$row['attribute']] = $row;
		}//end if

		return $data;
	}//end getPersonAttribute

	/**
	 * getPersonAttributes
	 *
	 * Get attributes assigned to a specific person.
	 *
	 * @since		version 1.1.1
	 * @access		public
	 * @param		int $pidm the person's pid
	 * @param		int|string $attribute optional. an attribute to filter for
	 * @param		int $filter_type include or exclude $attribute. Use IDM_INCLUDE or IDM_EXCLUDE
	 * @param		boolean $current whether or not only currently active attributes should be returned
	 */
	function getPersonAttributes($pidm, $attribute=false, $filter_type=self::IDM_INCLUDE, $current = true)
	{
		// wpid-only has not attributes
		if( PSU::is_wpid($pidm) ) {
			return array();
		}

		// setup possible where clause to filter on attributes
		$attr_sql = '';
		if($attribute)
		{
			if($filter_type == self::IDM_INCLUDE)
			{
				$filter = '=';
			}//end if
			else
			{
				$filter = '!=';
			}//end else
			$attribute = $this->getAttributeId($attribute);
			$attr_sql = "AND type_id $filter $attribute";
		}//end if

		$current_sql = '';
		if($current)
		{
			$current_sql = 'AND sysdate >= start_date AND (sysdate <= (end_date+1) OR end_date IS NULL)';
		}//end if

		// build the sql statement
		$sql = "SELECT *
							FROM {$this->table['person_attribute']}
						 WHERE 1=1
									 $current_sql
									 $attr_sql
									 AND pidm = :pidm";

		// array to hold our massaged query set
		$data = $this->initTypeArray();
		
		$result = $this->db->GetAll($sql, array('pidm' => $pidm));
		if($result === false)
		{
			return false;
		}//end if

		// sql succeeded, update
		foreach($result as $row)
		{
			$attribute_type = $this->getAttributeName($row['type_id']);
			$data[$attribute_type][$row['attribute']] = $row;
			unset($data[$row['attribute']]['attribute']);
		}//end foreach

		return $data;
	}//end getPersonAttributes

	/**
	 * getPersonAttributesByMeta
	 *
	 * Get attributes assigned to a specific person that have a specific meta value
	 *
	 * @since		version 1.1.1
	 * @access		public
	 * @param		int $pidm the person's pid
	 * @param		int|string $attribute optional. an attribute to filter for
	 * @param		int $filter_type include or exclude $attribute. Use IDM_INCLUDE or IDM_EXCLUDE
	 * @param		boolean $current whether or not only currently active attributes should be returned
	 */
	function getPersonAttributesByMeta($pidm, $meta, $attribute=false, $filter_type=self::IDM_INCLUDE, $current = true)
	{
		// setup possible where clause to filter on attributes
		$attr_sql = '';
		if($attribute)
		{
			if($filter_type == self::IDM_INCLUDE)
			{
				$filter = '=';
			}//end if
			else
			{
				$filter = '!=';
			}//end else
			$attribute = $this->getAttributeId($attribute);
			$attr_sql = "AND a.type_id $filter $attribute";
		}//end if

		$current_sql = '';
		if($current)
		{
			$current_sql = 'AND sysdate >= a.start_date AND (sysdate <= (a.end_date+1) OR a.end_date IS NULL)';
		}//end if

		// build the sql statement
		$sql = "SELECT *
							FROM {$this->table['person_attribute']} a,
							     {$this->table['meta']} m
						 WHERE m.type_id = a.type_id
						   AND m.attribute = a.attribute
									 $current_sql
									 $attr_sql
							 AND a.pidm = :pidm
							 AND m.meta = :meta";
		// array to hold our massaged query set
		$data = $this->initTypeArray();
		
		$result = $this->db->GetAll($sql, array('pidm' => $pidm, 'meta' => $meta));
		if($result === false)
		{
			return false;
		}//end if

		// sql succeeded, update
		foreach($result as $row)
		{
			$attribute_type = $this->getAttributeName($row['type_id']);
			$data[$attribute_type][$row['attribute']] = $row;
			unset($data[$row['attribute']]['attribute']);
		}//end foreach

		return $data;
	}//end getPersonAttributesByMeta

	/**
	 * getRoleChildren
	 *
	 * Recursively get all child roles for a given role.
	 *
	 * @param		$role the target role
	 */
	function getRoleChildren($role)
	{
		$child_roles = $this->getChildAttributes($role, 'role', self::IDM_INCLUDE);

		// no sub-roles
		if(!isset($child_roles['role']))
		{
			return array();
		}//end if

		$child_roles = array_keys($child_roles['role']);

		$final_roles = $child_roles;
		foreach($child_roles as $child_role)
		{
			$final_roles = array_merge($final_roles, $this->getRoleChildren($child_role));
		}//end foreach

		return $final_roles;
	}//end getRoleChildren

	/**
	 * getRoleCollection
	 *
	 * Gets all parent roles for a given role
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $role Role code
	 * @param		  array  $gathered_roles Role collection
	 * @return  	array
	 */
	function getRoleCollection($role)
	{
		$roles = array($role);
		$role_id = $this->getAttributeId('role');
		
		$sql = "SELECT parent_attribute
							FROM {$this->table['attribute_group']}
						 WHERE child_type_id = :child_type_id 
							 AND parent_type_id = :parent_type_id 
							 AND child_attribute = :child_attribute";

		if($results = $this->db->CacheExecute($sql, array('child_type_id' => $role_id, 'parent_type_id' => $role_id, 'child_attribute' => $role)))
		{
			while($row = $results->FetchRow())
			{
				$roles = array_merge($roles, $this->getRoleCollection($row['parent_attribute']));
			}//end while
		}//end if

		return $roles;
	}//end getRoleCollection
	
	function getRoleDescriptions()
	{
		$role_descriptions = array();
		$sql = "SELECT domain, code, description FROM role_desc";
		if($results = PSU::db('myplymouth')->Execute($sql))
		{
			while($row = $results->FetchRow())
			{
				$role_descriptions[$row['domain']][$row['code']] = $row['description'];
			}//end while
		}//edn if
		return $role_descriptions;
	}//end getRoleDescriptions

	/**
	 * getRoles
	 *
	 * Return a list of all roles.
	 *
	 * @access       public
	 */
	function getRoles()
	{
		$roles = array();
		$role_id = $this->getAttributeId('role');

		$sql = "SELECT *
		          FROM {$this->table['attribute_description']}
		         WHERE type_id = :type_id";
		
		$results = $this->db->CacheExecute($sql, array('type_id' => $role_id));

		while($row = $results->FetchRow())
		{
			$roles[$row['attribute']] = $row;
		}

		uasort($roles, create_function('$a, $b', 'return strcasecmp($a["name"], $b["name"]);'));

		return($roles);
	}

	/**
	 * Return the URL that would force the active user to relogin to the
	 * current page (for example, if the page is targeted at a specific user.)
	 */
	public static function getServerReloginURL() {
		global $PHPCAS_CLIENT;
		self::setupCAS();

		// getServerLogin( $gateway = false, $renew = false )
		// Force a renew. Unfortunately, this is not exposed through phpCAS,
		// and we have to access the global client directly.
		return $PHPCAS_CLIENT->getServerLoginURL(false, true);
	}

	/**
	 * Return users matching a specified role in Banner.
	 *
	 * @since		version 1.1.0
	 * @param		string $role the role name
	 * @return 	array a list of pidms which have that role
	 */
	function getUsersByBannerRole($role, $return = 'pid')
	{
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

		if(is_array($role))
		{
			foreach($role as &$r)
			{
				$r = $this->db->qstr($r);
			}
			$role = implode(",", $role);
		}
		else
		{
			$role = $this->db->qstr($role);
		}

		$role = strtoupper($role);

		$sql = "SELECT $return FROM gorirol, {$this->table['person']} p WHERE gorirol_role IN ($role) AND pid=gorirol_pidm";
		$data = array();
		
		if($results = $this->db->Execute($sql))
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
	 * getUsersByOracleRole
	 *
	 * Return users matching a specified role in Oracle.
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param		string $role the role name
	 * @return 	array a list of pidms which have that role
	 */
	function getUsersByOracleRole($role, $return = 'pid')
	{
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

		if(is_array($role))
		{
			foreach($role as &$r)
			{
				$r = $this->db->qstr($r);
			}
			$role = implode(",", $role);
		}
		else
		{
			$role = $this->db->qstr($role);
		}

		$role = strtolower($role);
		$sql = "SELECT $return FROM dba_role_privs, {$this->table['person']} p WHERE lower(granted_role) IN ($role) AND lower(p.username) = lower(grantee)";
		$data = array();
		
		if($results = $this->db->Execute($sql))
		{
			while($row = $results->FetchRow())
			{
				$row = array_change_key_case($row,CASE_LOWER);
				$data[]=$row[$return];
			}//end while
		}//end if
		
		return $data;
	}//end getUsersByOracleRole

	/**
	 * getUsersByAttribute
	 *
	 * retrieves users that have the given attribute
	 *
	 * @since       version 1.0.0
	 * @access      public
	 * @param       mixed $attribute Attribute array (id,value)
	 * @param       string $attribute_value Attribute Value
	 * @param       string $return What to return: pidm or full (full returns name, id, and pidm)
	 * @param       boolean $only_manual Return only manually assigned attributes?
	 * @return      array
	 */
	function getUsersByAttribute($attributes,$and_or='OR',$return='full',$only_manual=false)
	{
		$data=array();
		
		if($attributes['pa.type_id'])
		{
			$attributes = array($attributes);
		}//end if

		$attr_where = array();
		foreach($attributes as $attribute)
		{
			$this_sql = array();
			foreach($attribute as $key => $value)
			{
				if(is_numeric($key) || $key == 'sql')
				{
					$this_sql[] = $value;
				}//end if
				elseif(is_null($value))
				{
					$this_sql[] = "$key IS NULL";
				}
				else
				{
					$value = $this->db->qstr($value);
					$this_sql[] = "$key = $value";
				}//end else
			}
			$this_sql = implode(' AND ', $this_sql);
			$attr_where[] = "($this_sql)";
			unset($this_sql);
		}//end foreach
		$attr_where = implode($and_or, $attr_where);
		
		if($only_manual)
		{
			$manual=" AND a.manual IS NOT NULL";
		}
		else
		{
			$manual = '';
		}

		$what_sql = "i.pid";
		if($return == 'full')
		{
			$what_sql .= ",i.psu_id,i.username,i.first_name,i.middle_name,i.last_name,i.name_prefix,i.name_suffix";
		}//end if
		else
		{
			$what_sql .= ",".$return;
		}

		$sql = "SELECT $what_sql
		          FROM {$this->table['person_attribute']} pa LEFT JOIN
		               {$this->table['person']} i ON i.pid = pa.pidm LEFT JOIN
		               {$this->table['log']} l ON pa.attribute = l.attribute AND pa.pidm = l.pidm AND pa.type_id = l.type_id LEFT JOIN
		               {$this->table['log']} lorigin ON l.origin_id = lorigin.id LEFT JOIN
		               {$this->table['log']} lparent ON l.parent_id = lparent.id
		         WHERE ($attr_where)
		               $manual";

		$results = $this->db->GetAll($sql);
		return $results;
	}//end getUsersByAttribute

	/**
	 * hasAttribute
	 *
	 * Determine if the specified user has the specified attribute.
	 *
	 * @since     version 1.1.1
	 * @access    public
	 * @param     int $pidm Person identifier
	 * @param     string|int $type Type of attributes being passed
	 * @param     string $attribute value for the attribute
	 * @return    boolean
	 */
	function hasAttribute($pidm,$type,$attribute)
	{
		list($type_id, $type) = $this->any2type($type);

		$attributes = $this->getPersonAttribute($pidm, $type, $attribute);

		if(isset($attributes[$type][$attribute]))
		{
			return true;
		}//end if

		return false;
	}//end hasAttribute

	/**
	 * Determine if an attribute has children.
	 *
	 * @param      string|int $type the attribute type
	 * @param      string $attribute the attribute name
	 * @return     boolean
	 */
	function hasChildren($attribute)
	{
		$result = $this->db->CacheGetOne("SELECT 1 FROM {$this->table['attribute_group']} WHERE parent_attribute = :attribute", array('attribute' => $attribute));

		return !!$result;
	}//end hasChildren

	/**
	 * hasSystemAccount
	 * 
	 * Determines if the given user has a systems account
	 *
	 * @param      string|int $type the field
	 * @param      string $value the value
	 * @return     boolean
	 */
	function hasSystemAccount($type, $value, $where = '1=1')
	{
		$user_db = PSU::db('mysql/user_info-admin');
		$sql = "SELECT 1 FROM USER_DB WHERE (user_active=1 OR user_alumni=1) AND $type = '$value' AND $where";
		return $user_db->GetOne($sql);
	}//end hasSystemAccount

	/**
	 * hasBannerRole
	 *
	 * Determines if user has banner role
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		int $pidm Person identifier
	 * @param		string $role Role
	 * @param		boolean $roles Role list
	 * @return  	boolean
	 */
	function hasBannerRole($pidm,$role,$roles=false)
	{
		$roles=(!$roles)?$this->getAllBannerRoles($pidm):$roles;
		if(in_array(strtolower($role),$roles))
			return true;
		return false;
	}//end hasBannerRole

	/**
	 * hasOracleRole
	 *
	 * Determines if user has oracle role
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		int $pidm Person identifier
	 * @param		string $role Role
	 * @param		boolean $roles Role list
	 * @return  	boolean
	 */
	function hasOracleRole($pidm,$role,$roles=false)
	{
		$roles=(!$roles)?$this->getAllOracleRoles($pidm):$roles;
		if(in_array(strtolower($role),$roles))
			return true;
		return false;
	}//end hasOracleRole

	/**
	 * isManualAttribute
	 *
	 * Determines if an attribute was manually assigned
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		int $pidm Person identifier
	 * @param		string $type Attribute identifier
	 * @param		string $attribute Attribute value
	 * @return  	array
	 */
	function isManualAttribute($pidm,$type,$attribute='')
	{
		if(is_numeric($type))
		{
			$type_id = $type;
		}//end if
		else
		{
			list($type_id, $type) = $this->any2type($type);
		}//end else

		$sql="SELECT count(distinct pidm)
						FROM {$this->table['person_attribute']},
								 {$this->table['attribute_type']}
						WHERE pidm = :pidm 
							AND type_id = id 
							AND type_id = :type_id 
							AND manual IS NOT NULL";

		$query_params = array('pidm' => $pidm, 'type_id' => $type_id);

		if($attribute)
		{
			$sql .= "AND attribute = :attribute";
			$query_params['attribute'] = $attribute;
		}//end if

		return $this->db->GetOne($sql, $query_params);
	}//end isManualAttribute

	/**
	 * LDISync
	 */
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

	/**
	 * LDISyncAssignment
	 *
	 * 
	 *
	 * @since		version 1.1.0
	 * @access		public
	 * @param		$pidm
	 * @param		$banner_object
	 */
	function LDISyncAssignment($pidm,&$banner_object=false)
	{
		if(!$banner_object)
		{
			require_once('BannerFaculty.class.php');
			$banner_object = new BannerFaculty($this->db);
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
				$stmt=$this->db->PrepareSP($sql);
				if(!$this->db->Execute($stmt))
				{
					return false;
				}//end if
			}//end foreach
		}//end foreach
		
		return true;
	}//end LDISyncAssignment

	/** 
	 * LDISyncEnrollment
	 *
	 * @since		version 1.0.0
	 * @param		$pidm Person identifier
	 */
	function LDISyncEnrollment($pidm,&$banner_object=false)
	{
		if(!$banner_object)
		{
			require_once('BannerStudent.class.php');
			$banner_object = new BannerStudent($this->db);
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
				$stmt=$this->db->PrepareSP($sql);
				if(!$this->db->Execute($stmt))
				{
					return false;
				}//end if
			}//end foreach
		}//end foreach
		
		return true;
	}//end LDISyncEnrollment
	
	/**
	 * LDISyncPerson
	 *
	 * @since		version 1.0.0
	 * @param		$pidm
	 * @return		
	 */
	function LDISyncPerson($pidm)
	{
		$sql="
			BEGIN
				psu.pkg_roles.p_sync_user(".$pidm.");
			END;";

		$stmt=$this->db->PrepareSP($sql);
		return $this->db->Execute($stmt);
	}//end LDISyncPerson

	/**
	 * loadAuthZ
	 *
	 * loads Authorizations into session
	 *
	 * @since		version 1.0.0
	 * @param  		int $pidm Person identifier
	 * @return  	boolean
	 */
	function loadAuthZ($identifier, $force = false)
	{
		if(!$identifier)
		{
			throw new IDMException(IDMException::NO_PIDM);
		}//end if

		$authz = array();

		if( $force || !isset($_SESSION['AUTHZ']) || empty($_SESSION['AUTHZ']) || time() > $_SESSION['AUTHZ']['cache_time']+60)
		{
			$authz = PSU::get('idmobject')->getPersonAttributes($identifier);

			$sql = array();
			foreach($authz as $type => $list) {
				if( count($list) == 0 ) {
					continue;
				}

				$items = array_keys($list);
				array_walk( $items, 'IDMObject::role_escape' );
				$sql[$type] = sprintf("('%s')", implode("','", $items));
			}

			$authz['sql'] = $sql;

			if( $identifier ) {
				$authz['oracle'] = PSU::get('idmobject')->getAllOracleRoles($identifier);
				$authz['banner'] = array();

				$roles = PSU::get('idmobject')->getAllBannerRoles($identifier);
				foreach( (array) $roles as $role ) {
					$authz['banner'][ $role ] = $role;
				}//end foreach
			}//end if

			$authz['cache_time'] = time();

			return $authz;
		}//end if

		return $authz;
	}//end loadAuthZ

	/**
	 * Callback for array_walk while building role SQL.
	 */
	public static function role_escape( &$role, $key ) {
		$role = addcslashes($role, "'");
	}//end role_escape
	
	/**
	 * maintainBannerRoles
	 *
	 * Re-syncs Banner Roles
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		int $pidm Person identifier
	 * @return  	boolean
	 */
	function maintainBannerRoles($pidm)
	{
		$sql="
		DECLARE 
			roles gokisql.rule_tabtype; 
		BEGIN
			gb_institution_role.p_maintain_roles($pidm,'INTCOMP');
			gb_common.p_commit();
		END;";
		$stmt=$this->db->PrepareSP($sql);
		return $this->db->Execute($stmt);
	}//end maintainBannerRoles

	/**
	 * encrypt unencrypted pins
	 */
	function pin_encrypt_unencrypted( $user = null)
	{
		$user = $user ? $user : 'script';
		$sql = "SELECT gobtpac_pidm, gobtpac_pin, gobtpac_salt FROM gobtpac WHERE gobtpac_pin IS NOT NULL AND length(gobtpac_pin) < 30";
		if($results = $this->db->Execute($sql, $args))
		{
			foreach($results as $row)
			{
				$pin = $this->encryptPin($row['gobtpac_pin'], 'full', $row['gobtpac_salt']);	

				$sql="UPDATE gobtpac 
								 SET gobtpac_pin = :pin,
										 gobtpac_salt = :salt,
										 gobtpac_user = :username
							 WHERE gobtpac_pidm= :pidm";

				$args['pin'] = $pin['hash'];
				$args['salt'] = $pin['salt'];
				$args['username'] = $user;
				$args['pidm'] = $row['gobtpac_pidm'];

				$this->db->Execute($sql, $args);
			}//end foreach
		}//end if	
	}//end pin_encrypt_unencrypted

	/**
	 * removeAttribute
	 *
	 * Removes a given attribute.
	 *
	 * @since			version 1.0.0
	 * @access		public
	 * @param  		int $pidm Person identifier
	 * @param			int $origin_id The id from the attribute log table
	 * @return  	array a type array containing the elements that were removed
	 */
	function removeAttribute($pidm, $id)
	{
		// TODO: make removeAttribute always return a list of log ids that have been removed
		
		$attribute = $this->getLog($id);
		list($type_id, $type) = $this->any2type($attribute['type_id']);

		if($type == 'role')
		{
			// roles should be removed via _revokeRole()
			$result = $this->_revokeRole($pidm, $id);
			$this->db->CacheFlush();
			return $result;
		}//end if
		else
		{
			// everything else, just get rid of it
			$data = $this->initTypeArray();

			if($this->_doRemoveAttribute($pidm, $id))
			{
				$data[$type][] = $attribute;
			}//end if

			$this->db->CacheFlush();
			return $data;
		}//end else
	}//end removeAttribute

	/**
	 * removeExpiredAttributes
	 *
	 * Remove a user's expired attributes.
	 */
	function removeExpiredAttributes($pidm)
	{
		$atypes = $this->getPersonAttributes($pidm, false, false, false);
		$logs = $this->getLogs($pidm);
		$now = time();

		$removals = $this->initTypeArray(); // keep track of what needs to be removed

		foreach((array) $logs as $atype => $attributes)
		{
			foreach((array) $attributes as $attribute => $adata)
			{
				foreach((array) $adata as $id => $data)
				{
					if($data['end_date'] == '')
					{
						continue;
					}//end if
					$end_date = strtotime($data['end_date']);
					if($end_date < $now && !$data['origin_id'])
					{
						$removals[$atype][$attribute][$id] = $data;
					}//end if
				}//end foreach
			}//end foreach
		}//end foreach

		$real_removed = $this->initTypeArray(); // keep track of what was actually removed

		foreach((array) $removals as $rtype => $attributes)
		{
			foreach((array) $attributes as $attribute => $adata)
			{
				foreach((array) $adata as $id => $data)
				{
					$this_removed = $this->removeAttribute($pidm, $id);
					foreach((array) $this_removed as $ttype => $tattributes)
					{
						$real_removed[$ttype] = array_merge($real_removed[$ttype], $tattributes);
					}//end foreach
				}//end foreach
			}//end foreach
		}//end foreach

		return $real_removed;
	}//end removeExpiredAttributes

	/**
	 * setAttribute
	 *
	 * Set details for an attribute for a given person. Will add the attribute if necessary.
	 * Used only for "custom" attributes like titles and departments.
	 *
	 * @since			version 1.0.0
	 * @access		public
	 * @param  		int $pidm person identifier
	 * @param			int|string $type the attribute type
	 * @param			string $attribute the attribute value
	 * @param			string $source the source of the addition
	 * @param			boolean $multiple whether or not this attribute can have multiple values
	 * @param		  mixed $params parameter list
	 * @return  	array
	 */
	function setAttribute($pidm, $type, $attribute, $source, $multiple = false, $params='')
	{
		// $params:
		//		start_date (date)
		//		end_date (date)
		//		activity_date (date)
		
		if(!is_array($params))
		{
			parse_str(stripslashes($params), $params);
		}//end if

		list($params['type_id'], $type, $custom) = $this->any2type($type);

		if(!$custom)
		{
			throw new IDMException(IDMException::BAD_SET_ATTRIBUTE, htmlentities("$attribute [$type]"));
		}

		$params = $this->_attributeDefaults($params);
		
		$params['attribute'] = $attribute;

		//count the number of attributes that match...we only allow non-existant attributes to be set OR 
		// if only one attribute matches, it can be overridden
		$sql="SELECT count(*) 
		        FROM {$this->table['person_attribute']},
		             {$this->table['attribute_type']} 
		       WHERE pidm= :pidm 
		         AND type_id=id 
		         AND id= :id";	
		$num = $this->db->GetOne($sql, array('pidm' => $pidm, 'id' => $params['type_id']));

		if($multiple || $num == 0)
		{
			// multiples can just be added.
			return $this->_doAddAttribute($pidm, $params['type_id'], $attribute, $source, $params);
		}//end if

		// no multiples beyond this point.
		else
		{
			// there was one (or more) matching attributes, but we can't have multiples. delete the old ones.
			$this->db->Execute("DELETE FROM {$this->table['log']} WHERE type_id = {$params['type_id']} AND pidm = {$pidm}");
			$this->db->Execute("DELETE FROM {$this->table['person_attribute']} WHERE type_id = {$params['type_id']} AND pidm = {$pidm}");

			return $this->_doAddAttribute($pidm, $params['type_id'], $attribute, $source, $params);
		}//end else
	}//end setAttribute

	/**
	 * setupCAS
	 */
	static function setupCAS($host = null, $dir = false)
	{
		if( ! isset($host) ) {
			$host = PSU::isdev() ? 'connect.dev' : 'connect';
		}

		$dir = ($dir)?$dir:'cas/';
		if(!$GLOBALS['CAS_SETUP'])
		{
			$GLOBALS['CAS_SETUP'] = true;
	
			// import phpCAS lib
			require_once('cas/CAS.php');
	
			// initialize phpCAS
			phpCAS::client(CAS_VERSION_2_0, $host.'.plymouth.edu', 443, $dir, false);

			// logout if desired
			if (isset($_REQUEST['logout'])) {
				phpCAS::logout($_REQUEST['logout']);
			}

		}
	
		// logout if desired
		if (isset($_REQUEST['logout'])) {
			phpCAS::logout($_REQUEST['logout']);
		}
	}

	/**
	 * syncAdmitPin
	 */
	function syncAdmitPin($pid='', $user = 'script')
	{
		if($pid)
		{
			//retrieve all banner roles
			$roles = $this->getAllBannerRoles($pid);
			
			if(in_array('ug_app',$roles) && !in_array('student_account_active',$roles))
			{
				if(!$this->db->GetOne("SELECT count(*) FROM gobtpac WHERE gobtpac_pidm=$pid"))
				{
					$this->createBlankGobtpac($pid,'IDMObject->syncAdmitPin');
				}//end if

				$args = array('pidm' => $pid);
				
				$sql="SELECT sabnstu_pin pin,
										 gobtpac_salt
						FROM sabnstu,sabiden i1,gobtpac
					 WHERE sabnstu_aidm = i1.sabiden_aidm 
						 AND i1.sabiden_pidm= :pidm
					   AND i1.sabiden_pidm = gobtpac_pidm
						 AND i1.sabiden_aidm = (SELECT max(i2.sabiden_aidm) FROM sabiden i2 WHERE i2.sabiden_pidm = i1.sabiden_pidm)
						 AND gokisql.f_check_role('INTCOMP','UG_APP',sabiden_pidm)='Y'";
				
				if($row = $this->db->GetRow($sql, $args))
				{
					$pin = $this->encryptPin($row['pin'], 'full', $row['gobtpac_salt']);	

					$sql="UPDATE gobtpac 
						       SET gobtpac_pin = :pin,
											 gobtpac_salt = :salt,
											 gobtpac_pin_exp_date = add_months(sysdate,500),
								  		 gobtpac_user = :username
							   WHERE gokisql.f_check_role('INTCOMP','UG_APP',gobtpac_pidm)='Y'
								   AND gobtpac_pidm= :pidm";

					$args['pin'] = $pin['hash'];
					$args['salt'] = $pin['salt'];
					$args['username'] = $user;
								 
					/*
					 TODO: NEED TO generate a username if UG_APP and STUDENT_ACCOUNT_ACTIVE
					
					select psu_id from gorirol r1,psu_identity.person_identifiers
	where exists(select 1 from gobtpac where gobtpac_external_user is null and gobtpac_pidm=r1.gorirol_pidm)
	and r1.gorirol_role='UG_APP' and exists(select 1 from gorirol r2 where r2.gorirol_pidm=r1.gorirol_pidm and r2.gorirol_role='STUDENT_ACCOUNT_ACTIVE')
	and pid=gorirol_pidm;*/
				}//end if
				else
				{
					$sql="SELECT 1 FROM dual";
					$args = array();
				}//end else
			}//end if
			else
			{
				return false;
			}//end else
		}//end if
		else
		{
			$this->createBlankGobtpac(null, 'IDMObject->syncAdmitPin','UG_APP');
			
			$sql="UPDATE gobtpac SET gobtpac_pin = (
												SELECT sabnstu_pin
												FROM sabnstu,sabiden i1 
											 WHERE sabnstu_aidm = i1.sabiden_aidm 
												 AND i1.sabiden_pidm=gobtpac_pidm
												 AND i1.sabiden_aidm = (SELECT max(i2.sabiden_aidm) FROM sabiden i2 WHERE i2.sabiden_pidm = i1.sabiden_pidm)),
										gobtpac_pin_exp_date = add_months(sysdate,500)
					 WHERE exists(SELECT 1 FROM v_ug_app WHERE pidm = gobtpac_pidm)
			       AND not exists(SELECT 1 FROM gorirol WHERE gorirol_role in ('ZIMBRA', 'ALUMNI', 'STUDENT_ACCOUNT_ACTIVE') AND gorirol_pidm = gobtpac_pidm)
						 AND gobtpac_pin <> (
														 SELECT sabnstu_pin
												FROM sabnstu,sabiden i1 
											 WHERE sabnstu_aidm = i1.sabiden_aidm 
												 AND i1.sabiden_pidm=gobtpac_pidm
												 AND i1.sabiden_aidm = (SELECT max(i2.sabiden_aidm) FROM sabiden i2 WHERE i2.sabiden_pidm = i1.sabiden_pidm))
						 ";
			$sql="UPDATE gobtpac SET gobtpac_pin_exp_date = add_months(sysdate,500)
					 WHERE exists(SELECT 1 FROM v_ug_app WHERE pidm = gobtpac_pidm)
			       AND not exists(SELECT 1 FROM gorirol WHERE gorirol_role in ('ZIMBRA', 'ALUMNI', 'STUDENT_ACCOUNT_ACTIVE') AND gorirol_pidm = gobtpac_pidm)
						 AND gobtpac_pin <> (
														 SELECT sabnstu_pin
												FROM sabnstu,sabiden i1 
											 WHERE sabnstu_aidm = i1.sabiden_aidm 
												 AND i1.sabiden_pidm=gobtpac_pidm
												 AND i1.sabiden_aidm = (SELECT max(i2.sabiden_aidm) FROM sabiden i2 WHERE i2.sabiden_pidm = i1.sabiden_pidm))
						 ";
			$args = array();

			$pin_cleanup = true;
		}//end else

		if($pin_cleanup)
		{
			$this->db->Execute($sql, $args);
			$this->pin_encrypt_unencrypted( $user );
		}//end if
		else
		{	
			return $this->db->Execute($sql, $args);
		}//end else
	}//end syncAdmitPin

	/**
	 * syncAttribute
	 *
	 * Update columns for a person's attribute (start_date, end_date, etc.) from the
	 * log.
	 * 
	 * @access		public
	 * @param			int $pidm the user id
	 * @param			int|string $type the attribute type
	 * @param			string $attribute the attribute name
	 * @return		boolean true/false indicating success or failure
	 */
	function syncAttribute($pidm, $type, $attribute)
	{
		list($type_id, $type) = $this->any2type($type);

		$where_sql = "pidm = $pidm AND type_id = $type_id AND attribute = ".$this->db->qstr($attribute);

		$sql = "
			SELECT MIN(start_date) start_date,
			       MAX(end_date) end_date
			  FROM {$this->table['log']}
			 WHERE $where_sql
		";
		$params = $this->db->GetRow($sql);

		// empty start date means there is nothing remaining in the log, and we need
		// to remove this attribute.
		if($params['start_date'] === null)
		{
			$sql = "
				DELETE
					FROM {$this->table['person_attribute']}
				 WHERE $where_sql
			";
			return $this->db->Execute($sql);
		}

		// check for overriding null end_date
		$sql = "
			SELECT 1
			  FROM {$this->table['log']}
			 WHERE $where_sql AND
			       end_date IS NULL
		";

		$params['start_date'] = strtotime($params['start_date']);
		$params['end_date'] = $this->db->GetOne($sql) ? null : ($params['end_date'] ? strtotime($params['end_date']) : null);

		$current = $this->getPersonAttribute($pidm, $type_id, $attribute);
		$current = $current[$type][$attribute];

		// was there a change?
		if($current['start_date'] != $params['start_date'] || 
			 $current['end_date'] != $params['end_date'] || 
			 !isset($current['end_date']) || 
			 !isset($current['start_date']))
		{
			// update row in person_attribute with the new date data
			$params['activity_date'] = 'sysdate';
			$rs =& $this->db->CacheExecute("SELECT * FROM {$this->table['person_attribute']} WHERE $where_sql");
			$sql = $this->db->GetUpdateSQL($rs, $params, true);
			return $this->db->Execute($sql);
		}

		// no update necessary, just return true.
		return true;
	}//end syncAttribute

	/**
	 * synchronize banner roles with luminis
	 *
	 * @param $person mixed person identifier or person object
	 * @param $portal_roles array person's existing portal roles
	 */
	public function syncLuminisRoles( $person, $portal_roles = null ) {
		
		// if $person isn't a PSUPerson, instantiate
		if( !($person instanceof PSUPerson) ) {
			$person = new PSUPerson( $person );
		}//end if

		// if the person doesn't have a pidm, they won't exist in luminis
		if( !$person->pidm ) return;

		// if roles weren't passed in, grab 'em
		if( !$portal_roles ) {
			$portal_roles = PSU::get('luminisportal')->getRoles($person->login_name);
		}//end if

		// force a banner role calc
		PSU::get('idmobject')->maintainBannerRoles($person->pidm);

		// get the user's banner roles
		$banner_roles = PSU::get('idmobject')->getAllBannerRoles($person->pidm);

		$managed_roles = array(
			'alumni',
			'developmentofficer',
			'employee',
			'faculty',
			'finance',
			'friends',
			'student',
			'sysadmin',
			'syssupport',
			'creator',
			'accountadmin',
			'user',
			'prospectivestudent',
			'guest',
			'administrator',
			'observer',
			'prospect',
			'applicant',
			'institutionaccept',
			'applicantaccept',
			'bannerinb',
			'loadtester',
			'newstudent',
			'activestudent',
			'student_active',
			'student_grad',
			'student_undergrad',
			'student_exiting',
			'student_pending',
			'student_expected',
			'psu_friend',
			'student_ug_grad_candidate',
			'student_enrolled',
			'student_ug_first_year',
			'student_ug_junior',
			'student_ug_senior',
			'student_ug_sophomore',
			'student_ug_transfer',
			'ug_app',
			'ug_app_accept',
			'ug_app_denied',
			'ug_app_withdrawn',
			'student_account_active',
			'employee_os',
			'employee_pat',
			'employee_pa',
			'alumni_campus',
			'alumni_emeritus',
			'staff',
			'personal_email_collected',
			'student_former',
			'ug_app_accept_fall',
			'faculty_services',
			'student_worker',
			'finaid',
			'pds_authenticate',
			'zimbra',
		);

		$intended_roles = array();

		// figure out which roles the user should have
		if(is_array($portal_roles) && is_array($banner_roles)) {
			$managed_banner_roles = array_intersect($banner_roles, $managed_roles);
			$portal_only_roles = array_diff($portal_roles, $managed_roles);
			$intended_roles = array_merge($portal_only_roles, $managed_banner_roles);
		}

		if($intended_roles) {
			// determine roles to delete
			$roles_to_delete = array_diff($portal_roles, $intended_roles);
			// determine roles to add
			$roles_to_add = array_diff($intended_roles, $portal_roles);

			// if there are roles to delete or add, update the role
			if( $roles_to_delete || $roles_to_add ) {
				$array = array();
				$array['pdsrole'] = $intended_roles;

				PSU::get('luminisportal')->changeAttribute($person->login_name, $array);
			}//end if
		}//end if

		$person->destroy();
	}//end syncLuminisRoles

	public function trigger_banner_username_sync( $pidm ) {
		$sql = "UPDATE gobtpac SET gobtpac_activity_date = sysdate WHERE gobtpac_pidm = :pidm";
		return PSU::db('idm')->Execute( $sql, array( 'pidm' => $pidm ) );
	}//end trigger_banner_username_sync

	/**
	 * unauthN
	 *
	 * Calls phpCAS::logout
	 *
	 * @since		version 1.0.2
	 * @access		public
	 */
	static function unauthN()
	{
		require_once('cas/CAS.php');
		phpCAS::logout();
	}//end unauthN

	/**
	 * Determine how many people match the given information.
	 *
	 * @since		version 1.0.0
	 * @param		string $value value of the field
	 * @param		string $field the field to check against
	 * @return		integer the number of matching people
	 */
	function validIdentifier($value,$field)
	{
		return $this->db->GetOne("SELECT count(*) FROM {$this->table['person']} WHERE $field = :value", array('value' => $value));
	}//end validIdentifier

	/**
	 * Load the wpid in a way that's less dependant on WordPress.
	 */
	static function wpid2pidm($wpid)
	{
		$sql = "
			SELECT meta_value
			FROM wp_users u
			LEFT JOIN wp_usermeta m ON u.ID = m.user_id
			WHERE m.meta_key = 'pidm' AND u.user_login = ?
			LIMIT 1
		";

		return PSU::db('connect')->GetOne($sql, array($wpid));
	}//end wpid2pidm

	/**
	 * _assignRole
	 *
	 * Assigns a role to a user.
	 *
	 * @since			version 1.1.0
	 * @access		public
	 * @param  		string $pidm Person key id
	 * @param			array $attribute_data a string or associative array with role details
	 * @return		array a type array listing what was added
	 */
	private function _assignRole($pidm, $role, $source, $role_data='')
	{
		// $role_data:
		//		manual (boolean)
		//		granted_by (string)
		//		start_date (string)
		//		end_date (string)
		//
		// defaults for optional arguments determined by $this->_attributeDefaults()

		if(!is_array($role_data))
		{
			parse_str($role_data, $role_data);
		}//end if

		$role_id = $this->getAttributeId('role');

		// does this role exist?
		if(!$this->getAttribute($role_id, $role))
		{
			throw new IDMException(IDMException::INVALID_ATTRIBUTE);
		}//end if

		// has the role already been added for this source?
		$sql = "
			SELECT 1
			  FROM {$this->table['log']}
			 WHERE pidm = $pidm
			   AND type_id = $role_id
			   AND attribute = '$role'
			   AND source = '$source'
				 AND origin_id IS NULL
		";
		if($this->db->GetOne($sql))
		{
			throw new IDMException(IDMException::DUPLICATE_ROLE, htmlentities($role));
		}

		$added_attributes = $this->initTypeArray(); // what we actually added, returned to the user
		$role_id = $this->getAttributeId('role');

		$this->db->StartTrans();

		$role_data['origin_id'] = null; // origin_id starts as null, and will be set by the first role

		// add this role, and parents
		$roles = $this->getRoleCollection($role);
		foreach($roles as $this_role)
		{
			// don't inherit last parent_id, roles shouldn't have a parent
			$role_data['parent_id'] = null;

			// add the role. use internal function to avoid parent checks.
			if($result = $this->_doAddAttribute($pidm, $role_id, $this_role, $source, $role_data))
			{
				$added_attributes = array_merge_recursive($added_attributes, $result);

				if($role_data['origin_id'] === null)
				{
					$role_data['origin_id'] = $this->last_origin_id;
				}
			}//end if
			else
			{
				// addAttribute failed, which means insert failed. rollback transaction
				return $this->db->CompleteTrans();
			}//end else

			$role_data['parent_id'] = $this->last_origin_id;

			$this->addDefaultChildren($pidm, $this->last_origin_id, $source, $role_data);
		}//end foreach

		// if the addition failed, commit the transaction and return the added attributes
		if($this->db->CompleteTrans())
		{
			return $added_attributes;
		}//end if

		// if anything failed, return false. this should never happen, since failures should
		// be caught during addAttribute()
		return false;
	}//end _assignRole

	/**
	 * _attributeDefaults
	 *
	 * An array containing the default values for an attribute.
	 *
	 * @param		$input values to override defaults
	 */
	private function _attributeDefaults($input)
	{
		if(!is_array($input))
		{
			parse_str($input, $input);
		}//end if

		$data = array(
			'start_date' => date('Y-m-d'),
			'activity_date' => date('Y-m-d'),
			'granted_by' => $_SESSION['username'] ? $_SESSION['username'] : 'Script'
		);

		$data = array_merge($data, $input);

		if($data['granted_by'] != 'Script')
		{ 
			$data['grantor_pidm'] = $this->getIdentifier($data['granted_by'], 'username', 'pidm');
		}//end if

		return $data;
	}//end _attributeDefaults

	/**
	 * _cacheAttributes
	 *
	 * Cache the Attribute table for later usage.
	 *
	 * @access		private
	 */
	private function _cacheAttributes()
	{
		$this->_attributes = array();

		$sql = "SELECT * FROM {$this->table['attribute_type']}";
		$rows = $this->db->CacheGetAll($sql);

		foreach((array) $rows as $row)
		{
			$this->_attributes[$row['id']] = $row;
		}//end foreach
	}//end _cacheAttributes

	/**
	 * _doAddAttribute
	 *
	 * Add the attribute (and log it) with no prior checks.
	 *
	 * @access		private
	 */
	private function _doAddAttribute($pidm,$type,$attribute,$source,$params='')
	{
		list($type_id, $type) = $this->any2type($type);

		if(!is_array($params))
		{
			parse_str(stripslashes($params), $params);
		}//end if

		$params = $this->_attributeDefaults($params);

		$params['pidm'] = $pidm;
		$params['source'] = $source;
		$params['type_id'] = $type_id;
		$params['attribute'] = $attribute;
		
		// **************** Log the attribute addition. This always happens, whether
		// **************** or not we actually add the attribute.

		$rs = $this->db->CacheExecute("SELECT * FROM {$this->table['log']} WHERE id = -1");
		$sql = $this->db->GetInsertSQL($rs, $params);

		if(!$this->db->Execute($sql))
		{
			// failures return false
			return false;
		}

		$this->last_origin_id = $this->db->GetOne("SELECT psu_identity.seq_attribute_log.currval FROM DUAL");

		// **************** Add the attribute. We'll only do this if adding will
		// **************** not create a duplicate.

		$added = $this->initTypeArray();

		if($this->hasAttribute($pidm, $type_id, $attribute))
		{
			// we have this attribute. return empty array, indicating success but no addition
			return $added;
		}

		$rs = $this->db->CacheExecute("SELECT * FROM {$this->table['person_attribute']} WHERE pidm = -1");
		$sql = $this->db->GetInsertSQL($rs, $params);

		if($this->db->Execute($sql))
		{
			// success, return the type array
			$added[$type][] = $attribute;
			return $added;
		}

		// failure of some sort. return false.
		return false;
	}//end _doAddAttribute

	/**
	 * _doRemoveAttribute
	 *
	 * Remove the specified person's attribute. Calling function should determine
	 * if this is a sane thing to do.
	 *
	 * @since		version 1.1.1
	 * @access		private
	 * @param 		int $pidm Person identifier
	 * @param		int $type Attribute type
	 * @param		string $attribute Attribute identifier/name
	 */
	private function _doRemoveAttribute($pidm, $id)
	{
		$attribute = $this->getLog($id);

		$sql = "
			DELETE FROM {$this->table['log']}
			 WHERE pidm = $pidm
			   AND id = $id
		";
		
		$this->db->StartTrans();
		$this->db->Execute($sql);
		$this->syncAttribute($pidm, $attribute['type_id'], $attribute['attribute']);
		$this->db->CompleteTrans();
	}//end _doRemoveAttribute

	/**
	 * initTypeArray
	 *
	 * Return an array of arrays containing all valid attribute types as keys, suitable
	 * for combining with other function return values. Will have structure similar to:
	 *
	 * array('permission'=>array(), 'role'=>array(), [etc ...]);
	 *
	 */
	function initTypeArray()
	{
		$data = array();
		foreach((array) $this->_attributes as $a)
		{
			$data[$a['name']] = array();
		}//end foreach
		return $data;
	}//end initTypeArray

	/**
	 * _revokeRole
	 *
	 * Remove a role (based on origin id) and associated attributes for a user.
	 *
	 * @since			version 1.1.1
	 * @access		private
	 * @param			int $pidm pid of the target user
	 * @param			int $id the role's log id
	 * @return		boolean
	 */
	private function _revokeRole($pidm, $id)
	{
		$role = $this->getLog($id);

		// can only revoke a top-level role
		if($role['origin_id'])
		{
			throw new IDMException(IDMException::REVOKE_CHILD_ROLE, $id);
		}

		$children = $this->getLogChildren($id);

		$this->_doRemoveAttribute($pidm, $id);
		foreach($children as $child_id => $data)
		{
			$this->_doRemoveAttribute($pidm, $child_id);
		}
	}//end _revokeRole
}//end class IDMObject

/**
 * IDMException
 *
 * Provides an exception class for use with IDMObject.
 *
 * @package			IdentityManagement
 */
require_once('PSUException.class.php');
class IDMException extends PSUException {
	const INVALID_ATTRIBUTE = 1; // attribute does not exist
	const INVALID_TYPE = 2; // type does not exist
	const DUPLICATE_ROLE = 3; // role with this source and null origin exists
	const BAD_LOG_ID = 4; // log id doesn't exist
	const ASSIGN_ROLE_FAILED = 5; // addAttribute() couldn't add a required parent
	const PARENT_MISSING = 6; // attribute's parent missing
	const ADD_CHILDREN_NON_ROLE = 7; // attempt to add children on a non-role
	const SQL_ERROR = 8; // sql error
	const BAD_SET_ATTRIBUTE = 9; // setAttribute() on a non-custom type
	const REVOKE_CHILD_ROLE = 10; // tried to revoke a role with an origin_id
	const NO_PIDM = 11; //no pidm provided
	const NOT_CONNECTED = 12; //not connected to a database
	const MKNTPWD_FAILED = 13; // mkntpwd command exited with status > 0
	const AD_ACTIVE_FAIL = 14; // db error checking ad active status

	private static $_msgs = array(
		self::INVALID_ATTRIBUTE => 'Attribute does not exist',
		self::INVALID_TYPE => 'Type does not exist',
		self::DUPLICATE_ROLE => 'This role has already been added by this source',
		self::BAD_LOG_ID => 'There is no such log entry with that ID',
		self::ASSIGN_ROLE_FAILED => 'Could not add the required parent role',
		self::PARENT_MISSING => 'Attribute could not be added, user does not have parent attribute',
		self::ADD_CHILDREN_NON_ROLE => 'Attempt to add child attributes for a non-role',
		self::SQL_ERROR => 'There was an error in the SQL statement',
		self::BAD_SET_ATTRIBUTE => 'Attempt to setAttribute() on a non-custom attribute type',
		self::REVOKE_CHILD_ROLE => 'Refusing to revoke a role that has an origin_id',
		self::NO_PIDM => 'You must specify a pidm',
		self::NOT_CONNECTED => 'Unable to connect to database',
		self::MKNTPWD_FAILED => 'mkntpwd failed with exit code',
		self::AD_ACTIVE_FAIL => 'Could not check AD active status'
	);

	/**
	 * Wrapper construct so PSUException gets our message array.
	 */
	function __construct($code, $append=null)
	{
		parent::__construct($code, $append, self::$_msgs);
	}
}

// vim:ts=2:sw=2:noet:
?>
