<?php

/*
 * ems.class.php
 *
 * === Modification History ===<br/>
 * ALPHA  17-Nov-2006  [djb]  adapted from cms.class.php (zbtirrell)<br/>
 * 0.1	  29-Jan-2007  [zbt]  tweaks and minor bug fixes
 * 0.2    6-Jan-2007   [zbt]  more functions, functionality, and sweetness
 *
 */

/**
 * EMS API for SendStudio
 *
 * @version		0.2
 * @module		ems.class.php
 * @author		Dan Bramer <djbramer@plymouth.edu>
 * @copyright 2006, Plymouth State University, ITS
 */ 

require_once 'PSUTools.class.php';
require_once 'PSUDatabase.class.php';

class EMS
{
	public $db;            // ADOdb database object
	private $_list_id = 0;
	private $_programs = null;

	public $table = array(
		'list_subscribers' => 'ss_list_subscribers',
		'subscribers_data' => 'ss_subscribers_data',
		'customfields' => 'ss_customfields',
		'queues' => 'ss_queues',
		'autoresponders' => 'ss_autoresponders'
	);

	/**
	 * ems
	 *
	 * constructor initializes sql connection to the ems
	 *
	 * @param	integer $list_id list id to be set. Defaults to 0
	 */
	function ems($list_id=0)
	{
		if( isset($_SERVER['URANUS']) ) {
			$this->db = PSUDatabase::connect('mysql/sendstudio_dev');
		} else {
			$this->db = PSUDatabase::connect('mysql/sendstudio');
		}

		$this->setListID($list_id);
	}

	/**
	 * setListID
	 *
	 * set the list id
	 *
	 * @param	integer $list_id the value to assign to the list id
	 */
	function setListID($list_id)
	{
		$this->_list_id = $list_id;
		return $this->_list_id;
	}
	
	/**
	 * getListID
	 *
	 * return the id of the current list
	 *
	 */
	function getListID()
	{
		return $this->_list_id;
	}

	/**
	 * confirmSubscriber
	 *
	 * confirm a subscriber to cms system
	 *
	 * @param	integer $subscriberid id of subscriber to confirm
	 */
	function confirmSubscriber($subscriberid)
	{
		$subscriberid = (int)$subscriberid;

		$sql = "UPDATE {$this->table['list_subscribers']}
		           SET confirmdate = UNIX_TIMESTAMP(), confirmed = 1
		         WHERE subscriberid = $subscriberid";

		if(!$this->db->Execute($sql))
		{
			throw new Exception("Unable to confirm subscription in Email Marketer");
		}
	}

	/**
	 * addMember
	 *
	 * add a member to the system
	 *
	 * @param	array $info array of information about the subscriber
	 */
	function addMember($info)
	{
		list($tmp, $info['domain']) = explode('@', $info['email']);
		$sql = "INSERT INTO {$this->table['list_subscribers']} (listid, emailaddress, subscribedate, format, confirmed, formid, domainname, confirmcode, requestdate, confirmdate, confirmip, requestip)
		             VALUES (?, ?, UNIX_TIMESTAMP(), 'h', 1, 0, ?, MD5(UNIX_TIMESTAMP()), UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), '', '')";
		$args = array($this->_list_id, $info['email'], '@' . $info['domain']);
		$results = $this->db->Execute($sql, $args);
		$member_id = $this->db->Insert_ID();

		$this->addModifyMemberAttribute($member_id, 1, $info['first_name']);
		$this->addModifyMemberAttribute($member_id, 2, $info['last_name']);
		$this->addModifyMemberAttribute($member_id, 9, $info['link']);
		$this->addModifyMemberAttribute($member_id, 10, $info['email']);
		$this->addModifyMemberAttribute($member_id, 11, $info['pw']);

		$this->setFieldOfInterest($member_id, $info['field_of_interest']);

		$this->addAutoresponders($member_id);

