<?php

require_once('PSUTools.class.php');

class PasswordManager
{
	var $myplymouth;  // myPlymouth database (password expiration tables)
	var $idm;         // IDMObject
	var $user_db;     // USER_DB database (Ted's table)
	var $ad;          // Active Directory connection object

	public static $INSERTED = 1;
	public static $UPDATED = 2;

	public $table = array(
		'expirations' => 'password_expiration',
		'user_db' => 'USER_DB'
	);

	/**
	 * _construct
	 * 
	 * sets variables with passed in information if information was passed in
	 * 
	 * @access public
	 * @param mixed $myplymouth
	 * @param mixed $idm
	 * @param mixed $user_db
	 */
	function __construct( $myplymouth = false, $idmobject = false, $user_db = false )
	{
		$this->myplymouth = $myplymouth ? $myplymouth : PSU::db('myplymouth');
		$this->idm = $idmobject ? $idmobject : PSU::get('idmobject');
		$this->user_db = $user_db ? $user_db : PSU::db('userinfo');

		require('ldap/pluto.php');
		$this->_DB = $_DB;
	}//end __construct

	/**
	 * expire
	 *
	 * Expire a user's password.
	 *
	 * @param       $ident string|int username or pidm
	 * @param       $reason string reason for expiration. valid values: 'expired' or 'default'
	 * @return      int returns 1 if the expiration was new, 2 if the expiration was updated. (matches the affected row count paradigm of mysql's "INSERT ... ON DUPLICATE KEY UPDATE")
	 */
	function expire($ident, $reason)
	{
		if(is_numeric($ident))
		{
			$pidm = $ident;
			$username = $this->idm->getIdentifier($ident, 'pidm', 'username');
		}//end if
		else
		{
			$username = $ident;
			$pidm = $this->idm->getIdentifier($ident, 'username', 'pidm');
		}//end else

		$username = $this->myplymouth->qstr($username);
		$reason = $this->myplymouth->qstr($reason);

		if($this->isExpired($pidm))
		{ 
			$this->myplymouth->Execute("UPDATE {$this->table['expirations']} SET refreshed = NOW(), username = $username WHERE pidm = $pidm AND changed IS NULL");
			return self::$UPDATED;
		}//end if
		else
		{ 
			$this->myplymouth->Execute("INSERT INTO {$this->table['expirations']} (username, pidm, reason, added, refreshed) VALUES ($username, $pidm, $reason, NOW(), NOW())");
			return self::$INSERTED;
		}//end else
	}//end expire

	/**
	 * Mark an expiration record as resolved.
	 *
	 * @param $ident identifier for PSUPerson::get()
	 */
	function expirationResolved( $ident ) {
		$person = PSUPerson::get( $ident );

		if( ! ( $username = $person->username ) ) {
			return null;
		}

		$sql = "
			UPDATE password_expiration
			SET changed = NOW()
			WHERE username = ? AND changed IS NULL
		";

		$this->myplymouth->Execute($sql, array($username));

		return null;
	}//end expirationResolved

	/**
	 * Return an instance of PasswordManager with default params for the registry.
	 */
	public static function get()
	{
		static $instance = null;
		if($instance === null)
		{
			$instance = new PasswordManager;
		}
		return $instance;
	}//end get

	/**
	 * isExpired
	 *
	 * Test if a user's password is in the expiration table.
	 *
	 * @param mixed $ident int|username the username or pidm
	 * @return boolean
	 */
	function isExpired($ident)
	{
		if(!is_numeric($ident))
		{
			$ident = $this->idm->getIdentifier($ident, 'username', 'pidm');
		}//end if

		$sql = "SELECT 1 FROM {$this->table['expirations']} WHERE pidm = ? AND changed IS NULL";
		$args = array($ident);

		$result = $this->myplymouth->GetOne($sql, $ident);

		return $result == 1;
	}//end isExpired

	/**
	 * Check to see if a new password that the user is trying to use is 
	 * usable. Do this by checking that it is not within the history 
	 * table, or within a certain age.
	 */
	public function isUsablePassword( $ident, $pw, $reserve = 9, $time_limit = 730 ) {
		$p = PSUPerson::get( $ident );
		$hasher = new PasswordHash( 8, FALSE );
		$args = array(
			$p->wpid,
			strtotime( $time_limit . ' days ago' ),
			$reserve,
		);

		$sql = "
			SELECT * 
			  FROM password_history 
			 WHERE wpid = ?
				 AND activity_date <= ?
		ORDER BY activity_date ASC
		   LIMIT ?
		";

		foreach( (array)PSU::db('myplymouth')->GetAll( $sql, $args ) as $invalid ) {
			if( $hasher->CheckPassword( $pw, $invalid['password'] ) ) {
				return FALSE;
			}//end if
		}//end foreach

		return TRUE;
	}//end isUsablePassword

