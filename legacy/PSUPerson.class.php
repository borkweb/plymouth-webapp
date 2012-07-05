<?php

require_once('BannerObject.class.php');

/**
 * PSUPerson.class.php.
 *
 * Base Person Object
 *
 * &copy; 2009 Plymouth State University ITS
 *
 * @author		Matthew Batchelder <mtbatchelder@plymouth.edu>
 * @todo document $person->first_name and other property usage -jrl
 */ 
class PSUPerson extends BannerObject
{
	public $possible_issues = array(
		'app_provision_error',
		'bad_oracle_account_status',
		'bad_username',
		'duplicate_ssn',
		'no_app_zack',
		'no_pin',
		'no_ssn',
		'no_wpid',
		'no_system_username',
		'pending_creation',
		'pending_ldi_sync',
		'pin_disabled',
		'ping_support_locked',
		'ssn_mismatch',
		'support_locked',
		'swipe_issue_mismatch',
		'userdb_username_mismatch',
		'username_sync',
	);
	
	public $default_load = array();
	public $data = array();
	public $data_loaders = array();
	public $priority = 10;

	public static $loaders = array(
		'has_issue' => 'hasIssue'
	);

	/**
	 * checkIssue
	 *
	 * Checks if the user has a specific issue
	 *
	 * @access		public
	 * @param     string $issue issue code
	 * @return		boolean
	 */
	public function checkIssue($issue, $ignore_cache = false)
	{
		if(isset($this->issues[$issue])) return $this->issues[$issue];

		$problem = false;
		
		switch($issue)
		{
			case 'app_provision_error':
				$problem = $this->is_applicant ? $this->applicant_provision_error : false;
			break;
			case 'app_no_sabiden_sabnstu':
				$problem = $this->is_applicant && $this->applicant_missing_sabiden_sabnstu;
			break;
			case 'bad_oracle_account_status':
				$problem = $this->oracle_account_exists && $this->oracle_account_status != 'OPEN';
			break;
			case 'bad_username':
				$problem = (strpos($this->login_name,' ') !== false);
			break;
			case 'duplicate_ssn':
				// if an ssn exists, check to see if there are other matches in Banner
				if($this->ssn_exists)
				{
					$ssn_count = PSU::db('banner')->GetOne("SELECT count(1) FROM spbpers s1 WHERE s1.spbpers_ssn IS NOT NULL AND s1.spbpers_ssn = (SELECT s2.spbpers_ssn FROM spbpers s2 WHERE s2.spbpers_pidm = :pidm)", array('pidm' => $this->pidm));

					$problem = $ssn_count > 1;
				}//end if
			break;
			case 'pending_creation':
				$problem = $this->pendingCreation();
			break;
			case 'pending_ldi_sync':
				$problem = $this->pendingLDISync();
			break;
			case 'no_pin':
				$problem = ($this->banner_roles && !$this->pin);
			break;
			case 'no_ssn':
				$problem = (!$this->ssn_exists && !$this->certification_number && !$this->foreign_ssn) && !$this->is_applicant;
			break;
			case 'no_system_username':
				$problem = ($this->should_have_account && !$this->system_account_exists);
			break;
			case 'pin_disabled':
				$problem = $this->pin_disabled;
			break;
			case 'ping_support_locked':
				$problem = isset($_SESSION['ping_support_locked']);
			break;
			case 'ssn_mismatch':
				// if an ssn exists, check to see if it matches in userdb
				if($this->ssn_exists)
				{
					//load ssn into person object
					$this->_load_ssn('//');
				
					// TODO: expand this check to also compare first and last name
					$userdb_ssn = PSU::db('userinfo')->GetOne("SELECT user_ssn FROM USER_DB WHERE pidm = ?", array($this->pidm));
					
					//compare ssns
					$problem = ($userdb_ssn && $userdb_ssn != $this->ssn);
					
					//unset ssn in person object
					unset($this->ssn);
					unset($userdb_ssn);
				}//end if
			break;
			case 'support_locked':
				$problem = $this->support_locked;
			break;
			case 'userdb_username_mismatch':
				if( $this->system_account_exsits )
				{
					$userdb_username = PSU::db('userinfo')->GetOne('SELECT user_uname FROM USER_DB WHERE pidm = ?', array($this->pidm));
					$problem = ($userdb_username !== $this->username);
					unset($userdb_username);
				}//end if
			break;	
			case 'username_sync':
				$problem = ($this->system_account_exists && $this->should_have_account && $this->username != $this->login_name);
			break;
			case 'no_app_zack':
				// this flag is only valid when a decision has not been made for this applicant
				$problem = $this->is_applicant && !$this->applicant_chkl_zack && in_array($this->apdc_code, array('ND','RD'));
			break;
			case 'no_wpid':
				$problem = $this->wpid == '';
			break;
			case 'swipe_issue_mismatch':
				$problem = ($this->door_badge_issue_num && $this->idcard_issue_num != $this->door_badge_issue_num);
			break;
		}//end switch
		
		return $this->issues[$issue] = $problem;
	}//end checkIssue

