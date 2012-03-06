<?php

/**
 * search.class.php
 *
 * === Modification History ===
 * 1.1.0  11-sep-2006  [zbt] original
 * 1.2.0  26-may-2007  [zbt] added LIV compatibility and searchGoogle
 * 1.2.1  19-jun-2007  [zbt] added searchTLC
 * 1.2.2  16-jan-2008  [zbt] changed ldap connection code
 * 1.3.0  18-jan-2008  [zbt] changed all portal ldap lookups to be done by portal.class.php
 *
 * @package 		Tools
 */

/**
 * search.class.php
 *
 * search functions
 *
 * @version		1.2.2
 * @module		search.class.php
 * @author		Zachary Tirrell <zbtirrell@plymouth.edu>
 * @copyright 2007, Plymouth State University, ITS
 */ 

class searchPSU
{
	var $mysql_db, $tlc_db;
/**
  *search
  *
  *searchPSU TODO WTF
  *
  *@return boolean
  *
  */
	function searchPSU()
	{
		return true;
	}//end seachPSU
/**
  *searchLdap
  *
  *searches database and returns the information on the matches consistant with the search term
  *
  *@param integer $which
  *@param integer $search_type
  *@param string $search_term
  *@param mixed $max_result
  *@return array
  *
  */
	function searchLdap($which, $search_type, $search_term, $max_results=0)
	{
		/*
		which is 
		 0=>all 
		 1=>fac/staff
		 2=>student
		 3=>department			
		
		search_type is what to search on, valid search types:
		 1=>first_name
		 2=>last_name
		 3=>username
		 4=>extention
		 5=>exact username
		 6=>department
		 7=>full_name
		 8=>all
		*/

		if(empty($search_term) || $search_type<1 || $search_type>8)
		{
			return 0;
		}//end if

		switch($search_type)
		{
			case 1: // first_name
				$type = 'givenName';
				$search = $search_term.'*';
				break;
			case 2: // last_name
				$type = 'sn';
				$search = '*'.$search_term.'*';
				break;
			case 3: // username
				$type = 'pdsLoginId';
				$search = $search_term.'*';
				break;
			case 4: // extention
				$type = 'telephonenumber';
				$search = '*'.$search_term.'*';
				break;
			case 5: // complete username 
				$type = 'pdsLoginId';
				$search = $search_term;
				break;
			case 6: // department 
				$type = 'ou';
				$search = $search_term;
				break;
			case 7: // full name 
				$type = 'cn';
				$search = '*'.$search_term.'*';
				break;
			case 8: // any (slow)
				return 'Error: invalid search type';
				break;
		}//end switch

		$info = array();

		require_once 'portal.class.php';
		$portal = new Portal();

		$data = $portal->getUserInfo($search,$type,true);
		
		if(is_array($data))
		{
			foreach($data as $person)
			{
				$i++;
				if($which==0
					|| ($which==1 && is_array($person['pdsrole']) && in_array('employee',$person['pdsrole']))
					|| ($which==2 && is_array($person['pdsrole']) && in_array('student_active',$person['pdsrole']))
				    || ($which==3 && is_array($person['pdsrole']) && in_array('employee',$person['pdsrole'])))
				{
					$info[$person['last_name'].$person['first_name'].$person['username']] = $person;
				}//end if
			}//end foreach

			ksort($info);
		}//end if

		return $info;
	}//end searchLdap

/**
  *searchUserDB
  *
  *takes in specifications and returns an array with the results of the DB search
  *
  *@param string $search_term
  *@param integer search_type
  *@return array
  *
  */
	function searchUserDB($search_type, $search_term)
	{
		/*
		search_type is what to search on, valid search types:
		 1=>first_name
		 2=>last_name
		 3=>username
		 4=>UNSUPPORTED
		 5=>exact username
		*/

		if(!$this->_connectUserDB())
		{
			return array();
		}//end if 

		$where = '';

		// this should be changed to use an ADOdb function, but right now this is working...
		// didn't work: $this->mysql_db->qstr($search_term);
		$search_term = mysql_real_escape_string($search_term);
		switch($search_type)
		{
			case 1:
				$where = "user_first LIKE '$search_term%'";
			break;
			case 2:
				$where = "user_last LIKE '$search_term%'";
			break;
			case 3:
				$where = "user_uname LIKE '$search_term%'";
			break;
			case 5:
				$where = "user_uname='$search_term'";
			break;
			default:
				return array();
		}//end switch

		$users = array();
		$sql = "SELECT pidm, user_uname, user_first, user_last, user_active, user_alumni FROM USER_DB WHERE (user_active=1 OR user_alumni=1) AND $where ORDER BY user_last,user_first,user_uname";

		$res = $this->mysql_db->Execute($sql);
		while($user = $res->FetchRow())
		{
			$users[$user['user_last'].$user['user_first'].$user['user_uname']] = array('first_name'=>$user['user_first'], 'last_name'=>$user['user_last'], 'username'=>$user['user_uname'], 'phone'=>'N/A', 'title'=>'N/A', 'class'=>($user['user_alumni'])?'Alumni':'N/A');
		}//end while

		return $users;
	}//end searchUserDB
	
/**
  *searchTLC
  *
  *creates and array of cms information 
  *
  *@params string $q
  *@params integer $page_size
  *@return array
  */

	function searchTLC($q, $page_size=10)
	{
		if(!$this->_connectTLCDB())
		{
			return array();
		}//end if

		$links = array();

		$res = $this->tlc_db->Execute("SELECT id, cms_path, title, MATCH(title, content) AGAINST ('{$q}') AS relevance, content FROM cache_help WHERE MATCH(title, content) AGAINST ('{$q}') HAVING relevance > 0 ORDER BY relevance DESC LIMIT $page_size");

		while($link = $res->FetchRow())
		{
			list($desc) = explode('|||',wordwrap(html_entity_decode(strip_tags($link['content'])),100,'|||'));
			$desc.='...';

			$links[] = array('url'=>str_replace('www/', 'http://www.plymouth.edu/', $link['cms_path']), 'title'=>$link['title'], 'desc'=>$desc);
		}//end while

		return $links;
	}//end searchTLC

/**
  *_coonectUserDB
  *
  *sets up a connection with user info portion of the db
  *
  *@return boolean
  */
	function _connectUserDB()
	{
		if(is_object($this->mysql_db))
		{
			return true;
		}//end if
		
		require_once 'PSUDatabase.class.php';
		$this->mysql_db = PSUDatabase::connect('mysql/user_info-admin');

		return true;
	}//end _connectUserDB
/**
  * _connectTLCDB
  *
  * sets up a db connection with the mysql calllog portion 
  *
  *@return boolean
  */

	function _connectTLCDB()
	{
		if(is_object($this->tlc_db))
		{
			return true;
		}//end if

		require_once 'PSUDatabase.class.php';
		$this->tlc_db = PSUDatabase::connect('mysql/calllog');

		return true;
	}//end _connectTLCDB
}//end seachPSU