	/**
	 * defaultCredentials
	 *
	 * Get the default credentials for a username or pidm.
	 *
	 * @access       public
	 * @param        string|int $ident the username or pidm
	 * @return       array the username and default password
	 */
	function defaultCredentials($ident)
	{
		if(ctype_digit($ident))
		{
			$col = 'PIDM';
			$ident_safe = $ident;
		}//end if
		else
		{
			$col = 'USER_UNAME';
			$ident_safe = $this->user_db->qstr($ident);
		}//end else
	
		$sql = "SELECT USER_UNAME, USER_SSN, CONCAT(LOWER(SUBSTRING(USER_FIRST, 1, 1)), LOWER(SUBSTRING(USER_LAST, 1, 1)), SUBSTRING(USER_SSN, 1, 6)) AS pw FROM {$this->table['user_db']} WHERE $col = $ident_safe";
		$result = $this->user_db->GetRow($sql);
	
		if($result == false)
		{
			throw new PasswordException(PasswordException::NOT_IN_USERDB, $ident);
		}//end if
		
		if(strlen($result['pw']) != 8)
		{
			throw new PasswordException(PasswordException::USERDB_PASS_SHORT, $ident);
		}//end if
	
		return array($result['USER_UNAME'], $result['pw']);
	}//end defaultCredentials

	/**
	 * Log a password change for a user.
	 */
	function logChange($username)
	{
		PSU::db('mysql/systems')->Execute("INSERT INTO password_change (`username`,`date_changed`,`time_changed`,`changed_by`,`type`) VALUES(?, NOW(), NOW(), ?, 'user')", array($username, $username));
	}//end logChange

	/**
	 * Log old password for user upon successful update for history 
	 * purposes.
	 */
	function logPassword( $username, $old_pw ) {
		$person = PSUPerson::get( $username );
		$hasher = new PasswordHash( 8, FALSE );
		$args = array(
			$person->wp_id,
			$hasher->HashPassword( $old_pw ),
		);

		$sql = "
			INSERT INTO password_history (
				`wpid`, 
				`password`, 
				`activity_date` 
			) 
			VALUES(?, ?, NOW())
		";

		PSU::db('myplymouth')->Execute( $sql, $args );
	}//end logPassword	

	/**
	 * setADPassword
	 *
	 * sets the active directory password to the passed in string values
	 *
	 * @param       $username string target user
	 * @param       $passwd string the new password
	 */
	function setADPassword($username, $passwd)
	{
		$result = PSU::get('ad')->user_password($username, $passwd);

		if($result === false)
		{ 
			throw new PasswordException(PasswordException::PASS_CHANGE_FAIL_AD);
		}//end if
	}//end setADPassword

	/**
	 * setWPPassword
	 *
	 * Set the password within WordPress.
	 *
	 * @param string $username the psu account name
	 * @param string $password the new password
	 * @return bool true if the password could be reset
	 */
	public function setWPPassword($username, $password)
	{
		// WordPress libs are required. let the calling app include them to reduce overhead when it's not used.
		if( !isset($GLOBALS['wpdb']) )
		{
			throw new Exception('please include PSUWordPress.php in the global scope to set WordPress passwords');
		}

		$user = get_userdatabypsuname($username);

		if( $user === false )
		{
			return false;
		}

		return sl_sync_psuname_password( $user->ID, $password );
	}//end setWPPassword

	/**
	 * changes both passwords to the passed in string values
	 *
	 * @param       $ident string|int username or pidm
	 * @param       $password string the new password
	 */
	function setPassword($ident, $password, $log = false)
	{
		if(is_numeric($ident))
		{
			$pidm = $ident;
			$username = $this->idm->getIdentifier($ident, 'pidm', 'username');
		}//end if
		else
		{
			$username = $ident;
		}//end else

		$this->setADPassword($username, $password);
		$this->setWPPassword($username, $password);

		if( $log ) {
			$this->logChange( $username );
		}
	}//end setPassword

