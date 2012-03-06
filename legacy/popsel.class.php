<?php

/*
 * popsel.class.php
 *
 * === Modification History ===
 * 1.0.0  18-may-2003  [mtb]  original
 * 1.1.0  30-mar-2007  [mtb]  made this puppy class based finally
 *
 * @package 		PSUBannerAPI
 */

/**
 * Banner API
 *
 * @version		1.1.0
 * @module		popsel.class.php
 * @author		Matthew Batchelder <mtbatchelder@plymouth.edu>
 * @copyright 2007, Plymouth State University, ITS
 */ 
class PopSel
{
	var $db;

	/**
	 * PopSel constructor with db connection
	 *
	 * @since			version 1.1.0
	 * @param  		ADOdb $db ADOdb database connection
	 */
	function __construct(&$db)
	{
		$this->db=$db;
	}//end constructor

	/**
	 * Clears a given popsel selection
	 *
	 * @since			version 1.1.0
	 * @param  		string $application popsel application
	 * @param  		string $selection popsel selection
	 * @param  		string $username optional username.  If no username is specified, defaults to authenticated user
	 * @return		boolean
	 */
	function clearSelection($application,$selection,$username='')
	{
		$clean_username=($username)?strtoupper($username):strtoupper($_SESSION['username']);
		$sql="DELETE FROM glbextr WHERE glbextr_application='{$application}' AND glbextr_selection='{$selection}' AND glbextr_creator_id='{$clean_username}' AND glbextr_user_id='{$clean_username}'";
		if($this->db->Execute($sql))
			return true;
		return false;
	}//end clearSelection

	/**
	 * Creates a PopSel selection to add pidms to
	 *
	 * @since			version 1.1.0
	 * @param  		string $application popsel application
	 * @param  		string $selection popsel selection
	 * @param  		string $username optional username.  If no username is specified, defaults to authenticated user
	 * @return		boolean
	 */
	function createSelection($application,$selection,$username='')
	{
		$clean_username=($username)?strtoupper($username):strtoupper($_SESSION['username']);

		$sql="INSERT INTO glbslct 
						(glbslct_application,
						 glbslct_selection,
						 glbslct_creator_id,
						 glbslct_desc,
						 glbslct_lock_ind,
						 glbslct_activity_date) 
						VALUES
						('{$application}', 
						 '{$selection}', 
						 '{$clean_username}',
						 'Backend Pop-sel Load',
						 'N',
						 sysdate)";
		$this->db->Execute($sql);
	}//end createSelection

	/**
	 * Deletes a given popsel selection
	 *
	 * @since			version 1.1.0
	 * @param  		string $application popsel application
	 * @param  		string $selection popsel selection
	 * @param  		string $username optional username.  If no username is specified, defaults to authenticated user
	 * @return		boolean
	 */
	function deleteSelection($application,$selection,$username='')
	{
		$clean_username=($username)?strtoupper($username):strtoupper($_SESSION['username']);

		if($this->clearSelection($application,$selection))
		{
			$sql="DELETE FROM glbslct WHERE glbslct_application='{$application}' AND glbslct_selection='{$selection}' AND glbslct_creator_id='{$clean_username}'";
			if($this->db->Execute($sql))
				return true;
		}//end if
		return false;
	}//end deleteSelection

	/**
	 * Retrieves PopSel Applications
	 *
	 * @since			version 1.0.0
	 * @return		array
	 */
	function getApplications()
	{
		$data=array();
		$sql="SELECT * FROM glbappl ORDER BY glbappl_desc";
		if($results=$this->db->Execute($sql))
		{
			while($row=$results->FetchRow())
			{
				$data[]=$row;
			}//end while
		}//end if
		return $data;
	}//end getApplications

	/**
	 * Retrieves list of pidms from a given file
	 *
	 * @since			version 1.0.0
	 * @param  		string $filename filename that holds pidms
	 * @return		array
	 */
	function getPidms($filename)
	{
		if(file_exists($filename))
		{
			$pidms=array();
			$lines=file($filename);

			//grab only the first column of pidms and clean them up
			foreach($lines as $line)
			{
				if(preg_match('/.*,.*/',$line))
				{
					$words=explode(',',$line);
					$pidms[]=trim(str_replace("\t",'',$words[0]));
				}//end if
				else
				{
					$pidms[]=trim(str_replace("\t",'',$line));
				}//end else
			}//end foreach
		}//end if
		else
		{
			return false;
		}//end else
		return $pidms;
	}//end getPidms

