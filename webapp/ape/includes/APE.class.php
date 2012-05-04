<?php

/**
 * The APE API.
 * @ingroup ape
 */
class APE
{
	var $icon_types=array('user','roles','departments','home','hr','mailing','app');
	var $icons=array();

	/**
	 * Set up database connections.
	 * @param $db \b ADOConnection
	 * @param $idm \b IDMObject the identity management object
	 * @param $banner_general \b ADOConnection
	 * @param $myplymouth \b ADOConnection
	 */
	function __construct(&$db,&$idm,&$banner_general,&$myplymouth=false)
	{
		$this->db=&$db;
		$this->BannerIDM=&$idm;
		$this->BannerGeneral=&$banner_general;
		if($myplymouth)
		{
			$this->myplymouth = &$myplymouth;
		}

		if( $_GET['pidm'] || $_GET['username'] ) {
			list( $identifier, $person ) = self::get_identifier();
		}//end if
	}//end __construct

	/**
	 * Determine if the current user can reset passwords.
	 */
	function canResetPassword()
	{
		return IDMObject::authZ('permission', 'ape_pw') || APEAuthZ::infodesk();
	}//end canResetPassword

	/**
	 * close a calllog ticket
	 */
	public static function close_ticket( $call_id, $details, $params = null ) {
		require_once PSU_BASE_DIR . '/webapp/calllog/includes/functions.php';
		require_once PSU_BASE_DIR . '/webapp/calllog/includes/functions/add_update.class.php';

		$new_call = new NewCall( PSU::db('calllog') ); // New Call object

		$date = date('Y-m-d');
		$time = date('H:i:s');

		$call_info = array(
			'updated_by' => $_SESSION['username'],
			'comments' => $details,
			'call_id' => $call_id,
			'current' => 1,
			'call_status' => 'closed',
			'its_assigned_group' => 7,
			'tlc_assigned_to' => 'unassigned',
			'call_priority' => 'normal',
			'datetime_assigned' => $date.' '.$time,
			'date_assigned' => $date,
			'time_assigned' => $time,
		);

		return $new_call->addToCallHistory($call_info); // adds call to the call_history table
	}//end close_ticket

	/**
	 * generate a calllog ticket
	 */
	public static function create_ticket( $user, $title, $details, $params = null ) {
		require_once PSU_BASE_DIR . '/webapp/calllog/includes/functions.php';
		require_once PSU_BASE_DIR . '/webapp/calllog/includes/functions/add_update.class.php';

		$new_call = new NewCall( PSU::db('calllog') ); // New Call object

		$person = new PSUPerson( $user );

		$date = date('Y-m-d');
		$time = date('H:i:s');

		$args = array(
			'caller_user_name' => $person->login_name,
			'call_source' => 'APE',
			'title' => $title,
			'problem_details' => $details,
			'resnet_check' => 'no',
			'its_assigned_group' => 7,
			'tlc_assigned_to' => 'unassigned',
			'call_status' => 'open',
			'call_priority' => 'normal',
			'location_building_id' => 0,
			'location_building_room_number' => '',
			'location_call_logged_from' => 'support',
			'keywords' => 'password',
			'datetime_assigned' => $date.' '.$time,
			'date_assigned' => $date,
			'time_assigned' => $time,
		);

		if( $person->phones['OF'][0] ) {
			$args['caller_phone_number'] = '('.$person->phones['OF'][0]->phone_area.')'.$person->phones['OF'][0]->phone_number;
		} else {
			$args['caller_phone_number'] = '';
		}//end else

		$args = PSU::params( $params, $args );

		return $new_call->addNewCall($args, 'APE');
	}//end create_ticket

	/**
	 * find a ticket
	 */
	public static function find_ticket_by_source( $person, $title ) {
		if( !is_object( $person ) ) {
			$person = new PSUPerson( $person );
		}//end if

		$sql = "SELECT *
			        FROM call_log c,
							     call_history h
						 WHERE ( c.wp_id = ? OR c.caller_username = ? )
							 AND c.location_call_logged_from = 'APE'
							 AND c.title = ?
							 AND h.current = 1
							 AND h.call_status = 'open'
						   AND c.call_id = h.call_id";

		return PSU::db('calllog')->GetRow( $sql, array( $person->wp_id, $person->username, $title ) );
	}//end find_ticket_by_source