	/**
	 * Set passwords in the background by spawning a subprocess. The output of
	 * this action will be lost in the ether.
	 * @param $username \b string target username
	 * @param $password \b string new password
	 * @param $targets \b array list of targets to change. include a subarray with key "oracle" listing oracle servers, and "mysql" listing mysql servers (ex. array('oracle' => array('pods', 'psc1'))
	 */
	function setPasswordBackground( $username, $password, $targets )
	{
		$username = base64_encode($username);
		$password = base64_encode($password);

		// shell argument targets
		$saTargets = '';

		foreach( $targets as $key => $target ) {
			if( $key === 'oracle' || $key === 'mysql' ) {
				$saTargets .= "--$key=" . implode(',', $target);
			}
		}

		exec("/usr/local/bin/php /web/includes_psu/PasswordSet.php $saTargets --username-base64=$username --password-base64=$password &>/dev/null &");
	}//end setPasswordBackground

	/**
	 * Set the ODS password for the user, if they have an ODS account.
	 *
	 * @param       $username \b string username
	 * @param       $password \b string the new password
	 */
	public static function setODSPassword($username, $password)
	{
		return self::setOraclePassword( PSU::db('pods'), $username, $password );
	}//end setODSPassword

	/**
	 * Validate that a username is valid in Oracle.
	 * @param $username \b string the password to validate
	 */
	public static function validateOracleUsername( $username )
	{
		// validate username (alpha-numeric, starting with alpha, plus _)
		if( !preg_match( '/^[A-Za-z][A-Za-z0-9_]+$/', $username ) )
		{
			return false;
		}

		return true;
	}//end validateOracleUsername

	/**
	 * Validate that a given password is valid in Oracle.
	 * @param $password \b string the password to validate
	 */
	public static function validateOraclePassword( $password )
	{
		// check that ASCII between 0-127 was used, but not the double quote (34)
		$lPassword = strlen($password);
		for( $i = 0; $i < $lPassword; $i++ )
		{
			$ord = ord($password[$i]);

			if( $ord > 127 || $ord == 34 )
			{
				return false;
			}
		}

		return true;
	}//end validateOraclePassword

	/**
	 * Set the MySQL password for a user.
	 * @param $database \b ADOConnection the mysql connection over which to reset the password
	 * @param $username \b string target user (will be lowercased)
	 * @param $password \b string new password
	 */
	public static function setMysqlPassword( ADOConnection $database, $username, $password )
	{
		$username = strtolower($username);

		if( $username === 'ambackstrom' || $username === 'zbtirrell' ) {
			return false;
		}

		$sql = "UPDATE mysql.user SET Password = PASSWORD(?) WHERE User = ?";
		$database->Execute($sql, array($password, $username));
		$database->Execute("FLUSH PRIVILEGES");

		return true;
	}//end setMysqlPassword

	/**
	 * Set the Oracle password for a user, if they have an Oracle account. Since we cannot
	 * use variable binding, validate the username and password before we pass them to
	 * the script
	 *
	 * @param       $database \b ADOConnection an adodb connection
	 * @param       $username \b string username
	 * @param       $password \b string the new password
	 */
	public static function setOraclePassword(ADOConnection $database, $username, $password)
	{
		$username = strtoupper($username);

		// do not proceed if user does not have an account in this database
		if( ! $database->GetOne("SELECT 1 FROM dba_users WHERE username = :u", array('u' => $username) ) )
		{
			return false;
		}

		if( ! self::validateOraclePassword( $password ) )
		{
			throw new Exception('Invalid password');
		}

		if( ! self::validateOracleUsername( $username ) )
		{
			throw new Exception('Invalid username');
		}

		$password = str_replace('"', '\"', $password);

		$database->Execute("ALTER USER $username IDENTIFIED BY \"$password\"");

		if( $database->ErrorNo() > 0 ) {
			throw new Exception( "Database error:" . $database->ErrorMsg() );
		}

		return true;
	}//end setOraclePassword

	/**
	 * mkntpwd
	 *
	 * Run mkntpwd on the specified password.
	 *
	 * @param      $passwd string the password to hash
	 * @return     array the hash pair
	 */
	function mkntpwd($passwd)
	{
		$descriptors = array(
			0 => array("pipe", "r"),
			1 => array("pipe", "w")
		);

		$mkntpwd = proc_open('/usr/local/bin/mkntpwd -',  $descriptors, $pipes);

		fwrite($pipes[0], $passwd);
		fclose($pipes[0]);

		$hash = rtrim(stream_get_contents($pipes[1]));
		$hash = explode(':', $hash);
		fclose($pipes[1]);

		$exit_code = proc_close($mkntpwd);

		if($exit_code !== 0)
		{
			throw new PasswordException(PasswordException::MKNTPWD_FAILED, $exit_code);
		}//end if

		return $hash;
	}//end mkntpwd

