<?php
require_once 'autoload.php';
require_once('adldap/adLDAP.php');

class PSUadLDAP extends adLDAP {
	/**
	 * addUserToGroup
	 *
	 * Adds the given user to an AD group
	 *
	 * @param      mixed $username user identifier
	 * @param      mixed $group AD group
	 */
	function addUserToGroup($username, $group) {
		if(!is_array($username)) {
			$username = array($username);
		}//end if
		
		if(!is_array($group)) {
			$group = array($group);
		}//end if
	
		foreach($username as $user) {
			foreach($group as $g) {
				if(!$this->group_add_user($g,$user)) {
					echo "error adding ".$user." to ".$g."\n";
				}//end if
			}//end foreach
		}//end foreach
	}//end addUserToGroup

	/**
	 * group_users
	 *
	 * Return users from a group.
	 *
	 * @param       string $group the group name
	 * @return      array the list of users
	 */
	function group_users($group) {
		$group_info = $this->group_info($group);
		$count = $group_info[0]['member']['count'];

		$users = array();

		for($i = 0; $i < $count; $i++) { 
			$dn = $group_info[0]['member'][$i];
			$users[] = substr($dn, 3, strpos($dn, ',') - 3);
		}

		return $users;
	}//end group_users

	/**
	 * removeUserFromGroup
	 *
	 * Removes the given user from an AD group
	 *
	 * @param      mixed $username user identifier
	 * @param      mixed $group AD group
	 */
	function removeUserFromGroup($username, $group) {
		if(!is_array($username)) {
			$username = array($username);
		}//end if
		
		if(!is_array($group)) {
			$group = array($group);
		}//end if
	
		foreach($username as $user) {
			foreach($group as $g) {
				if(!$this->group_del_user($g,$user)) {
					echo "error removing ".$user." from ".$g."\n";
				}//end if
			}//end foreach
		}//end foreach
	}//end removeUserFromGroup

	/**
	 * syncAllGroups
	 * 
	 * Synchronizes all users' ad roles with banner
	 *
	 */
	function syncAllGroups($output = false, $letters = 'abcdefghijklmnopqrstuvwxyz') {
		$students = PSU::get('idmobject')->getUsersByBannerRole("student_account_active","username");
		$employees = PSU::get('idmobject')->getUsersByBannerRole("employee","username");
		$friends = PSU::get('idmobject')->getUsersByBannerRole("psu_friend","username");
		$alumni = PSU::get('idmobject')->getUsersByBannerRole("alumni","username");
		$alumni_campus = PSU::get('idmobject')->getUsersByBannerRole("alumni_campus","username");
		$alumni_emeritus = PSU::get('idmobject')->getUsersByBannerRole("alumni_emeritus","username");

		$letters = str_split( $letters );

		echo "Beginning Role Import to AD\n";
		foreach((array)$letters as $letter) { //a-z ascii
			echo "starting ".$letter."\n";

			// store the base dn because we need to override it to only include cn=Users for this loop
			$base_dn = $this->_base_dn;

			// prepend cn=Users
			$this->_base_dn = 'cn=Users,' . $this->_base_dn;

			$users = $this->all_users(false,"{$letter}*",true);

			// set base dn back to what it was before
			$this->_base_dn = $base_dn;

			echo count($users)." users\n";
			foreach((array)$users as $user) {
				$pidm = PSU::get('idmobject')->getIdentifier($user,'username','pid');
				
				$banner_roles = array();
				if(in_array($user,$students)) $banner_roles[] = 'student_account_active';
				if(in_array($user,$employees)) $banner_roles[] = 'employee';
				if(in_array($user,$friends)) $banner_roles[] = 'psu_friend';
				if(in_array($user,$alumni)) $banner_roles[] = 'alumni';
				if(in_array($user,$alumni_campus)) $banner_roles[] = 'alumni_campus';
				if(in_array($user,$alumni_emeritus)) $banner_roles[] = 'alumni_emeritus';
				
				$this->syncGroups($pidm, $banner_roles, false, $user);
			}//end for
		}//end for
	}//end syncAllGroups
	