		return $member_id;
	}//end addMember

	/**
	 * add a member to the list for auto responders
	 *
	 * @param	$member_id \b int id of member to add to autoresponders
	 */
	function addAutoresponders($member_id)
	{
		$member_id = (int)$member_id;

		$rsQueue = $this->db->Execute("SELECT * FROM {$this->table['queues']} WHERE queueid = -1");
		$autoresponders  = $this->db->GetAll("SELECT * FROM {$this->table['autoresponders']}");

		foreach($autoresponders as $autoresponder)
		{
			$data = array(
				'queueid' => $autoresponder['queueid'],
				'queuetype' => 'autoresponder',
				'ownerid' => $autoresponder['ownerid'],
				'recipient' => $member_id,
				'processed' => 0,
				'sent' => 0,
				'processtime' => null
			);
			$sql = $this->db->GetInsertSQL($rsQueue, $data);

			$this->db->Execute($sql);
		}
	}
	
	/**
	 * _cache_fields_of_interest
	 *
	 * cache the fields of interest
	 *
	 */
	private function _cache_fields_of_interest()
	{
		$sql = "SELECT fieldsettings
		          FROM {$this->table['customfields']}
		         WHERE name = 'Program'";
		$fieldsettings = $this->db->GetOne($sql);

		$fieldsettings = unserialize($fieldsettings);

		$this->_programs = array();
		foreach($fieldsettings['Key'] as $i => $program)
		{
			$this->_programs[$program] = $i;
		}
	}//end _cache_fields_of_interest()

	/**
	 * setFieldOfInterest
	 *
	 * set fields of interest for a member
	 *
	 * @param	integer $member_id id of member
	 * @param	array $fields_of_interest array of fields of interes t to set for user
	 */
	function setFieldOfInterest($member_id, $fields_of_interest)
	{
		if($this->_programs == null)
		{
			$this->_cache_fields_of_interest();
		}

		$foi_sql = array();
		foreach($fields_of_interest as $foi)
		{
			if(isset($foi, $this->_programs))
			{
				$index = $this->_programs[$foi];
				$foi_sql[$index] = $foi;
			}
		}
		$foi_sql = serialize($foi_sql);

		$this->addModifyMemberAttribute($member_id, 15, $foi_sql);
	}//end setFieldOfInterest

	/**
	 * addModifyMemberAttribute
	 *
	 * add or modif the attribute of a member
	 *
	 * @param	integer $member_id id of member to work with
	 * @param	integer $attr id of field to change or add
	 * @param	integer $attr_value the value to set the attribute to 
	 */
	function addModifyMemberAttribute($member_id, $attr, $attr_value)
	{
		$args = array($attr, $member_id);
		$sql = "DELETE FROM {$this->table['subscribers_data']} WHERE fieldid = ? AND subscriberid = ? LIMIT 1";
		$results = $this->db->Execute($sql, $args);

		$args[] = trim($attr_value);

		$sql = "INSERT INTO {$this->table['subscribers_data']} (fieldid, subscriberid, data) VALUES (?, ?, ?)";
		$results = $this->db->Execute($sql, $args);
	}

	/**
	 * getMemberID
	 *
	 * get a members ID given their email
	 *
	 * @param	string $email email address of member
	 * @return	integer	member id
	 */
	function getMemberID($email, $listid = null)
	{
		$args = array($email);
		$sql = "SELECT subscriberid FROM {$this->table['list_subscribers']} WHERE emailaddress=?";

		if( $listid !== null )
		{
			$args[] = $listid;
			$sql .= " AND listid=?";
		}

		$this->db->debug = true;
		return $this->db->GetOne($sql, $args);
	}
	
	/**
	 * changeSubscribeDate
	 *
	 * change the date that a member subscribes
	 *
	 * @param	integer $member_id id of member
	 * @param	string $date date to change to, defaults to false
	 * @return	boolean success or failure of query
	 */
	function changeSubscribeDate($member_id, $date=false)
	{
		$sql = "UPDATE {$this->table['list_subscribers']} SET subscribedate=UNIX_TIMESTAMP() WHERE subscriberid=$member_id AND listid={$this->_list_id}";
		return $this->db->Execute($sql);
	}

	/**
	 * checkUserListStatus
	 *
	 * check the status of a user for a list
	 *
	 * @param	string $email $email address of user to check for
	 * @param	integer $list_id id of list to check in
	 * @return	integer 1 if new, 2 if in email, but not list, 3 for both
	 */
	function checkUserListStatus($email, $list_id)
	{
		// returns 
		//	1 if new
		//	2 if exists in Email but not in List
		//	3 if exists in Email & List
		$sql = "SELECT 3 FROM {$this->table['list_subscribers']} WHERE emailaddress='$email' AND listid=$list_id";
		$results=$this->db->getOne($sql);
		if ($results == 3) 
		{
			return $results;
		}
		$sql = "SELECT 2 FROM {$this->table['list_subscribers']} WHERE emailaddress='$email'";
		$results=$this->db->getOne($sql);
		if ($results == 2) 
		{
			return $results;
		}
		return 1;
	}

	/**
	 * getSubscriberId
	 *
	 * get the id of a subscriber with a particular email in a specified list
	 *
	 * @param	string $email email address of user
	 * @param	integer	$list_id id of list
	 * @return	integer the id of subscriber
	 */
	function getSubscriberId($email, $list_id)
	{
		$sql = "SELECT subscriberid FROM {$this->table['list_subscribers']} WHERE emailaddress = '$email' AND listid = $list_id";
		return $this->db->GetOne($sql);
	}//end getSubscriberId

	/**
	 * dropMember
	 *
	 * drop a member from lists
	 *
	 * @param	string $email email address of user
	 * @param	integer $list_id id of list to use to get subscriber id
	 */
	function dropMember($email, $listid)
	{
		$listid = (int)$listid;

		$sql = "SELECT subscriberid, listid FROM {$this->table['list_subscribers']} WHERE emailaddress='$email' AND listid = $listid"; 

		// loop unsubscribe user through all listIds for that user.
		if($res=$this->db->Execute($sql))
		{	
			$rows = $res->GetAll();
			foreach($rows as $row)
			{
				PSUTools::cleanKeys('','',$row);

				// drop from ss_members
				$sql = "DELETE FROM {$this->table['list_subscribers']} WHERE emailaddress='".$email."' AND subscriberid=".$row['subscriberid'];
				$this->db->Execute($sql);

				// drop from ss_list_field_values
				$sql = "DELETE FROM {$this->table['subscribers_data']}  WHERE subscriberid=".$row['subscriberid'];
				$this->db->Execute($sql);

				$this->db->Execute("DELETE FROM {$this->table['queues']} WHERE recipient = {$row['subscriberid']}");
			}//end while
		}//end if
	}

	/**
	 * changeList
	 *
	 * change the list that a user is subscribed to
	 *
	 * @param	integer $emailaddr email address of user
	 * @param	integer $listIDOld old list id
	 * @param	integer	$listIDNew new list id
	 */
	function changeList($emailaddr,$listIDOld,$listIDNew)
	{
        $recstatus = $this->checkUserListStatus($emailaddr,$listIDNew);
		if ($recstatus == 3)
		{ 
			return "Record already exists for $emailaddr in the new list\n";
		}

		$subscriberid = $this->getSubscriberId($emailaddr, $listIDOld);
		$sql = "UPDATE {$this->table['list_subscribers']} SET listid = $listIDNew WHERE listid = $listIDOld AND subscriberid = $subscriberid";
		if(!$this->db->Execute($sql))
		{
			// failure
			return false;
		}

		$updated = $this->db->Affected_Rows();

		return $updated;
	}//end changeList

	/**
	 * dropList
	 *
	 * drop a list for a user
	 *
	 * @param	string $emailaddr email address of user
	 * @param	integer	$listID id of list to drop
	 */
	function dropList($emailaddr, $listID)
	{
		$recstatus = $this->checkUserListStatus($emailaddr,$listID);

		if ($recstatus != 3)
		{ 
			return "Record does not exist for $emailaddr in this list\n";
		}

		$this->dropMember($emailaddr);
	}//end dropList

	/**
	 * unsubscribeAll
	 *
	 * unsubscribe a user from all lists
	 *
	 * @param	string $emailaddr email address of user
	 */
	function unsubscribeAll($emailaddr)
	{
		// loop unsubscribe user through all listIds for that user.
		$sql = "SELECT subscriberid, listid FROM {$this->table['list_subscribers']} WHERE emailaddress='$emailaddr'"; 

		if($results=$this->db->Execute($sql))
		{	
			while($row=$results->FetchRow())
			{
				PSUTools::cleanKeys('','',$row);
				$this->dropMember($emailaddr, $row['listid']);
			}
		}
	}//end unsubscribeAll
}//end ems