	/**
	 * hasDefaultPassword
	 *
	 * Check if a user is using his default password.
	 *
	 * @param     string|int $ident username or pidm
	 * @return    boolean true if the user's password is the default, false otherwise
	 */
	function hasDefaultPassword($ident)
	{
		if(is_numeric($ident))
		{
			$pidm = $ident;
			$username = $this->idm->getIdentifier($ident, 'pidm', 'username');
		}//end if
		else
		{
			$username = $ident;
		}//end else

		list($username, $password) = $this->defaultCredentials($username);

		if(PSU::get('ad')->authenticate($username, $password))
		{
			return true;
		}//end if

		return false;
	}//end hasDefaultPassword

	/**
	 * Try to authenticate to one of the LDAPs using the supplied credentials.
	 *
	 * @param        string|int $ident username or pidm
	 * @param        string $password password to check
	 * @param        string $server server to authenticate to. currently must be set to 'ad' (the default)
	 * @return boolean
	 */
	function authenticate($ident, $password, $server = 'ad')
	{
		if(is_numeric($ident))
		{
			$pidm = $ident;
			$username = $this->idm->getIdentifier($ident, 'pidm', 'username');
		}//end if
		else
		{
			$username = $ident;
		}//end else

		if($server === 'ad')
		{
			if(PSU::get('ad')->authenticate($username, $password))
			{
				return true;
			}//end if

			return false;
		}//end if

		throw new PasswordException(PasswordException::UNKNOWN_AUTH_SERVER, $server);
	}//end authenticate

	/**
	 * Determine if user's password is expired
	 *
	 * @param        $ident string|int username or pidm
	 * @param        $max_days int number of days stale to check. -1 (default) to force the password to be considered fresh
	 * @return       boolean True for password change, false on valid pass
	 */
	function isPasswordStale($ident, $max_days = -1)
	{
		if( $max_days === -1 ) {
			/**
			 * ALL FEAR THE ROLLING PASSWORD EXPIRATION THAT IS FORTHCOMING!!!
			 *
			 * BOW DOWN TO ME AS I CONTROL HOW LONG YOU CAN KEEP YOUR MEASLY 
			 * PASSWORD THAT MAY OR MAY NOT BE THE NAME OF YOUR CAT AND A 
			 * NUMBER THAT WILL PROBABLY BE EASILY GUESSABLE AS AN IMPORTANT 
			 * DATE IN YOUR LIFE...
			 */

			/**
			 * Just good practice to only get the time once for logic checks
			 */
			$now = time();

			/**
			 * Do the identifier check here just for now (I know it happens 
			 * again later, but this is going away!)
			 */
			if( is_numeric( $ident ) ) {
				$pidm = $ident;
				$username = $this->idm->getIdentifier($ident, 'pidm', 'username');
			} else {
				$username = $ident;
			}//end else

			/**
			 * Kick off the dog and pony show only if we can actually get 
			 * their AD info
			 */
			if( $ad_info = PSU::get('ad')->user_info( $username, array('pwdlastset') ) ) {

				/**
				 * Let's start the game on Feb 21st.
				 */
				if( $now >= 1361422800 ) {//Feb 21st

					/**
					 * Only do these calculations now that we know we care
					 */
					$ad_stamp = round(($ad_info[0]['pwdlastset'][0]-116444736000000000)/10000000);
					$age_today = round( ( time() - $ad_stamp )/60/60/24 );
					$age_from = round( ( strtotime('21 February 2013') - $ad_stamp )/60/60/24 );
					$age_from=181;

					/**
					 * From our histogram we know that on the 21st we want to 
					 * expire users whos password was greater than 282 days old as 
					 * of the 21st, so set the max days for this check to 
					 * something we KNOW will get caught
					 */
					if( $age_from > 282 ) {
						$max_days = 1;
					}//end if

					/**
					 * Only do into our next population if it is, or is after Feb. 
					 * 28th
					 */
					if( $now >= 1362027600 ) {//Feb 28th

						/**
						 * Now only grab people who have an age as of the 21st EQUAL 
						 * TO 282 days. Note that I'm grabbing greater than or equal 
						 * to. This is just a discount double check on the people 
						 * from population one.
						 */
						if( $age_from >= 282 ) {
							$max_days = 1;
						}//end if

						/**
						 * Dive into the third population if it is on or after March 
						 * 7th.
						 */
						if( $now >= 1362632400 ) {//March 7th

							/**
							 * Now our limiter is 275 days. Even though it's not 
							 * needed, this is a double check on the last population, 
							 * and a tripple check on the first.
							 */
							if( $age_from >= 275 ) {
								$max_days = 1;
							}//end if

							/**
							 * At this point, we are sooooo over doing hard catches. 
							 * We'll start the 180 day policy until we can pull this 
							 * cruft out and make that the default logic simply passed 
							 * into this function.
							 */
							if( $now >= 1363233600 ) {//March 14th
								$max_days = 180;
							}//end if
						}//end if
					}//end if
				}//end if
			}//end if

			if( $max_days === -1 ) {
				return false;
			}//end if
		}

		if(is_numeric($ident))
		{
			$pidm = $ident;
			$username = $this->idm->getIdentifier($ident, 'pidm', 'username');
		}//end if
		else
		{
			$username = $ident;
		}//end else

		$groups = PSU::get('ad')->user_groups($username);
		if(!in_array('faculty', $groups) && !in_array('staff', $groups))
		{
			return false;
		}//end if

		$days = $this->passwordAge( $username );

		if($days >= $max_days)
		{
			return true;
		}//end if

		return false;
	}//end isPasswordStale