	/**
	 * commonMatchRecord performs common matching on a given set of values and returns an array of match status and pidm.
	 *
	 * @return	\b array
	 */
	public function common_match( $args = array() )
	{
		$args = PSU::params($args, array(
			'rule' => 'GENERAL_PERSON'
		));

		$sql = array("p_entity_cde=>'P'");

		$rule = $args['rule'];
		unset($args['rule']);

		$rule = strtoupper($rule);
		if( ! preg_match('/^[A-Z_]+$/', $rule) ) {
				throw new Exception('That rule name is rediculous.'); // [sic]
		}

		foreach($args as $key => $arg) {
			$key = strtolower($key);

			if( ! preg_match('/^[a-z_]+$/', $key) ) {
				throw new Exception('Bad key name in ' . __FUNCTION__);
			}

			if( $key == 'first_name' || $key == 'last_name' ) {
				$arg = strtoupper($arg);
			}
			
			$sql[] = "p_" . $key . "=>" . PSU::db('banner')->qstr($arg);
		}

		PSU::db('banner')->debug = true;

		$sql = implode(', ', $sql);
		$sql = "BEGIN gp_common_matching.p_insert_gotcmme($sql); END;";

		$stmt = PSU::db('banner')->PrepareSP($sql);

		//foreach($args as $key => $arg) {
		//	if( $key == 'first_name' || $key == 'last_name' ) {
		//		$arg = strtoupper($arg);
		//	}
		//	PSU::db('banner')->InParameter($stmt, $arg, $key);
		//}

		PSU::db('banner')->Execute($stmt);

		//
		// get the result of the match
		//

		$match = array();
		$sql = "BEGIN gp_common_matching.p_common_matching(:rule, :flag, :pidm); END;";
		$stmt = PSU::db('banner')->PrepareSP($sql);

		PSU::db('banner')->InParameter($stmt, $rule, 'rule');
		PSU::db('banner')->OutParameter($stmt, $match['flag'], 'flag');
		PSU::db('banner')->OutParameter($stmt, $match['pidm'], 'pidm');
		PSU::db('banner')->Execute($stmt);

		$match['matches'] = PSU::db('banner')->GetAll('SELECT * FROM gotcmme');

		return $match;
	}//end commonMatchRecord

	/**
	 * formatName
	 *
	 * Retrieve the formatted name of a user
	 *   f=first
	 *   m=middle
	 *   i=middle initial
	 *   l=last
	 *   p=prefix
	 *   s=suffix
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		string $input_id Input identifier
	 * @param  		string $name_format Name format 
	 * @return  	string
	 */
	public function formatName($name_format='l, f m')
	{
		$name_format = strtolower($name_format);

		//assign name information to name codes
		$name=array(
			'f'=>$this->first_name,
			'm'=>$this->middle_name,
			'i'=>substr($this->middle_name,0,1),
			'l'=>$this->last_name,
			'p'=>$this->name_prefix,
			's'=>$this->name_suffix
		);

		//replace name codes with name parts
		$name_format=chunk_split($name_format,1,'|||');
		$name_format=explode('|||',$name_format);
		foreach($name_format as $key=>$chunk)
		{
			if(array_key_exists($chunk,$name))
			{
				$name_format[$key]=$name[$chunk];
			}//end if
		}//end foreach
		$formatted_name=implode('',$name_format);
		
		return $formatted_name;
	}//end formatName

	public function get_email_override($username){
		static $overrides = null;
		if ($overrides === null){
			$config = \PSU\Config\Factory::get_config();
			$overrides = $config->get_json( 'psuperson', 'overrides' );
			$overrides = array_flip((array)$overrides);
		}
		if (isset ($overrides[$username])){
			return $overrides[$username] . '@plymouth.edu';
		}

		return null;
	}
	/**
	 * getIssues
	 *
	 * Retrieves all issue codes associated with user
	 *
	 * @access		public
	 * @return		boolean
	 */
	public function getIssues()
	{
		foreach($this->possible_issues as $issue)
		{
			$this->checkIssue($issue);
		}//end foreach

		$issues = array();
		foreach( (array) $this->issues as $key => $value ) {
			if( $value ) {
				$issues[ $key ] = $value;
			}//end if
		}//end foreach
		
		return $issues;
	}//end getIssues

	/**
	 * hasIssue
	 *
	 * Checks if the user data contains a problem
	 *
	 * @access		public
	 * @return		boolean
	 */
	public function hasIssue()
	{
		foreach($this->possible_issues as $issue)
		{
			if($this->checkIssue($issue))
			{
				$this->has_issue = true;
			}//end if
		}//end foreach
		
		return $this->has_issue;
	}//end hasIssue

	/**
	 * True if the user has a private identifier (SSN, FSSN, CN), otherwise false.
	 */
	public function hasPrivateIdentifier() {
		foreach( array('ssn', 'foreign_ssn', 'certification_number') as $ident ) {
			$value = $this->$ident;

			if( ! empty($value) ) {
				return true;
			}
		}

		return false;
	}//end hasPrivateIdentifier