	/**
	 * gets the appropriate identifier
	 */
	public static function get_identifier() {
		$identifier = $_GET['username'] ? $_GET['username'] : ($_GET['pidm'] ? $_GET['pidm'] : $_SESSION['ape_identifier']);
		try {
			$person = new PSUPerson( $identifier );
			$_SESSION['ape_identifier'] = $identifier;
		} catch( PSUPersonException $e ) {
			$_SESSION['errors'][] = $e->getMessage();

			PSUHTML::redirect( $GLOBALS['BASE_URL'] . '/search.html?type=name&identifier=' . urlencode($identifier) );
			exit;
		} catch( Exception $e ) {
			throw $e;
		}

		return array( $identifier, $person );
	}//end get_identifier

	function handleUserAction($action, $username)
	{
		if($action)
		{
			$person = new PSUPerson($username);
			
			if($person->pidm)
			{
				switch($action)
				{
					case 'drive_quota':
						echo calcDriveQuota($person->username);
					break;
					case 'view_ssn':
						if(IDMObject::authZ('permission','ape_ssn'))
						{
							$GLOBALS['LOG']->write('Viewing SSN',$person->login_name);
							$person->_load_ssn('//');
							echo $person->ssn;
						}//end if
						else
						{
							echo 'You do not have access to view this ssn.';
						}//end else
					break;
					case 'view_pin_response':
						if(IDMObject::authZ('permission','ape_pin_hint'))
						{
							$GLOBALS['LOG']->write('Viewing Pin Response',$person->login_name);
							echo $person->security_response;
						}//end if
						else
						{
							echo 'You do not have access to view this pin hint response.';
						}//end else
					break;
					case 'view_cert':
						if(IDMObject::authZ('permission','ape_ssn'))
						{
							$GLOBALS['LOG']->write('Viewing Foreign Cert Number',$person->login_name);
							echo $person->certification_number;
						}//end if
						else
						{
							echo 'You do not have access to view this cert number.';
						}//end else
					break;
					case 'view_foreign_ssn':
						if(IDMObject::authZ('permission','ape_ssn'))
						{
							$GLOBALS['LOG']->write('Viewing Foreign SSN',$person->login_name);
							echo $person->foreign_ssn;
						}//end if
						else
						{
							echo 'You do not have access to view this foreign ssn.';
						}//end else
					break;
					case 'add_workflow_hiring_roles':
						if(IDMObject::authZ('permission','ape_workflow'))
						{
							if (!$GLOBALS['Workflow']->isWorkflowUser($person->login_name))
							{
								$attr = array (
									'logonID' => $person->login_name,
									'lastName' => $person->last_name,
									'firstName' => $person->first_name,
									'emailAddress' => $person->login_name.'@plymouth.edu'
								);
								$GLOBALS['Workflow']->createUser($attr);
							}
							$GLOBALS['Workflow']->addHiringRolesToUser($person->login_name);
							echo ' [Roles added]';
						}//end if
						else
						{
							echo 'You do not have access to modify foreign data.';
						}//end else
					break;
				}//end switch
			}//end if
			else
			{
				echo 'Invalid pidm';
			}//end else
		}//end if
	}//end handleUserAction

	/**
	 * Log an action.
	 *
	 * @param $user_pidm       \b int pidm of user being edited
	 * @param $action          \b string the action being performed
	 * @param $status          \b string result of the action: success, failure, denied
	 * @param $type            \b string IDM type or textual description of item being affected
	 * @param $attribute       \b string (optional) attribute being affected
	 */
	function log($user_pidm, $action, $status, $type, $attribute=null)
	{
		$data = array(
			'pidm' => $user_pidm,
			'action' => $action,
			'type' => $type,
			'status' => $status,
			'attribute' => $attribute,
			'admin_pidm' => $_SESSION['pidm'],
			'admin_username' => $_SESSION['username'],
		);

		$data['username'] = $GLOBALS['BannerIDM']->getIdentifier($data['pidm'],'pid','username');

		$table = 'ape_log';
		$sql = $GLOBALS['CALLLOG']->GetInsertSQL($table, $data, get_magic_quotes_gpc(), false);
		$GLOBALS['CALLLOG']->Execute($sql);
	}