	/**
	 * syncGroups
	 * 
	 * Synchronizes the given user's ad roles with banner
	 *
	 * @param       mixed $pidm the user identifier
	 */
	public function syncGroups($pidm, $banner_roles = false, $output = false, $username = null) {
		$lists = array(
			'pat',
			'os',
			'faculty',
			'lecturer',
			'hourly',
			'pa',
			'usnh_employees',
		);
		
		//these are the banner roles we care about for AD
		$ad_banner_roles = array(
			'student_account_active',
			'student_active',
			'student_worker',
			'student_exiting',
			'employee',
			'faculty',
			'psu_friend',
			'alumni',
			'alumni_campus',
			'alumni_emeritus',
		);

		try{
			if( ! $username ) {
				$username = PSU::get('idmobject')->getIdentifier($pidm, 'pidm', 'username');
			}//end if

			if( ! $username ) {
				throw new UnexpectedValueException('Pidm '.$pidm.' does not have a username');
			}//end if

			$person_attributes = PSU::get('idmobject')->getPersonAttributes($pidm);
			
			$groups = $this->user_groups($username, true); //all of the user's current AD groups
			if(!is_array($groups)) $groups = array();
	
			$add_groups = array();
			$remove_groups = array();
			
			if(!$banner_roles) $banner_roles = PSU::get('idmobject')->getAllBannerRoles($pidm);
	
			$banner_roles = array_intersect($banner_roles, $ad_banner_roles);

			$banner_roles = (array) $banner_roles;
	
			//********* CHECK FOR STUDENT ROLE *******
			if(in_array('student_account_active',$banner_roles) || in_array('student_active',$banner_roles) || in_array('student_worker', $banner_roles)) {
				//they're a student
				if(!in_array('students',$groups)) $add_groups[] = 'students';
			} else {
				if(in_array('students',$groups)) $remove_groups[] = 'students';
			}
			//********** END STUDENT ROLE CODE ********
	
			//********** CHECK EMPLOYEE ROLES *********
			if(in_array('employee',$banner_roles) || $person_attributes['role']['usnh'] ) {
				//they're an employee break it down
				foreach($lists as $list) {
					if( 'usnh_employees' == $list ) {
						$role = 'usnh';
					} else {
						$role = $list;
					}//end else

					if($person_attributes['role'][$role]) {
						if(!in_array($list,$groups)) $add_groups[] = $list;
					} else {
						if(in_array($list,$groups)) $remove_groups[] = $list;
					}//end else
				}//end foreach
			} else {
				// they aren't an employee
				foreach($lists as $list) {
					if(in_array($list,$groups)) $remove_groups[] = $list;
				}//end foreach
			}//end else
			//************ END EMPLOYEE ROLE CODE ***************
	
			//********* CHECK FOR PSU FRIEND *******
			if(in_array('psu_friend',$banner_roles)) {
				//they're a friend
				if(!in_array('friends',$groups)) $add_groups[] = 'friends';
			} else {
				if(in_array('friends',$groups)) $remove_groups[] = 'friends';
			}
			//********** END PSU FRIEND ROLE CODE ********
		
			//********* CHECK FOR RETIREE *******
			if($person_attributes['role']['retiree'] || in_array( 'alumni_emeritus', $banner_roles )) {
				//they're a Retiree
				if(!in_array('retirees',$groups)) $add_groups[] = 'retirees';
			} else {
				if(in_array('retirees',$groups)) $remove_groups[] = 'retirees';
			}
			//********** END RETIREE ROLE CODE ********
	
			//********* CHECK FOR ALUMNI ROLE *******
			if(in_array('alumni',$banner_roles)) {
				//they're alumni
				if(!in_array('alumni',$groups)) $add_groups[] = 'alumni';
			} else {
				if(in_array('alumni',$groups)) $remove_groups[] = 'alumni';
			}
			//********** END ALUMNI ROLE CODE ********
	
			//********* CHECK FOR ALUMNI CAMPUS ROLE *******
			if(in_array('alumni_campus',$banner_roles)) {
				//they're alumni
				if(!in_array('alumni_campus',$groups)) $add_groups[] = 'alumni_campus';
			} else {
				if(in_array('alumni_campus',$groups)) $remove_groups[] = 'alumni_campus';
			}
			//********** END ALUMNI ROLE CODE ********
	
			//********* CHECK FOR ALUMNI ONLY *******
			if(in_array('alumni',$banner_roles) && count($banner_roles) == 1) {
				//they're alumni and have no other roles
				if(!in_array('alumni_only',$groups)) $add_groups[] = 'alumni_only';
			} else {
				if(in_array('alumni_only',$groups)) $remove_groups[] = 'alumni_only';
			}
			//********** END ALUMNI ONLY ROLE CODE ********

			//********* CHECK FOR PENDING DELETION *******				
			if(empty($banner_roles)) {
				//they have no valid banner roles that would cause account creation
				if(!in_array('pending_deletion',$groups)) $add_groups[] = 'pending_deletion';
			} else {
				if(in_array('pending_deletion',$groups)) $remove_groups[] = 'pending_deletion';
			}
			//********** END PENDING DELETION ********

			$this->addUserToGroup($username,$add_groups);
			$this->removeUserFromGroup($username,$remove_groups);
		} catch(Exception $e) {
			return false;
		}//end catch
 	}//end syncGroups
 	
	/**
	 * __construct
	 *
	 * fixes all the of options for passing up to the parent constructer
	 *
	 * @param array $options
	 */
	function __construct($options = array()) {
		$conf = PSUDatabase::connect('ldap/password','return');
		$conf['password'] = PSUSecurity::password_decode($conf['password']);
		
		if(empty($options)) {
			$options['account_suffix']="@plymouth.edu";
			$options['base_dn']=$conf['dn'];
			$options['domain_controllers']=array($conf['hostname'],$conf['hostname2']);
			$options['ad_username']=$conf['username'];
			$options['ad_password']=$conf['password'];
			$options['real_primarygroup']=true;
			$options['use_ssl']=true;
			$options['recursive_groups']=true;
		}

		parent::__construct($options);
	}//end __construct
}//end class PSUadLDAP
