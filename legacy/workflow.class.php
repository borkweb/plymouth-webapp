<?php

/**
 * workflow.class.php
 *
 * === Modification History ===<br/>
 * 0.1.0  05-Dec-2008  [djb]  original<br/>
 * 0.2.0  15-Dec-2008  [djb]  updated functionality
 * 0.5.0  15-Jan-2009  [djb]  all user and attribute functions in
 * 0.6.0  18-Sep-2009  [djb]  Added postExternalEvent
 * 0.7.0  29-Mar-2010  [djb]  Added functions for channel functionality
 *
 */

/**
 * workflow.class.php
 *
 * SungardHE Workflow API
 *
 * @version		0.7.0
 * @module		workflow.class.php
 * @author		Dan Bramer <djbramer@plymouth.edu>
 * @copyright 2008-2009, Plymouth State University, ITS
 */ 

class Workflow
{
	/**
	 * SOAP client object.
	 * @name		$_client
	 * @since		version 0.1.0
	 */
	private $_client;

	/**
	 * Workflow SOAP authentication string
	 * @name		$_authentication
	 * @since		version 0.1.0
	 */
	private $_authentication; // auth string for lookup


	/**
	 * addHiringRolesToUser
	 * Adds the two PSU hiring roles to a workflow user
	 *
	 * @param  $search \b of user's identification
	 * @param  $type \b type of id passed in
	 * @return  workflow primary key
	 */
	function addHiringRolesToUser($search,$type='username') 
	{
		$userPK = $this->getID($type,'workflowpk',$search);
		$attr1 = array(
			'roleName' => 'Hourly Hire Coordinator',
			'startDate' => strtotime('now'),
			'organizationName' => 'PSU'
		);
		$attr2 = array(
			'roleName' => 'Work Study Coordinator',
			'startDate' => strtotime('now'),
			'organizationName' => 'PSU'
		);
	
		$key = array('key' => $userPK);
		$attr1['userPK'] = $key;
		$attr2['userPK'] = $key;
		$request = array('authentication' => $this->_authentication, 'roleAssignment' => $attr1);
		$info =  $this->makeRequest($request,'addRoleAssignmentForUser');
		$request = array('authentication' => $this->_authentication, 'roleAssignment' => $attr2);
		$info =  $this->makeRequest($request,'addRoleAssignmentForUser');
		return $info->assignmentPK->key;
	}

	/**
	 * addRoleAssignmentForUser
	 * Add a role to a workflow user
	 *
	 * @param  $attrin \b of user's role name to add
	 * @param  $search \b of user's identification
	 * @param  $type \btype of id passed in
	 * @return  workflow primary key
	 */
	function addRoleAssignmentForUser($attrin,$search,$type='username') 
	{
		if(!is_array($attrin))
		{
			parse_str($attrin, $attrin);
		}//end if
		$userPK = $this->getID($type,'workflowpk',$search);
		$default_attr = array(
			'startDate' => strtotime('now'),
			'organizationName' => 'PSU'
		);
	
		$attrs = array_merge($default_attr, $attrin);
		$key = array('key' => $userPK);
		$attrs['userPK'] = $key;
		$request = array('authentication' => $this->_authentication, 'roleAssignment' => $attrs);
		$info =  $this->makeRequest($request,__FUNCTION__);
		return $info->assignmentPK->key;
	}

	/**
	 * createUser
	 *
	 * Create a new workflow user
	 *
	 * @param  $attrin \b of new user's attributes ('logonID','lastName','firstName',and 'emailAddress' must be specified, and logonID must match a current Luminis username. Non-required array items are found in the source.
	 * @return newly created user's workflow primary key (workflowpk)
	 */
	function createUser($attrin) 
	{
		if(!is_array($attrin))
		{
			parse_str($attrin, $attrin);
		}//end if
		if ((!isset($attrin['logonID'])) || (!isset($attrin['lastName'])) || (!isset($attrin['firstName'])) || (!isset($attrin['emailAddress'])))
		{
			return "Input array missing required element. logonID, firstName, lastName, and emailAddress are required.";
		}
		$default_attr = array(
			'middleName' => '',
			'effectiveFrom' => strtotime('now'),
//			'effectiveTo' => ,
			'enabled' => true
		);	
		$attrs = array_merge($default_attr, $attrin);
		$immid = $attrs['logonID'];
		$userAuthMethod = array('externalID'=>$immid);
		//$userAuthMethod = array('password'=>'', 'externalID'=>$immid);
		$request = array('authentication' => $this->_authentication, 'attributes' => $attrs,'authenticationMethod'=>$userAuthMethod);
		$info =  $this->makeRequest($request,__FUNCTION__);
		return $info->userPK->key;
	}


