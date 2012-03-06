<?php
/**
 * PrintUser.class.php
 *
 * API for Print User Management (MS SQL)
 *
 * @version		1.0.0
 * @module		PrintUser.class.php
 * @author		Matthew Batchelder <mtbatchelder@plymouth.edu>
 * @copyright 2009, Plymouth State University, ITS
 */ 
require_once('PSUDatabase.class.php');
require_once('IDMObject.class.php');
require_once('PSUTools.class.php');
require_once('PSUPerson.class.php');

class PrintUser
{
	/**
	 * adjustBalance
	 *
	 * adjusts a user's print balance
	 *
	 * @since		version 1.0.0
	 * @param  		float $amount dollar amount the PrintUser's balance is to be adjusted by
	 * @return    mixed
	 */
	public function adjustBalance($amount)
	{
		if(!$this->has_record)
		{		
			//no balance entry...add one.
			return 'no_record';
		}//end if
		elseif($this->balance + $amount < 0)
		{
			return 'too_small';
		}//end elseif

		//balance exists...add funds.
		$sql = "UPDATE UserQuotas SET Balance = (Balance + ".$amount.") WHERE UserName = '".$this->username."'";
		if($this->db->Execute($sql))
		{
			return true;
		}//end if
		
		return false;
	}//end adjustBalance

	/**
	 * load
	 *
	 * loads a user's printing information
	 *
	 * @since		version 1.0.0
	 * @param  		string $username optional username.  Passed only when changing who the PrintUser object is attached to
	 * @return    boolean
	 */
	public function load($username = '')
	{
		if($username) $this->username = $username;
		
		$sql = "SELECT * FROM UserQuotas WHERE UserName = '".$this->username."'";
		// Note: in a future version of ADOdb (5.11) we experience an issue where 
		// all field names are getting lowercased, which in turn makes a lot of assumptions
		// about the case of the field names.  ex. "Balance"
		// ZBT 9/30/10 
		
		if($data = $this->db->GetRow($sql))
		{
			foreach($data as $key => $value)
			{
				$this->$key = $value;
			}//end foreach
			
			$this->has_record = true;
		}//end if
		else
		{
			$this->has_record = false;
			$this->balance = 20;
		}//end else

		return $this->has_record;
	}//end

	/**
	 * constructor that accepts in either a pidm or a username, or a PSUPerson object
	 *
	 * @since    version 1.0.0
	 * @param    string $id PSUPerson, username, or pidm
	 */
	public function __construct($id)
	{
		if( is_object($id) && get_class($id) === 'PSUPerson' )
		{
			$this->person = $id;
		}
		else
		{
			$this->person = PSUPerson::get($id);
		}

		if( ! $this->person || ! $this->person->hasSystemAccount() ) {
			return;
		}

		$this->username = $this->person->username;

		$this->db = PSUDatabase::connect('mssql/printers2');
		
		$this->load();
	}//end __construct
}//end class PrintUser

/**
 * Exceptions for the PrintUser.
 */
class PrintUserException extends PSUException
{
	function __construct($code, $append=null)
	{
		parent::__construct($code, $append, self::$_msgs);
	}
}