	/**
	 * Get all the user's roles, including duplicates.
	 * @param $pidm \b int user pidm
	 */
	function userRoles($pidm)
	{
		$role_id = $GLOBALS['BannerIDM']->getAttributeId('role');
		$roles = $GLOBALS['BannerIDM']->getLogs($pidm, "type_id = $role_id");

		$expanded_roles = array();

		foreach($roles['role'] as $attribute => $instances)
		{
			$description = $GLOBALS['BannerIDM']->getAttribute($role_id, $attribute);
			foreach($instances as &$instance)
			{
				$instance['name'] = $description['name'];
			}
			$expanded_roles = array_merge($expanded_roles, $instances);
		}
		
		usort($expanded_roles, 'APE::roleSort');

		return $expanded_roles;
	}//end userRoles

	/**
	 * Children for a log entry.
	 * @param $id \b int
	 */
	function roleChildren($id)
	{
		$children = $GLOBALS['BannerIDM']->getLogChildrenByParent($id);

		foreach($children as &$child)
		{
			$description = $GLOBALS['BannerIDM']->getAttribute($child['type_id'], $child['attribute']);
			$child['name'] = $description['name'];
		}

		usort($children, 'APE::roleSort');

		return $children;
	}//end roleChildren

	/**
	 * Role sorting function
	 */
	public static function roleSort( $a, $b ) {
		return strnatcasecmp($a['name'], $b['name']);
	}//end roleSort