	/**
	 * deleteUser
	 *
	 * Delete an existing workflow user
	 *
	 * @param $search \b identifier
	 * @param $type \b type of identifier in $search (workflowpk, externalid, or username)
	 */
	function deleteUser($search,$type='username') // other types: workflowpk and externalid
	{
		$primaryKey = array('key' => $this->getID($type,'workflowpk',$search));
		$request = array('authentication' => $this->_authentication, 'userPK' => $primaryKey);
		$this->makeRequest($request,__FUNCTION__);
	}


	/**
	 * findUserByExternalID
	 *
	 * Converts External ID into one of the other id types
	 *
	 * @param  $search \b user's external id
	 * @param  $outtype \b id type to return (username or workflowpk)
	 * @return  $outtype
	 */
	function findUserByExternalID($search, $outtype='username') 
	{ 
		$primaryKey = array('key' => $this->getID('externalid','workflowpk',$search));
		$request = array('authentication' => $this->_authentication, 'externalID' => $search);
		$out = $this->makeRequest($request,__FUNCTION__)->userPK->key;
		return $this->getID('workflowpk',$outtype,$out);
	}


	/**
	 * findUsersWhoAreExternallyAuthenticated
	 *
	 * Retruens array of all externally authenticated users, regardless of whether or not they are logged in
	 *
	 * @param  $outtype \b type of id to return (username, workflowpk, or externalid)
	 * @return  array of users displayed by $outtype
	 */
	function findUsersWhoAreExternallyAuthenticated($outtype='username') // other types: workflowpk and externalid
	{
		$info = $this->makeRequest(array('authentication' => $this->_authentication),__FUNCTION__);
		foreach($info->userPK as $pk)
		{
			$users[] = $this->getId('workflowpk',$outtype,$pk->key);
		}
		return $users;
	}


