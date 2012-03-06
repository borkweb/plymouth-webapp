<?php

/**
 * A class to log messages to a database. Access via PSU::get('psulog').
 */
class PSULog
{
	var $logged_view=false;
	var $message='';
	var $target='';

	/**
	 * The user performing the action.
	 */
	var $username;

	/**
	 * Class constructor.
	 *
	 * @param string $app_code short application code from the log_apps table
	 * @param string $username the user performing the action
	 */
	function __construct($app_code, $username = false)
	{
		$this->db = PSU::db('audit');

		if(!is_numeric($app_code))
		{
			$app_code = $this->db->GetOne("SELECT id FROM log_apps WHERE code = ".$this->db->qstr($app_code));
		}//end if

		$this->app_code = $app_code;

		if($username)
		{
			$this->username = $username;
		}//end if
		elseif( isset($_SESSION['username']) && !empty($_SESSION['username']) )
		{
			$this->username = $_SESSION['impersonate'] ? $_SESSION['impersonate_store']['username'] : $_SESSION['username'];
		}//end else
		else
		{
			throw new Exception('A username must be provided!');
		}//end else
	}//end PSULog

	/**
	 * Get log entries from the database.
	 *
	 * @param array $params key/value pairs which will be added to the WHERE clause
	 * @param int $limit the number of records to return
	 * @return array associative array of records from the log table
	 */
	function get($params,$limit = 50)
	{
		$where='';
		if(is_array($params))
		{
			foreach($params as $key=>$param)
			{
				$where.=" AND $key='$param'";
			}//end foreach
		}//end if

		$data=array();
		$sql="SELECT id,pidm,username,application,log,activity_date FROM log WHERE 1=1 $where LIMIT 0,$limit";
		$data = $this->db->GetAll($sql);
		return $data;
	}//end get

	/**
	 * Log the currently active PHP script.
	 */
	function logView()
	{
		if(!$this->logged_view)
		{
			$this->write('viewing '.$_SERVER['SCRIPT_FILENAME']);
			$this->logged_view=true;
		}//end if
	}//end logView

	/**
	 * Write a message to the log.
	 *
	 * @param string $message the message to log
	 * @param string $target the user being affected
	 * @return boolean true if message was inserted, otherwise false.
	 */
	function write($message,$target='', $additional = null)
	{
		//check if message is a duplicate duplicates
		if($message!=$this->message || ($message==$this->message && $target!=$this->target))
		{
			$args = array(
				$_SESSION['impersonate'] ? $_SESSION['impersonate_store']['pidm'] : $_SESSION['pidm'],
				$this->username,
				$this->app_code,
				$message,
				$additional,
			);

			$sql="INSERT INTO log (
							 pidm,
			         username,
			         application,
			         log,
							 additional,
			         activity_date
			         ".(($target)?",target_username":'')."
			      ) VALUES (
							?,
							?,
							?,
							?,
							?,
			       	NOW()
			       	".(($target)?",'$target'":'')."
			      )";
			if($this->db->Execute($sql, $args))
			{
				//prep duplicate message
				$this->message = $message;
				$this->target = $target;
				return true;
			}//end if

			error_log( 'PasswordManager::write() db error: ' . $this->db->ErrorMsg() );
			return false;
		}//end if
		return false;
	}//end write
}//end PSULog