	/**
	 * Retrieves counts for given populations
	 *
	 * @since			version 1.1.0
	 * @param  		string $type type of count being done
	 * @param  		string $selection popsel selection
	 * @param  		string $application popsel application
	 * @return		int
	 */
	function getPopselCounts($type,$selection,$application)
	{
		$clean_username=strtoupper($_SESSION['username']);
		if($type=='total')
		{
			$sql="SELECT count(glbextr_key)
								FROM glbextr 
							 WHERE glbextr_creator_id='{$clean_username}' 
								 AND glbextr_user_id='{$clean_username}' 
								 AND glbextr_selection='{$selection}'
								 AND glbextr_application='{$application}'";
		}//end if
		else
		{
			$sql="SELECT count(glbextr_key)
								FROM glbextr 
							 WHERE glbextr_creator_id='{$clean_username}' 
								 AND glbextr_user_id='{$clean_username}' 
								 AND glbextr_selection='{$selection}'
								 AND glbextr_application='{$application}'
								 AND glbextr_slct_ind is null";
		}//end else
		return $this->db->GetOne($sql);
	}//end getPopselCounts

	/**
	 * Retrieves PopSels owned by a given user
	 *
	 * @since			version 1.1.0
	 * @param  		string $username optional username.  If no username is specified, defaults to authenticated user
	 * @return		array
	 */
	function getPopsels($username='')
	{
		$data=array();
		$clean_username=($username)?strtoupper($username):strtoupper($_SESSION['username']);

		$sql="SELECT glbslct_selection,
									 glbslct_application
							FROM glbslct 
						 WHERE glbslct_creator_id='{$clean_username}' 
						 GROUP BY glbslct_selection,glbslct_application
						 ORDER BY glbslct_selection";

		if($results=$this->db->Execute($sql))
		{
			while($row=$results->FetchRow())
			{
				$data[]=$row;
			}//end while
		}//end if
		return $data;
	}//end getPopsels

	/**
	 * Inserts pidms into a population
	 *
	 * @since			version 1.1.0
	 * @param  		array $pidms pidms to add to popsel
	 * @param  		string $application popsel application
	 * @param  		string $selection popsel selection
	 * @param  		string $username optional username.  If no username is specified, defaults to authenticated user
	 * @return		boolean
	 */
	function insertPopselRecords($pidms,$application,$selection,$username='')
	{
		$clean_username=($username)?strtoupper($username):strtoupper($_SESSION['username']);

		if(sizeof($pidms)>0)
		{
			foreach($pidms as $pidm)
			{
				$sql="INSERT INTO glbextr
								(
									glbextr_application, 
									glbextr_selection, 
									glbextr_creator_id,
									glbextr_user_id, 
									glbextr_key, 
									glbextr_activity_date, 
									glbextr_sys_ind, 
									glbextr_slct_ind
								)
								VALUES
								( 
									'{$application}', 
									'{$selection}', 
									'{$clean_username}', 
									'{$clean_username}', 
									'".trim($pidm)."',
									sysdate, 
									'M', 
									NULL
								)";

				$this->db->Execute($sql);
			}//end foreach
			return true;
		}//end if
		return false;
	}//end insertPopselRecord

	/**
	 * Does a given selection exist for the specified user?
	 *
	 * @since			version 1.1.0
	 * @param  		string $application popsel application
	 * @param  		string $selection popsel selection
	 * @param  		string $username optional username.  If no username is specified, defaults to authenticated user
	 * @return		boolean
	 */
	function selectionExists($application,$selection,$username='')
	{
		$clean_username=($username)?strtoupper($username):strtoupper($_SESSION['username']);
		$sql="SELECT count(*) FROM glbslct WHERE glbslct_application='{$application}' AND glbslct_selection='{$selection}' AND glbslct_creator_id='{$clean_username}'";
		if($this->db->GetOne($sql)) 
			return true;
		return false;
	}//end selectionExists

	/**
	 * Retrieves list of pidms from a given list of ids
	 *
	 * @since			version 1.0.0
	 * @param  		array $ids array of ids
	 * @return		array
	 */
	function updateIDsToPidms($ids)
	{
		$pidms=array();
		foreach($ids as $key=>$id)
		{
			//loop over ids and retrieve pidms
			$sql="SELECT distinct(spriden_pidm) pidm FROM spriden WHERE spriden_id='$id' AND spriden_change_ind is null";
			if($result=$this->db->Execute($sql))
				if($row=$result->FetchRow())
					$pidms[$key]=trim($row['pidm']);
				else
					return false;
			else
				return false;
		}//end for
		return $pidms;
	}//end updateIDsToPidms

	/**
	 * Retrieves list of pidms from a given list of usernames
	 *
	 * @since			version 1.0.0
	 * @param  		array $usernames array of usernames
	 * @return		array
	 */
	function updateUsernamesToPidms($usernames)
	{
		$pidms=array();
		foreach($usernames as $key=>$username)
		{
			//loop over usernames and retrieve pidms
			$sql="SELECT distinct(gobtpac_pidm) pidm FROM gobtpac WHERE gobtpac_external_user='$username'";
			if($result=$this->db->Execute($sql))
				if($row=$result->FetchRow())
					$pidms[$key]=trim($row['pidm']);
				else
					return false;
			else
				return false;
		}//end for
		return $pidms;
	}//end updateUsernamesToPidms
}//end class PopSel
