<?php
/**
 * cron.class.php
 *
 * === Modification History ===
 * 0.2.0  16-may-2006  [zbt]  original
 *
 * @package 		Tools
 */

require_once 'autoload.php';

/**
 * cron.class.php
 *
 * Has capability to create lock files and time script execution 
 *
 * @version		0.2.0
 * @module		cron.class.php
 * @author		Zachary Tirrell <zbtirrell@plymouth.edu>
 * @copyright 2006, Plymouth State University, ITS
 */ 

class cron
{
	var $page_threshold = 3; // how many lock failures before paging
	var $_lock_file_name = '';
	var $_app_name = '';
	var $debug = false;
	var $_start_time;
	var $_end_time;
	var $_run_time;
	var $_stdin;
	var $_lock_override;
	var $page = 'nrporter@plymouth.edu,djbramer@plymouth.edu';

	/**
	 * cron
	 *
	 * function called to create a cron
	 *
	 * @access	public
	 * @param	string $app_name name of application to cron
	 * @param	mixed $params null, or array of params for cron creation
	 */
	function cron($app_name,$params = '')
	{
		// be very nice to other processes
		if( php_sapi_name() == 'cli' ) { 
			proc_nice(19);
		}

		$this->_startTimer();
		$this->_app_name = $app_name;
		$this->_lock_file_name = '/web/temp/'.$app_name.'.lock';

		set_time_limit(0);
		$this->_stdin = fopen('php://stdin','r');
		$this->log_file = '/web/temp/'.$app_name.'.log';
		
		parse_str($params,$params);
		foreach($params as $param => $value)
		{
			$this->$param = $value;
		}//end foreach
	}

	/**
	 * Check to see if there are locks on the cron.
	 *
	 * @return mixed returns false if no locks, otherwise returns lock
	 */
	function checkLock()
	{
		if(file_exists($this->_lock_file_name))
		{
			$locks = file_get_contents($this->_lock_file_name);
			$this->lock();
			return $locks;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Static to register the error handler.
	 */
	public static function register_error_handler()
	{
		set_error_handler(__CLASS__.'::error_handler', E_RECOVERABLE_ERROR);
	}//end register_error_handler

	/**
	 * Error handler.
	 */
	public static function error_handler( $errno, $errstr, $errfile, $errline )
	{
		echo "Catchable Fatal Error: $errstr in $errfile line $errline. Stack trace follows.\n";
		PSU::backtrace();
		die();
	}//end error_handler
	
	/**
	 * log
	 * 
	 * log the cron
	 *
	 * @access	public
	 * @param	string $message message to post with cron in the log
	 */
	function log($message)
	{
		file_put_contents($this->log_file,date('Y-m-d H:i:s').' :: '.$message."\n",FILE_APPEND);
	}//end log

	/**
	 * lock
	 *
	 * lock the cron file
	 *
	 * @access	public
	 * @param	integer	$num num for locked or unlocked, defaults to 1
	 * @return	mixed returns false on failure of lock or string specifying failure
	 */
	function lock($num = 1)
	{
		if($this->debug)
		{
			echo "locking...\n";
		}
		
		$lock_file_name = $this->_lock_file_name;
		if(file_exists($lock_file_name))
		{
			$status = file_get_contents($lock_file_name);

			$fp = fopen($lock_file_name, 'w');
			if($fp)
			{
				if($status===false)
				{
					fwrite($fp, $num);
					return false;
				}
				else
				{
					fwrite($fp, intval($status) + $num );

					$status_text = 'locked';
					if($status > $this->page_threshold && $status%($this->page_threshold+1)==0)
					{
						$this->page('cron is locked - has failed '.$status.' times');
						$status_text = 'locked_paged';
					}

					return $status_text.':'.trim($status);
				}
			}
			else
			{
				return 'unable to create lock file';
			}
		}
		else
		{
			file_put_contents($lock_file_name,'1');
			return false;
		}
	} // end lock

	/**
	 * unlock
	 *
	 * unlock the cron
	 *
	 * @access	public
	 * @return	mixed true or result of unlinking the file_name
	 */
	function unlock()
	{
		if($this->debug)
		{
			echo "unlocking...\n";
		}
		
		if(!$this->_lock_override)
		{
			return unlink($this->_lock_file_name);
		}

		return true;
	} // end unlock

	/**
	 * lockOveride
	 *
	 * set the lock overide var
	 *
	 * @access	public
	 */
	function lockOverride()
	{
		$this->_lock_override = true;
	}

	/**
	 * page
	 *
	 * page someone about the cron
	 *
	 * @access	public
	 */
	function page($message)
	{
		PSU::mail($this->page, 'Cron Email ('.$this->_app_name.')', $message, 'From: Cron-Paging@www');
	}

	/**
	 * clear
	 *
	 * call `clear`
	 *
	 * @access	public
	 */
	function clear() 
	{
		`clear`;	
	}

	/**
	 * pause
	 *
	 * pause the cron
	 *
	 * @access	public
	 */
	function pause() 
	{
		print "\n**** press <enter> key to continue ****\n";
		fgets($this->_stdin,256);
	}

	/**
	 * getInput
	 * 
	 * get Input on the cron
	 *
	 * @access	public
	 */
	function getInput()
	{
		return trim(fgets($this->_stdin,256));
	}

	/**
	 * prompt
	 *
	 * prompt the user
	 *
	 * @access	public
	 * @param	string $text text to print
	 * @return	mixed results of getInput on the cron
	 */
	function prompt($text)
	{
		print $text;
		return $this->getInput();
	}

	/**
	 * _startTimer
	 *
	 * start the timer on the cron
	 *
	 * @access	public
	 * @param	mixed $time defaults to false, time value to start cron
	 */
	function _startTimer($time=false)
	{
		if($time===false)
		{
			$this->_start_time = microtime(true);
		}
		else
		{
			$this->_start_time = $time;
		}
	}

	/**
	 * stopTimer
	 *
	 * stop the timer on the cron
	 *
	 * @access	public
	 * @return	mixed current runtime when stopped
	 */
	function stopTimer()
	{
		$this->_end_time = time();
		$this->_run_time = $this->_end_time-$this->_start_time;

		StatsD\StatsD::timing('cron.web.' . $this->_app_name, microtime( true ) - $this->_start_time );

		return $this->_run_time;
	}

	/**
	 * getRunTime
	 *
	 * get the runtime of the cron
	 *
	 * @access	public
	 * @return	mixed time of current runtime
	 */
	function getRunTime()
	{
		return $this->_run_time;
	}//end getRunTime
}//end class cron

require_once('PSUTools.class.php');