	/**
	 * getAlertsCount
	 *
	 * Returns whether the user has hiring roles
	 *
	 * @param  $search \b user's id
	 * @param  $type \b type of id in $search (username, workflowpk, or externalid)
	 * @return  boolean is the user in workflow
	 */
	function getAlertsCount($username)
	{
		$alertslist = array();
		$sql = "SELECT count(ewf.id)
                                  FROM workflow.eng_workitem ewi,
                                           workflow.eng_workflow ewf,
                                           workflow.eng_workflow_alert ewa,
                                           workflow.process pro,
                                           workflow.process_definition prodef,
                                           workflow.role rle,
                                           workflow.role_assignment ras,
                                           workflow.wfuser u
                                 WHERE ewi.wf_id = ewf.id
                                   AND ewi.current_state = 'started.stalled'
                                   AND ewa.wf_id = ewf.id
                                   AND ewi.id = ewa.workitem_id
                                   AND ewf.originating_process_id = pro.id
                                   AND u.logon = :username
                                   AND ras.user_id = u.id
                                   AND rle.id = ras.role_id
                                   AND ewi.pd_id = prodef.id
                                   AND prodef.owner = rle.id";
		$numalerts = PSU::db('banner')->GetOne($sql,array('username'=>$username));
		$sql = "SELECT count(ewf.id)
                                  FROM workflow.eng_workitem ewi,
                                           workflow.eng_workflow ewf,
                                           workflow.eng_workflow_alert ewa,
                                           workflow.process_definition pro,
                                           workflow.role rle,
                                           workflow.role_assignment ras,
                                           workflow.wfuser u
                                 WHERE ewi.wf_id = ewf.id
                                   AND ewi.current_state = 'started.suspended'
                                   AND ewa.wf_id = ewf.id
                                   AND ewi.id = ewa.workitem_id
                                   AND u.logon = :username
                                   AND ras.user_id = u.id
                                   AND rle.id = ras.role_id
                                   AND ewi.pd_id = pro.id
                                   AND pro.owner = rle.id";
		return $numalerts + PSU::db('banner')->GetOne($sql,array('username'=>$username));
	}


/**
	 * getAlerts
	 *
	 * Returns whether the user has hiring roles
	 *
	 * @param  $search \b user's id
	 * @param  $type \b type of id in $search (username, workflowpk, or externalid)
	 * @return  boolean is the user in workflow
	 */
	function getAlerts($username,$numitems=10)
	{
		$alertslist = array();
		//This sql pulls all error-style alerts
		$sql = "SELECT ewf.name wf_name,
					   ewf.id wf_id,
					   ewi.id wf_itemid,
					   ewa.msg_param wa_param,
					   ewa.originating_error wa_error,
					   ewf.start_date wf_start,
					   pro.name pro_name
				  FROM workflow.eng_workitem ewi,
					   workflow.eng_workflow ewf,
					   workflow.eng_workflow_alert ewa,
					   workflow.process pro,
                                           workflow.process_definition prodef,
                                           workflow.role rle,
                                           workflow.role_assignment ras,
                                           workflow.wfuser u
				 WHERE ewi.wf_id = ewf.id
				   AND ewi.current_state = 'started.stalled'
				   AND ewa.wf_id = ewf.id
				   AND ewi.id = ewa.workitem_id
				   AND ewf.originating_process_id = pro.id
                                   AND u.logon = :username
                                   AND ras.user_id = u.id
                                   AND rle.id = ras.role_id
                                   AND ewi.pd_id = prodef.id
                                   AND prodef.owner = rle.id
				 ORDER BY wf_start ASC";
		if ($results = PSU::db('banner')->SelectLimit($sql,$numitems,-1,array('username'=>$username)))
		{
			foreach($results as $row)
			{
				$alertslist[] = $row;
			}
		}
		$numitems_2 = count($alertslist) >= $numitems ? 0 : $numitems - count($alertslist);
		if ($numitems > 0)
		{
		//This sql pulls all workflow that users have requested to stop ... technically an alert by workflow standars
		$sql = "SELECT ewf.name wf_name,
					   ewf.id wf_id,
					   ewi.id wf_itemid,
					   ewa.msg_param wa_param,
					   ewa.originating_error wa_error,
					   ewf.start_date wf_start
				  FROM workflow.eng_workitem ewi,
					   workflow.eng_workflow ewf,
					   workflow.eng_workflow_alert ewa,
                                           workflow.process_definition pro,
                                           workflow.role rle,
                                           workflow.role_assignment ras,
                                           workflow.wfuser u
				 WHERE ewi.wf_id = ewf.id
				   AND ewi.current_state = 'started.suspended'
				   AND ewa.wf_id = ewf.id
				   AND ewi.id = ewa.workitem_id
                                   AND u.logon = :username
                                   AND ras.user_id = u.id
                                   AND rle.id = ras.role_id
                                   AND ewi.pd_id = pro.id
                                   AND pro.owner = rle.id
  				 ORDER BY wf_start DESC";
			if ($results = PSU::db('banner')->SelectLimit($sql,$numitems_2,-1,array('username'=>$username)))
			{
				foreach($results as $row)
				{
					$alertslist[] = $row;
				}
				for($i=($numitems-$numitems_2);$i<count($alertslist);$i++)
				{
					$alertslist[$i]['wa_param'] = 'A stop has been requested for this workflow';
				}
			}
		}
		return $alertslist;
	}


/**
	 * getChannelCounts
	 *
	 * Returns whether the user has hiring roles
	 *
	 * @param  $search \b user's id
	 * @param  $type \b type of id in $search (username, workflowpk, or externalid)
	 * @return  boolean is the user in workflow
	 */
	function getChannelCounts($username)
	{
		$counts = array();
		return array (
			'worklist' => self::getWorklistCount($username),
			'reserved' => self::getReservedCount($username),
			'alerts' => self::getAlertsCount($username),
			'processes' => self::getProcessesCount($username)
		);		
	}


