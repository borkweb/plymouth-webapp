<?php

/*
 * webct.class.php
 *
 * === Modification History ===
 * 0.1.0  16-may-2006  [zbt]  original
 * 0.2.0  13-jun-2006  [zbt]  added getCourseByCRN function
 * 0.3.0  27-feb-2008  [djb]  updated connection scheme
 *
 *
 * @package 		Tools
 */

/**
 * WebCT function library
 *
 * @version		0.2.0
 * @module		webct.class.php
 * @author		Zachary Tirrell <zbtirrell@plymouth.edu>
 * @copyright 2006, Plymouth State University, ITS
 */ 
class WebCT
{
	var $_which;
	var $_db;

	/**
	 * attempts to connect to the production database
	 *
	 * @param string $which
	 * @return boolean
	 */
	function __construct($which='prod')
	{
		return $this->connectDB($which);
	}//end WebCT

	/**
	 * attempts to establish a connection with the passed in DB
	 *
	 * @param string $which
	 * @return boolean
	 */
	function connectDB($which)
	{
		//$this->_db = PSUDatabase::connect('oracle/webct_portal'); // type of connection

		if($this->_db) // check if able to connect ok
		{
			return true;
		}//end if

		return false;
	}//end connectDB

	/**
	 * retrieves all the course information for a specific user
	 *
	 * @param string $username
	 * @param mixed $crn
	 * @return
	 */
	function getCourses($username,$crn=false)
	{
		$courses = array();
/*
		$sql = "
		SELECT l.id AS section_id, l.name AS course_number, l.description AS title, l.orgname AS department, l.source_id AS source, m.id as member_id,c.name as category, l.parent_id
		FROM webct.learning_context l, webct.person p, webct.member m, webct.lc_categorization z, webct.lc_category c
		WHERE l.id=m.learning_context_id 
		AND p.id=m.person_id 
		AND l.type_code='Section' 
		AND p.activestatus=1 
		AND z.learning_context_id=l.parent_id
		AND z.lc_category_id=c.id
		AND c.name <> 'Sandbox'
		AND m.status_flag=1
		AND p.webct_id='$username'";

		if($crn)
		{
			$sql .= " AND l.source_id LIKE '$crn%'";
		}//end if
		
		$res = $this->_db->Execute($sql);

		while($row = $res->FetchRow())
		{
			$row['ROLE'] = array();
			$role_res = $this->_db->Execute("SELECT rd.name as role FROM webct.role r, webct.role_definition rd WHERE member_id={$row['MEMBER_ID']} AND r.role_definition_id=rd.id AND r.delete_status=0");
			while($role = $role_res->FetchRow())
			{
				$row['ROLE'][] = $role['ROLE'];
				$role['ROLE'] = ($role['ROLE']=='SDES' || $role['ROLE']=='STEA')?'SINS':$role['ROLE'];
				$roles[$role['ROLE']] = true;
			}//end while

			// exclude course that a user has dropped/benn disassociated with.
			if(empty($row['ROLE']))
			{
				continue;
			}//end if

			$row['INSTRUCTOR'] = array();
			$intstruct_res = $this->_db->Execute("SELECT p.webct_id AS username, p.name_n_family AS last_name, p.name_n_given AS first_name, p.name_n_prefix AS prefix, p.name_n_suffix AS suffix FROM webct.member m, webct.role r, webct.role_definition rd, webct.person p WHERE m.person_id=p.id AND r.member_id=m.id AND rd.id=r.role_definition_id AND rd.name='SINS' AND p.activestatus=1 AND r.delete_status=0 AND m.learning_context_id={$row['SECTION_ID']}");
			while($instructor = $intstruct_res->FetchRow())
			{
				$row['INSTRUCTOR'][] = $instructor;
			}//end while
			
			if($row['TITLE']=='')
			{
				$sql = "SELECT name FROM webct.learning_context WHERE id={$row['PARENT_ID']}";
				$row['TITLE'] = $this->_db->GetOne($sql);
				if($row['TITLE']=='')
				{
					$row['TITLE'] = '[No Title]';
				}//end if
			}//end if

			$row['COURSE_NUMBER'] = preg_replace('/ [0-9]/','',$row['COURSE_NUMBER']);

			list($row['CRN'],$row['TERM']) = explode('.',$row['SOURCE']);
			if($row['TERM']!='200620' && $row['TERM']!='200692' && $row['TERM']!='200685' && $row['TERM']!='200680')
			{
				$terms[$row['TERM']] = true;
				$row['SYS'] = '6';
				$courses[] = $row;
			}//end if
		}//end while
 */
		return $courses;
	}//end getCourses

	/**
	 * searches for courses using the crn as the username
	 *
	 * @param mixed $crn
	 * @param string $username
	 * @return string
	 */
	function getCourseByCRN($crn,$username)
	{
		//$temp = $this->getCourses($username,$crn);
		//$course = array_pop($temp);
		return $course;
	}//emd getCourseByCRN

	/**
	 * returns database information on the user 
	 *
	 * @param string $username
	 * @return mixed
	 */
	function getUserInfo($username)
	{
		/*$sql = "SELECT id, webct_id, remote_userid, name_n_family, name_n_given FROM webct.person WHERE webct_id='$username' AND deletestatus is null";
		$person = $this->_db->GetRow($sql);
		if($person)
		{
			return $person;
		}//end if
		 */
		return false;
	}//end getUserInfo
}//end WebCT