	/**
	 * Return the age, in days, of a user's password.
	 * @param $ident an identifier to pass to PSUPerson::get()
	 * @return int the password age
	 */
	function passwordAge( $ident ) {
		$person = PSUPerson::get( $ident );

		$ad_info = PSU::get('ad')->user_info($person->login_name, array('pwdlastset'));

		// 116444736000000000 = 10000000 * 60 * 60 * 24 * 365 * 369 + 89 leap days huh.
		$ad_stamp = round(($ad_info[0]['pwdlastset'][0]-116444736000000000)/10000000);
		$change_date = date('F j, Y',$ad_stamp);

		$seconds = time() - $ad_stamp;
		$days = round(($seconds)/60/60/24);

		return $days;
	}//end passwordAge

	/**
	 * Test if a provided password is valid, given our password requirements.
	 * Tests format only, ie. number of capital letters and such,
	 * does not care about default passwords.
	 * @todo function is unfinished
	 * @todo test suite
	 */
	public static function validPassword( $password )
	{
		if( strlen($password) < 8 )
		{
			return false;
		}

		if( preg_match('/[@;$:]/', $password) )
		{
			return false;
		}

		if( 0 === preg_match('/[A-Z]/', $password) ) {
			return false;
		}

		return true;
	}//end validPassword
}//end class

require_once('PSUException.class.php');

PSU::get()->add_shortcut('pwman', array('PasswordManager', 'get'));

class PasswordException extends PSUException
{
	const CONNECTION_FAILURE = 1;
	const FAILED_PROTOCOL_3 = 2;
	const AUTH_FAILED = 3;
	const MKNTPWD_FAILED = 4; // mkntpwd command exited with status > 0
	const NOT_IN_USERDB = 5;
	const USERDB_PASS_SHORT = 6;
	const LDAP_MODIFY_ERROR = 7;
	const PASS_CHANGE_FAIL_AD = 8;
	const PASS_CHANGE_FAIL_OPENLDAP = 9;
	const UNKNOWN_AUTH_SERVER = 10;

	private static $_msgs = array(
		self::CONNECTION_FAILURE => 'Unable to connect to LDAP server',
		self::FAILED_PROTOCOL_3 => 'Failed to set LDAP protocol version 3',
		self::AUTH_FAILED => 'Could not bind to LDAP',
		self::MKNTPWD_FAILED => 'mkntpwd failed with exit code',
		self::NOT_IN_USERDB => 'User not found in USER_DB',
		self::USERDB_PASS_SHORT => 'Password in USER_DB was too short',
		self::LDAP_MODIFY_ERROR => 'LDAP modification failed',
		self::PASS_CHANGE_FAIL_AD => 'Failed to change password in AD',
		self::PASS_CHANGE_FAIL_OPENLDAP => 'Failed to change password in OpenLDAP', // legacy message
		self::UNKNOWN_AUTH_SERVER => 'Unknown authentication server'
	);

	/**
	 * Wrapper construct so PSUException gets our message array.
	 */
	function __construct($code, $append=null)
	{
		parent::__construct($code, $append, self::$_msgs);
	}//end __construct
}//end PasswordException