	/**
	 * getExternalIDForUser
	 *
	 * Retrieves External ID from one of the other id types
	 *
	 * @param  $search \b user's id
	 * @param  $type \b id type coverting from (username or workflowpk)
	 * @return  string of externalid
	 */
	function getExternalIDForUser($search, $type='workflowpk') // other type: username
	{ 
		$primaryKey = array('key' => $this->getID($type,'workflowpk',$search));
		$request = array('authentication' => $this->_authentication, 'userPK' => $primaryKey);
		return $this->makeRequest($request,__FUNCTION__)->externalID;
	}

	
	/**
	 * getID
	 *
	 * Converts Worflow specific ids
	 *
	 * @param  $from \b type of id coverting from (username, workflowpk, or externalid)
	 * @param  $to \b type of id coverting to (username, workflowpk, or externalid)
	 * @param  $inval \b original id
	 * @return  string converted id
	 */
	function getId($from,$to,$inval)
	{
		if ($from == $to) return $inval;
		switch ($to) :
			case 'workflowpk';
				if ($from == 'username')
				{
					$externalID = $inval;
					$request = array('authentication' => $this->_authentication, 'externalID' => $externalID);
					return $this->makeRequest($request,'findUserByExternalID')->userPK->key;
				} else if ($from == 'externalid')
				{
					$request = array('authentication' => $this->_authentication, 'externalID' => $inval);
					return $this->makeRequest($request,'findUserByExternalID')->userPK->key;
				}
				break;
			case 'username';
				if ($from == 'workflowpk')
				{
					return $this->getExternalIDForUser($inval);
				} else if ($from == 'externalid')
				{
					return $inval;
				}
				break;
			case 'externalid';
				if ($from == 'username')
				{
					return $this->getExternalIDForUser($inval,'username');				
				} else if ($from == 'workflowpk')
				{
					return $this->getExternalIDForUser($inval,'workflowpk');				
				}
				break;
		endswitch;
	}


/**
	 * getProcessesCount
	 *
	 * Returns whether the user has hiring roles
	 *
	 * @param  $search \b user's id
	 * @param  $type \b type of id in $search (username, workflowpk, or externalid)
	 * @return  boolean is the user in workflow
	 */
	function getProcessesCount($username)
	{
		$sql = "SELECT count(p.name) 
				  FROM workflow.process p,
					   workflow.initiator_role ir, 
					   workflow.wfuser u, 
					   workflow.role_assignment rass, 
					   workflow.role r
				 WHERE p.id = ir.process_id
				   AND u.id = rass.user_id
				   AND r.id = rass.role_id
				   AND r.id = ir.role_id
				   AND u.logon=:username";
		return PSU::db('banner')->GetOne($sql,array('username'=>$username));
	}


/**
	 * getProcesses
	 *
	 * Returns whether the user has hiring roles
	 *
	 * @param  $search \b user's id
	 * @param  $type \b type of id in $search (username, workflowpk, or externalid)
	 * @return  boolean is the user in workflow
	 */
	function getProcesses($username,$numitems=10)
	{
		$processlist = array();
		$sql = "SELECT p.name,
					   p.id
				  FROM workflow.process p,
					   workflow.initiator_role ir, 
					   workflow.wfuser u, 
					   workflow.role_assignment rass, 
					   workflow.role r
				 WHERE p.id = ir.process_id
				   AND u.id = rass.user_id
				   AND r.id = rass.role_id
				   AND r.id = ir.role_id
				   AND u.logon=:username";
		if ($results = PSU::db('banner')->SelectLimit($sql,$numitems,-1,array('username'=>$username)))
		{
			foreach($results as $row)
			{
				$processlist[] = $row;
			}
		}
		return $processlist;
	}


/**
	 * getReservedCount
	 *
	 * Returns whether the user has hiring roles
	 *
	 * @param  $search \b user's id
	 * @param  $type \b type of id in $search (username, workflowpk, or externalid)
	 * @return  boolean is the user in workflow
	 */
	function getReservedCount($username)
	{
		$sql = "SELECT count(ewi.id)
				  FROM workflow.eng_workitem ewi, 
				       workflow.eng_workflow ewf, 
				       workflow.wfuser u 
				 WHERE ewi.reserved_date is not null 
				   AND ewi.current_state like 'started.running.%'
				   AND ewf.id = ewi.wf_id
				   AND u.id = ewi.worklist_owner
				   AND u.logon = :username";
		return PSU::db('banner')->GetOne($sql,array('username'=>$username));
	}


/**
	 * getReserved
	 *
	 * Returns whether the user has hiring roles
	 *
	 * @param  $search \b user's id
	 * @param  $type \b type of id in $search (username, workflowpk, or externalid)
	 * @return  boolean is the user in workflow
	 */
	function getReserved($username,$numitems=10)
	{
		$reservedlist = array();
		$sql = "SELECT ewf.name wf_name,
					   ewf.id wf_id,
					   ewi.id wf_itemid,
                       ewi.name wf_itemname,
                       translate(ewi.name,'_',' ') wf_cleanitemname,
					   ewf.start_date wf_start
				  FROM workflow.eng_workitem ewi, 
				       workflow.eng_workflow ewf, 
				       workflow.wfuser u 
				 WHERE ewi.reserved_date is not null 
				   AND ewi.current_state like 'started.running.%'
				   AND ewf.id = ewi.wf_id
				   AND u.id = ewi.worklist_owner
				   AND u.logon = :username
				 ORDER BY wf_start DESC";
		if ($results = PSU::db('banner')->SelectLimit($sql,$numitems,-1,array('username'=>$username)))
		{
			foreach($results as $row)
			{
				$reservedlist[] = $row;
			}
		}
		return $reservedlist;
	}


/**
	 * getUserAttributes
	 *
	 * Retrieves a user's workflow attributes
	 *
	 * @param $search \b user's id
	 * @param $type \b type of id in $search (username, workflowpk, or externalid)
	 * @return  array of attributes
	 */
	function getUserAttributes($search,$type='username') // other types: workflowpk and externalid
	{
		$primaryKey = array('key' => $this->getID($type,'workflowpk',$search));
		$request = array('authentication' => $this->_authentication, 'userPK' => $primaryKey);
		return $this->makeRequest($request,__FUNCTION__)->attributes;
	}


/**
	 * getWorklistCount
	 *
	 * Returns whether the user has hiring roles
	 *
	 * @param  $search \b user's id
	 * @param  $type \b type of id in $search (username, workflowpk, or externalid)
	 * @return  boolean is the user in workflow
	 */
	function getWorklistCount($username)
	{
		$sql = "SELECT count(ewi.id)
				  FROM workflow.wfuser u, 
					   workflow.role_assignment rass, 
					   workflow.role r,
					   workflow.eng_workitem ewi,
					   workflow.eng_workflow ewf
				 WHERE u.id = rass.user_id
				   AND r.id = rass.role_id
				   AND r.id = ewi.role_id
				   AND ewi.wf_id = ewf.id
				   AND ewf.current_state = 'started.running'
				   AND ewi.current_state like 'started.running%'
				   AND u.logon= :username
				   AND ewi.id NOT IN (
					SELECT distinct(ewi2.id)
					  FROM workflow.eng_workitem ewi2, 
						   workflow.eng_workflow ewf2, 
						   workflow.wfuser u2 
					 WHERE ewi2.reserved_date IS NOT NULL 
					   AND ewi2.current_state like 'started.running.%'
					   AND ewf2.id = ewi2.wf_id
					   AND u2.id = ewi2.worklist_owner
					)";
		return PSU::db('banner')->GetOne($sql,array('username'=>$username));
	}


/**
	 * getWorklist
	 *
	 * Returns whether the user has hiring roles
	 *
	 * @param  $search \b user's id
	 * @param  $type \b type of id in $search (username, workflowpk, or externalid)
	 * @return  boolean is the user in workflow
	 */
	function getWorklist($username,$numitems=10)
	{
		$worklist = array();
		$sql = "SELECT ewf.name wf_name,
					   ewf.id wf_id,
                       ewi.name wf_itemname,
                       translate(ewi.name,'_',' ') wf_cleanitemname,
					   ewi.id wf_itemid,
                       ewf.start_date wf_start
				  FROM workflow.wfuser u, 
					   workflow.role_assignment rass, 
					   workflow.role r,
					   workflow.eng_workitem ewi,
					   workflow.eng_workflow ewf
				 WHERE u.id = rass.user_id
				   AND r.id = rass.role_id
				   AND r.id = ewi.role_id
				   AND ewi.wf_id = ewf.id
				   AND ewf.current_state = 'started.running'
				   AND ewi.current_state like 'started.running%'
				   AND u.logon= :username
				   AND ewi.id NOT IN (
					SELECT distinct(ewi2.id)
					  FROM workflow.eng_workitem ewi2, 
						   workflow.eng_workflow ewf2, 
						   workflow.wfuser u2 
					 WHERE ewi2.reserved_date IS NOT NULL 
					   AND ewi2.current_state like 'started.running.%'
					   AND ewf2.id = ewi2.wf_id
					   AND u2.id = ewi2.worklist_owner
					)
				  ORDER BY wf_start DESC";
		if ($results = PSU::db('banner')->SelectLimit($sql,$numitems,-1,array('username'=>$username)))
		{
			foreach($results as $row)
			{
				$worklist[] = $row;
			}
		}
		return $worklist;
	}


/**
	 * hasHiringRoles
	 *
	 * Returns whether the user has hiring roles
	 *
	 * @param  $search \b user's id
	 * @param  $type \b type of id in $search (username, workflowpk, or externalid)
	 * @return boolean is the user in workflow
	 */
	function hasHiringRoles($search,$type='username') // other types: workflowpk and externalid
	{
		if(!$this->isWorkflowUser($search,$type))
		{
			return false;
		}
		$primaryKey = $this->getID($type,'workflowpk',$search);
		$sql = "SELECT count(user_id) 
				  FROM workflow.role_assignment 
				 WHERE role_id IN (1548101,10228) 
				   AND user_id = :pk 
				 GROUP BY user_id HAVING count(user_id) > 1";
		return PSU::db('banner')->GetOne($sql,array('pk'=>$primaryKey));
	}


/**
	 * isWorkflowUser
	 *
	 * Returns whether the user is in workflow already or not
	 *
	 * @param  $search \b user's id
	 * @param  $type \b type of id in $search (username, workflowpk, or externalid)
	 * @return  boolean is the user in workflow
	 */
	function isWorkflowUser($search,$type='username') // other types: workflowpk and externalid
	{
		$users = $this->findUsersWhoAreExternallyAuthenticated();
		if (in_array($search,$users))
		{
			return true;
		}
		return false;
	}