	/**
	 * Roles that have not yet been added by APE, suitable for Smarty's {html_options}
	 * @param $pidm \b integer
	 */
	function nonApeRoles($pidm)
	{
		$all_roles = $GLOBALS['BannerIDM']->getRoles();

		$select_options = array();
		foreach($all_roles as $role => $attrs)
		{
			if($attrs['name'])
			{
				$select_options[$role] = $attrs['name'];
			}
			else
			{
				$select_options[$role] = $role;
			}
		}

		$role_id = $GLOBALS['BannerIDM']->getAttributeId('role');
		$user_ape_roles = $GLOBALS['BannerIDM']->getLogs($pidm, "type_id = $role_id AND
			source = '{$GLOBALS['IDM_SOURCE']}' AND origin_id IS NULL");
		$user_ape_roles = $user_ape_roles['role'];

		$select_options = array_diff_key($select_options, $user_ape_roles);

		return $select_options;
	}//end nonApeRoles

	/**
	 * Children of a role that are not already present, suitable for Smarty's {html_options}
	 * @param $role \b array role
	 * @param $user_children \b array child attributes to add
	 */
	function childrenToAdd($role, $user_children)
	{
		$all_children = $GLOBALS['BannerIDM']->getChildAttributes($role['attribute'], 'permission', IDMObject::IDM_INCLUDE);
		$all_children = $all_children['permission'];

		// pull the children list into something we can work with more easily
		$user_children_expanded = $GLOBALS['BannerIDM']->initTypeArray();
		foreach($user_children as $child)
		{
			list($type_id, $type) = $GLOBALS['BannerIDM']->any2type($child['type_id']);
			$user_children_expanded[$type][$child['attribute']] = true;
		}
		$user_children = $user_children_expanded;

		// loop through all the valid children, removing ones the user already has and scrubbing data
		foreach($all_children as $attribute => &$data)
		{
			if(isset($user_children[$type][$attribute]))
			{
				unset($all_children[$attribute]);
				continue;
			}

			$description = $GLOBALS['BannerIDM']->getAttribute('permission', $attribute);
			if($description['name'])
			{
				$data = $description['name'];
			}
			else
			{
				$data = $attribute;
			}
		}

		asort($all_children);

		return $all_children;
	}//end childrenToAdd

	/**
	 * Convenience function to test if the user can administer this role.
	 *
	 * @param $role       \b array an associative array of the role data
	 */
	function canAdminRole($role=null)
	{
		// can't admin attributes
		if(!IDMObject::authZ('permission','ape_attribute_admin'))
		{
			return false;
		}

		// wasn't a role-specific query, user is allowed
		if($role==null)
		{
			return true;
		}

		// allowed to edit this role?
		if($role['origin_id'] == null && $role['source'] == $GLOBALS['IDM_SOURCE'])
		{
			return true;
		}

		return false;
	}//end canAdminRole

	/**
	 * Return number of currently locked accounts.
	 * @return integer
	 */
	function locks_count()
	{
		return $this->myplymouth->GetOne("SELECT COUNT(id) FROM `ape_support_locks`");
	}//end locks_count

	/**
	 * Return the currently locked accounts.
	 * @return array of username, fullname, added date/time, and lock status
	 */
	function locks()
	{
		$results = $this->myplymouth->GetAll("SELECT pidm, login_name, fullname, added, status, reason, locker_pidm FROM `ape_support_locks`");

		if( $results ) {
			array_walk( $results, array($this, 'locks_identifiers') );
		}

		return $results;
	}

	/**
	 * Callback for APE::locks() function.
	 */
	function locks_identifiers( &$item, $key ) {
		static $person_cache;

		if( isset($person_cache[$item['locker_pidm']]) ) {
			$locker = $person_cache[$item['locker_pidm']];
		} else {
			try {
				$locker = new PSUPerson( $item['locker_pidm'] );
			} catch( Exception $e ) {
				$locker = (object) array('username' => '');
			}

			$person_cache[$item['locker_pidm']] = $locker;
		}

		$item['locker'] = $locker->username;
	}//end locks_pidm2username

	/**
	 * Return a list of users who will be created by the next account creation process.
	 */
	public function pending_accounts() {
		return PSU::db('userinfo')->GetAll("SELECT pidm, user_first, user_middle, user_last, user_uname FROM USER_TEMP ORDER BY user_last, user_first");
	}//end pending_accounts

	/**
	 * Return number of users who will be created by the next account creation process.
	 */
	public function pending_accounts_count() {
		return (int)PSU::db('userinfo')->GetOne("SELECT COUNT(1) FROM USER_TEMP");
	}//end pending_accounts

	/**
	 * Accounts pending deletion.
	 */
	public function pending_deletion() {
		$users = PSU::get('ad')->group_info('pending_deletion');
		$users = $users[0]['member'];

		unset( $users['count'] );

		array_walk($users, create_function('&$v,$k', '$v = array_shift( explode(",", substr($v, 3)) );') );

		sort($users);

		return $users;
	}//end pending_deletion

	public function pending_deletion_count() {
		$users = PSU::get('ad')->group_info('pending_deletion');
		return $users[0]['member']['count'];
	}

	/**
	 * update a calllog ticket
	 */
	public static function update_ticket( $call_id, $details, $params = null ) {
		require_once PSU_BASE_DIR . '/webapp/calllog/includes/functions.php';
		require_once PSU_BASE_DIR . '/webapp/calllog/includes/functions/add_update.class.php';

		$new_call = new NewCall( PSU::db('calllog') ); // New Call object

		date_default_timezone_set( 'America/New_York' );

		$date = date('Y-m-d');
		$time = date('H:i:s');

		$call_info = array(
			'updated_by' => $_SESSION['username'],
			'comments' => $details,
			'call_id' => $call_id,
			'current' => 1,
			'call_status' => 'open',
			'its_assigned_group' => 7,
			'tlc_assigned_to' => 'unassigned',
			'call_priority' => 'normal',
			'datetime_assigned' => $date.' '.$time,
			'date_assigned' => $date,
			'time_assigned' => $time,
		);

		return $new_call->addToCallHistory($call_info); // adds call to the call_history table
	}//end update_ticket

	/**
	 * checks for an invalid person object and redirects if there is a problem
	 */
	public static function validate_person( $identifier, &$person ) {
		// if this pidm is bogus, display an error
		if(!$person->wp_id && !$person->pidm)
		{
			if( $person->identifier_type ) {
				$_SESSION['errors'][] = sprintf('"%s" is not a recognized '. $person->identifier_type .'.', htmlentities($person->initial_identifier));
			} else {
				$_SESSION['errors'][] = sprintf('"%s" is not a unique identifier.  A skilled and helpful APE has executed a search on your behalf.  Rejoice!', htmlentities($person->initial_identifier));
			}//end else
			PSUHTML::redirect( $GLOBALS['BASE_URL'] . '/search.html?type=name&identifier=' . urlencode($identifier) );
			exit;
		}//end if

		return true;
	}//end validate_person
}//end class APE