	/**
	 * Check if an email address is tied to this user.
	 */
	public function has_email( $email ) {
		$san = new PSU_Sanitizer_Email;

		$email = $san->clean( $email );
		$wp_email = $san->clean( $this->wp_email );
		$wp_email_alt = $san->clean( $this->wp_email_alt );

		return in_array( $email, array( $wp_email, $wp_email_alt ) );
	}//end has_email

	/**
	 * Returns true if this object has any loaders, i.e. if the provided identifier can be used to supply
	 * any person data.
	 */
	public function is_valid() {
		return $this->has_loader();
	}//end is_valid()

	/**
	 * load
	 * 
	 * loads data for the person
	 *
	 * @access		public
	 */
	public function load($type = null)
	{
		$func = '_load_' . $type;

		if($type && (method_exists($this, $func) || in_array($type, $this->load_params)))
		{
			return $this->$func();
		}//end if
		else {
			foreach( $this->failover_data as $priority => $failovers ) {
				foreach( $failovers as $failover ) {
					if( method_exists($failover, $func) ) {
						return call_user_func( array($failover, $func) );
					}
				}
			}
		}

		throw new PSUPersonException( PSUPersonException::INVALID_DATA_SET , 'Load: '.$type);
	}//end load

	/**
	 * loadIfNeeded
	 * 
	 * determines if the given data set has been loaded and if not, loads it
	 *
	 * @access		public
	 * @param   string $data_set Data Set to check/load
	 */
	public function loadIfNeeded($data_set)
	{
		if(!isset($this->data[$data_set]))
		{
			$this->load($data_set);
		}//end if
	}//end loadIfNeeded

	/**
	 * save
	 * 
	 * save handler for person
	 *
	 * @access		public
	 */
	public function save($type = null)
	{
		if($type && in_array($type, $this->load_params))
		{
			$func = '_save_' . $type;
			return $this->$func();
		}//end if
		else
		{
			throw new PSUPersonException( PSUPersonException::INVALID_DATA_SET , 'Save: '.$type);
		}//end else
	}//end save

	public static function adjust_weights( &$item, $key, $multiplier ) {
		$item *= $multiplier;
	}//end adjust_weights

	/**
	 * Method to get a PSUPerson with on-demand instantiation.
	 */
	public static function get( $identifier = null ) {
		static $people = array();
		//static $weights = array();

		if( $identifier === null ) {
			return null;
		}

		/*//
		array_walk( $weights, __CLASS__ . '::adjust_weights', 0.99 );
		asort($weights, SORT_NUMERIC);
		//*/

		//
		// add new person to our internal arrays
		//

		/*//
		if( isset($weights[$identifier]) ) {
			$weights[$identifier] += 1;
		} else {
			$weights[$identifier] = 1;
		}
		//*/

		if( !isset($people[$identifier]) ) {
			$people[$identifier] = new PSUPerson($identifier);
		}

		//
		// trim cache if it's too large
		//

		/*//
		if( count($people) > 50 ) {
			reset($weights);

			$key = key($weights);
			$person = $people[$key];

			$person->destroy();

			unset($person, $people[$key], $weights[$key]);
		}
		//*/

		return $people[$identifier];
	}//end get

	public function _load_issues()
	{
		$this->issues = array();
		$this->getIssues();
	}//end _load_isues

	/**
	 * __construct
	 * 
	 * PSUPerson constructor
	 *
	 * @access		public
	 * @param string $identifier Identifier of user
	 * @param mixed $load data sets to be loaded. deprecated in favor of lazy loading.
	 * @param $default_loaders \b bool true if we should use the default loaders
	 * @todo scrub old usage of $load and remove parameter
	 */
	public function __construct($identifier, $load = null, $default_loaders = true)
	{
		parent::__construct();
		
		$this->initial_identifier = trim($identifier);

		$this->merge_data_loaders( $this );
		
		// deprecated load params
		//$this->load_params = PSU::params($load, $this->default_load);

		if( $default_loaders ) {
			$this->add_failover( 'PSUPerson_Loader_SIS' );
			$this->add_failover( 'PSUPerson_Loader_Connect' );
			$this->add_failover( 'PSUPerson_Loader_Relationshiptemp' );
		}

		$this->events->trigger('load_all');
	}//end constructor
}//end class PSUPerson

class PSUPersonException extends PSUException
{
	const INVALID_DATA_SET = 1;
	const INVALID_PIDM = 2;
	const INVALID_USER = 3;

	private static $_msgs = array(
		self::INVALID_DATA_SET => 'You have specified an invalid data set',
		self::INVALID_PIDM => 'No user was found with that pidm',
		self::INVALID_USER => 'A matching user was not found'
	);

	/**
	 * Wrapper construct so PSUException gets our message array.
	 */
	function __construct($code, $append=null)
	{
		parent::__construct($code, $append, self::$_msgs);
	}//end constructor
}//end PSUPersonException

?>