	/**
	 * makeRequest
	 *
	 * calls SOAP functions
	 *
	 * @param  $request \b SOAP request
	 * @param  $fname \b Name of SOAP function to call
	 * @return  array requested data (can also be object)
	 */
	private function makeRequest($request,$fname)
	{
		try {
			$info = $this->_client->$fname($request);
		} catch (Soapfault $exception) {
			$this->throwSoapError($fname,$exception);
		}
		return $info;
 	}

	/**
	 * postExternalEvent
	 *
	 * Fires an external event in workflow
	 *
	 * @param  $eventarr \b of event information
	 * @param  $paramarr \b of event parameter information
	 * @return  string instanciated workflow id
	 */
	function postExternalEvent($eventarr,$paramarr)
	{
		$request = array('authentication' => $this->_authentication,
						'eventName' => $eventarr['eventName'],
						'productTypeName' => $eventarr['productTypeName'],
						'externalSource' => $eventarr['externalSource'],
						'externalID' => $eventarr['externalID'],
						'externalDate' => $eventarr['externalDate'],
						'workflowName' => $eventarr['workflowName'],
						'parameter' => $paramarr);
						
		$info =  $this->makeRequest($request,__FUNCTION__);
		return $info->externalEventPK->key;
	}


	/**
	 * throwSoapError
	 *
	 * SOAP Client error message handler
	 *
	 * @param  $fname \b Name of SOAP function with error
	 * @param  $msg \b SOAP Error message
	 */
	private function throwSoapError($fname,$msg)
	{
		echo 'Exception thrown in '.$fname.': '.$msg;
	}
	
	
	/**
	 * updateUserAttributes
	 *
	 * Updates a user's attributes in workflow
	 *
	 * @param  $attrin \b of new user's attributes (not all have to be defined, only those that will change
	 * @return  string user's workflow primary key (workflowpk)
	 */
	function updateUserAttributes($attrin,$search,$type='username')
	{
		
		if(!is_array($attrin))
		{
			parse_str($attrin, $attrin);
		}//end if
		$old_attr_obj = $this->getUserAttributes($this->getID($type,'username',$search));
		
		$old_attr = array(
			'logonID' => $old_attr_obj->logonID,
			'lastName' => $old_attr_obj->lastName,
			'firstName' => $old_attr_obj->firstName,
			'middleName' => $old_attr_obj->middleName,
			'emailAddress' => $old_attr_obj->emailAddress,
			'effectiveFrom' => $old_attr_obj->effectiveFrom,
			'effectiveTo' => $old_attr_obj->effectiveTo,
			'enabled' => $old_attr_obj->enabled
			);
		
		$attrs = array_merge($old_attr, $attrin);
		$userpk = array('key' => $this->getID($type,'workflowpk',$search));
		$request = array('authentication' => $this->_authentication, 'userPK' => $userpk, 'attributes' => $attrs);
		return $this->makeRequest($request,__FUNCTION__);
	}
	

	/**
	 * UpdateUserAuthentication
	 *
	 * Retrieves a user's workflow attributes
	 *
	 * @param  $what \b user's new password
	 * @param  $search \b user's id
	 * @param  $type \b type of id in $search (username, workflowpk, or externalid)
	 * @return  string user's workflowpk
	 */
	function UpdateUserAuthentication($what,$search,$type='username') 
	{
		// This will not be used at PSU because updating user authentication via this method removes external identity from the account.
		return 'This will not be used at PSU because updating user authentication via this method removes external identity from the account.';
		/*
		$userpk = array('key' => $this->getID($type,'workflowpk',$search));
		$userAuthMethod = array('password' => $what,'externalID' => $attrs['logonID']);
		$request = array('authentication' => $this->_authentication, 'userPK' => $userpk,'authenticationMethod'=>$userAuthMethod);
		return $this->makeRequest($request,__FUNCTION__)->userPK->key;
		*/
	}


	/**
	 * workflow
	 *
	 * constructor initializes SOAP connection to the workflow wsdl
	 *
	 * @access		public
	 * @since		version 0.1.0
	 */
	function __construct($in=null)
	{
		if (is_null($in))
		{
			if (PSU::isDev())
			{
				$which='test';
				$domain = 'https://www.dev.plymouth.edu/';
			} else
			{
				$which='psc1';
				$domain = 'https://www.plymouth.edu/';
			}
		} else 
		{
			$which=$in;
		}
		$this->_instance = $which;
		try
		{
			//$wsdl_url = "https://draco.plymouth.edu/wf".$this->_instance."/ws/services/WorkflowWS/v1_1?WSDL";
			$wsdl_url = $domain."webapp/workflow/".$this->_instance.".wsdl.xml";

			if(PSU::curl($wsdl_url,PSU::FILE_GET_CONTENTS))
			{
				$this->_client = new SoapClient($wsdl_url, 
					array('trace' => 1, 
					      'connection_timeout'=>5,
					      'cache_wsdl'=>WSDL_CACHE_MEMORY)
				);
			}
			else
			{
				return false;
			}	
		}
		catch(Exception $e)
		{
			return false;
		}

		$this->_num_soap_calls++;

		include('oracle/'.$this->_instance.'_wf_webservice.php');
		$this->_authentication = array(
			'principal' => $_DB['oracle'][$this->_instance.'_wf_webservice']['username'], 
			'credential' => PSUSecurity::password_decode($_DB['oracle'][$this->_instance.'_wf_webservice']['password'])
		); // auth string for lookup
	}


}
