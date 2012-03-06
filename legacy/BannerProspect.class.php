<?php

/**
 * BannerProspect
 *
 * Class to handle inserting prospects into Banner tables. API for Banner prospect tables.
 * 
 * @module	BannerProspect.class.php
 * @copyright 2008, Plymouth State University, ITS
 */
 
class BannerProspect
{
	/**
	 * The Banner database object.
	 * @ignore
	 */
	private $banner;

	/**
	 * List of Banner tables.
	 */
	protected static $tables = array(
		'person' => 'saturn.srtpers',
		'ident' => 'saturn.srtiden',
		'race' => 'saturn.srtprac',
		'source' => 'saturn.srtprel',
		'email' => 'saturn.srtemal',
		'international' => 'saturn.srtintl',
		'address' => 'saturn.srtaddr',
		'phone' => 'saturn.srttele',
		'highschool' => 'saturn.srthsch',
		'college' => 'saturn.srtpcol',
		'interests' => 'saturn.srtints',
		'tests' => 'saturn.srttest'
	);

	/**
	 * Internal structure for the prospect data.
	 * @ignore
	 */
	private $data = array();

	/**
	 * Maximum number of tries when updating sequence numbers.
	 */
	const MAX_SEQUENCE_RETRY = 5;

	/**
	 * Object setup.
	 *
	 * @param	  ADOdb $banner The Banner database object.
	 */
	function __construct(&$banner)
	{
		$this->banner = &$banner;

		$this->_set_term_code();

		// some defaults
		$this->activity_date = 'SYSDATE';
		$this->degc_code = '000000';
		$this->majr_code = '0000';
	}//end __construct

	/**
	 * Function to assign prospect parameters.
	 * @ignore
	 */
	function __set($key, $value)
	{
		if(empty($value))
		{
			return;
		}

		// does a special method exist for this key?
		if(method_exists($this, '_set_' . $key))
		{
			$function = "_set_$key";
			$this->$function($value);
		}
		else
		{
			$this->data[$key] = $value;
		}
	}//end __set

	/**
	 * __get
	 *
	 * Function to read prospect parameters.
	 *
	 * @access	public
	 * @param	integer $key key of array data
	 */
	function __get($key)
	{
		if(!array_key_exists($key, $this->data))
		{
			throw new ProspectException(ProspectException::KEY_ERROR, $key);
		}

		return $this->data[$key];
	}//end __get

	/**
	 * save
	 *
	 * Save new prospect record.
	 *
	 * @access	public
	 */
	function save()
	{
		// bwskwprv.P_InsertDataIntoTable
		$this->_generate_ridm(); // line 370
		$this->_generate_prospect_id(); // line 413
		
		$this->_save_ident(); // SRTIDEN, ident
		$this->_save_person(); // SRTPERS, person
		//$this->_save_race(); // SRTPRAC, race. 'temporary race table?'
		$this->_save_source(); // SRTPREL, source
		$this->_save_email(); // SRTEMAL, email
		//$this->_save_international(); // SRTINTL, international
		$this->_save_address(); // SRTADDR, address
		$this->_save_telephone(); // SRTTELE, phone
		//$this->_save_highschool(); // SRTHSCH, highschool
		//$this->_save_college(); // SRTPCOL, college
		//$this->_save_interests(); // SRTINTS, interests
		//$this->_save_tests(); // SRTTEST, tests
		// SRTLEND
		// SRTMATL
	}//end save

	/**
	 * _banner_valdate
	 * 
	 * Validate a value against a Banner table.
	 *
	 * @access	public
	 * @param	string $table name of table to select from
	 * @param	string $column name of column to select from
	 * @param	mixed $value value to use as paramater
	 */
	function _banner_validate($table, $column, $value)
	{
		$params = array('value' => $value);

		$sql = "SELECT 1 FROM $table WHERE $column = :value";
		$result = (bool)$this->banner->CacheGetOne($sql, $params);

		if($this->banner->ErrorNo() > 0)
		{
			throw new ProspectsException(ProspectsException::SQL_ERROR, $this->banner->ErrorNo());
		}

		return $result;
	}//end _banner_validate
	
	/**
	 * _generate_ridm
	 * 
	 * Generate a RIDM.
	 *
	 * @access	public
	 * @param	integer $i value used to determan if the max number of tries has been reached, defaults to 0
	 * @return	mixed returns an ridm number?
	 */
	private function _generate_ridm($i = 0)
	{
		// max tries before we error out
		if($i > self::MAX_SEQUENCE_RETRY)
		{
			throw new ProspectException(ProspectException::SEQUENCE_FAILED, 'RIDM');
		}

		$sql = "SELECT sobseqn_maxseqno FROM sobseqn WHERE sobseqn_function = 'RIDM'";
		$ridm = $ridm_prev = $this->banner->GetOne($sql);
		$ridm += 1;

		$sql = "UPDATE sobseqn
		           SET sobseqn_maxseqno = :ridm
		         WHERE sobseqn_function = 'RIDM' AND
		               sobseqn_maxseqno = :ridm_prev";

		$result = $this->banner->Execute($sql, array(
			'ridm' => $ridm,
			'ridm_prev' => $ridm_prev
		));

		// error in sql statement
		if($result === false)
		{
			throw new ProspectException(ProspectException::SQL_ERROR);
		}

		// no rows updated (concurrency problem?), try again
		if($this->banner->Affected_Rows() === 0)
		{
			return $this->_generate_ridm(++$i);
		}
		
		$this->ridm = $ridm;
		return $this->__get('ridm');
	}//end _generate_ridm

	/**
	 * _generate_prospect_id
	 *
	 * Generate a prospect_id via srkprel.f_get_prospect_sobseqn.
	 *
	 * @access	public
	 * @return	integer $prospect_id the new prospect_id
	 */
	private function _generate_prospect_id()
	{
		$sql = "BEGIN :prospect_id := srkprel.f_get_prospect_sobseqn('PROSPECT_ID'); END;";
		$stmt = $this->banner->PrepareSP($sql);
		$this->banner->OutParameter($stmt, $prospect_id, 'prospect_id');
		$result = $this->banner->Execute($stmt);

		if($result === false)
		{
			throw new ProspectException(ProspectException::SQL_FAILED);
		}

		$this->id = $prospect_id;
		return $this->__get('id');
	}//end _generate_prospect_id

	/**
	 * _save_address
	 *
	 * Save address to SRTADDR.
	 *
	 * @access	public
	 * @return	mixed returns the result of the query
	 */
	private function _save_address()
	{
		$table = self::$tables['address'];

		$fields = array("ridm", "street_line1", "street_line2", "street_line3", "city",
			"stat_code", "cnty_code", "zip", "natn_code", "activity_date");

		$extra = array(
			'seqno' => 1,
			'atyp_code' => 'MA' // STVATYP: "mailing address"
		);

		$insert = $this->_insert_array($table, $fields, $extra);
		return $this->_save_data($table, $insert);
	}//end _save_address()

	/**
	 * _save_email
	 *
	 * Save email address to SRTEMAL.
	 *
	 * @access	public
	 * @return	mixed returns the result of the query
	 */
	private function _save_email()
	{
		$table = self::$tables['email'];

		$fields = array("ridm", "email_address", "activity_date");

		$extra = array(
			'status_ind' => 'A',
			'preferred_ind' => 'Y',
			'disp_web_ind' => 'Y',
			'emal_code' => 'PE',
			'user_id' => 'App: iGrad'
		);

		$insert = $this->_insert_array($table, $fields, $extra);
		return $this->_save_data($table, $insert);
	}//end _save_email

	/**
	 * _save_ident
	 *
	 * Save general person data.
	 *
	 * @access	public
	 * @return	mixed returns the result of the query
	 */
	private function _save_ident()
	{
		$table = self::$tables['ident'];

		$fields = array('ridm', 'id', 'last_name', 'first_name', 'mi', 'activity_date');
		$extra = array(
			'ntyp_code' => 'PREF' // gtvntyp: "preferred name"
		);

		$insert = $this->_insert_array($table, $fields, $extra);
		return $this->_save_data($table, $insert);
	}//end _save_ident

	/**
	 * _save_person
	 *
	 * Save person data into SRTPERS.
	 *
	 * @access	public
	 * @return	mixed returns result of query
	 */
	private function _save_person()
	{
		$table = self::$tables['person'];

		$fields = array('ridm', 'birth_date', 'birth_mon', 'birth_day', 'birth_year',
			'birth_century', 'ethn_code', 'sex', 'name_prefix', 'activity_date');

		$insert = $this->_insert_array($table, $fields);
		return $this->_save_data($table, $insert);
	}//end _save_person()

	/**
	 * _save_race
	 *
	 * Save race data into SRTPRAC.
	 *
	 * @access	public
	 * @return	mixed returns the result of the query
	 */
	private function _save_race()
	{
		$table = self::$tables['race'];

		$fields = array('ridm', 'race_cde', 'activity_date');

		$insert = $this->_insert_array($table, $fields);
		return $this->_save_data($table, $insert);
	}//end _save_race

	/**
	 * _save_source
	 *
	 * Save source data into SRTPREL.
	 *
	 * @access	public
	 * @return	mixed returns the result of the query
	 */
	private function _save_source()
	{
		$table = self::$tables['source'];

		$fields = array("ridm", "prel_code", "tape_id", "term_code",
			"admin_seqno", "levl_code", "coll_code", "camp_code", "program", "degc_code", "majr_code",
			"dept_code", "recr_code", "admt_code", "rsta_code", "styp_code", "egol_code",
			"sbgi_code", "rtyp_code", "ctyp_code", "term_code_ctlg", "activity_date");

		$extra = array('add_date' => 'SYSDATE', 'admin_seqno' => 1, 'user' => 'www_user');

		$insert = $this->_insert_array($table, $fields, $extra);
		return $this->_save_data($table, $insert);
	}//end _save_source
	
	/**
	 * _save_telephone
	 *
	 * Save telephone data to SRTTELE.
	 *
	 * @access	public
	 * @return	mixed returns the result of the query
	 */
	function _save_telephone()
	{
		// don't bother doing anything if phone_number was not set
		if(!array_key_exists('phone_number', $this->data))
		{
			return true;
		}

		$table = self::$tables['phone'];

		$fields = array('ridm', 'phone_area', 'phone_number', 'tele_code', 'activity_date');

		$extra = array(
			'seqno' => 1
		);

		$insert = $this->_insert_array($table, $fields, $extra);
		return $this->_save_data($table, $insert);
	}//end _save_telephone

	/**
	 * _insert_array
	 *
	 * Build an array suitable for ADOdb->GetInsertSQL().
	 *
	 * @access	public
	 * @param	string $table the table name
	 * @param	array $fields the fields to pull from the $data array
	 * @param	array $additional extra fields to include in the insert array
	 * @return	array an associative array
	 */
	private function _insert_array($table, $fields, $additional = array())
	{
		// remove user from table name, ie. saturn.srtpers -> srtpers
		$pos = strpos($table, ".");
		if($pos !== false)
		{
			$table = substr($table, $pos + 1);
		}

		$insert = array();

		foreach($fields as $field)
		{
			if(array_key_exists($field, $this->data))
			{
				$insert["{$table}_{$field}"] = $this->data[$field];
			}
			else
			{
				//throw new ProspectsException(ProspectsException::MISSING_FIELD, $field);
			}
		}

		foreach($additional as $key => $value)
		{
			$insert["{$table}_{$key}"] = $value;
		}

		return $insert;
	}//end _insert_array

	/**
	 * _save_data
	 *
	 * Take an array of data and save it do a table.
	 *
	 * @access	public
	 * @param	string $table table name (user.table)
	 * @param	array $insert associative array of data to insert
	 * @return	boolean true if the call succeeded, false otherwise
	 */
	private function _save_data($table, $insert)
	{
		$rs = $this->banner->CacheExecute("SELECT * FROM $table WHERE 1=0");
		$sql = $this->banner->GetInsertSQL($rs, $insert);
		
		$result = $this->banner->Execute($sql);

		if($result === false)
		{
			switch($this->banner->ErrorNo())
			{
				case 1400: throw new ProspectsException(ProspectsException::MISSING_FIELD);
			}
		}

		return $result;
	}//end _save_data

	/**
	 * _set_birth_date
	 *
	 * Custom code when birth_date is set.
	 *
	 * @access	public
	 * @param	date $date 
	 */
	private function _set_birth_date($date)
	{
		$this->data['birth_date'] = strtotime($date);
		$this->data['birth_mon'] = strftime('%m', $this->data['birth_date']);
		$this->data['birth_day'] = strftime('%d', $this->data['birth_date']);
		$this->data['birth_year'] = strftime('%g', $this->data['birth_date']);
		$this->data['birth_century'] = strftime('%C', $this->data['birth_date']);
	}//end _set_birth_date

	/**
	 * _set_ethn_code
	 *
	 * Validate ethnicity code.
	 *
	 * @access	public
	 * @param	mixed $code code for particular ethn
	 */
	private function _set_ethn_code($code)
	{
		if(!$this->_banner_validate('stvethn', 'stvethn_code', $code))
		{
			throw new ProspectsException(ProspectsException::INVALID_ETHN);
		}
		
		$this->data['ethn_code'] = $code;
	}//end _set_ethn_code

	/**
	 * _set_levl_code
	 *
	 * Validate the level code.
	 *
	 * @access	public
	 * @param	mixed $code level code
	 */
	private function _set_levl_code($code)
	{
		if(!$this->_banner_validate('stvlevl', 'stvlevl_code', $code))
		{
			throw new ProspectsException(ProspectsException::INVALID_LEVL_CODE);
		}
		
		$this->data['levl_code'] = $code;
	}//end _set_levl_code

	/**
	 * _set_state
	 *
	 * Set the state, validating against STVSTAT.
	 *
	 * @access	public
	 * @param	mixed $state state code
	 */
	private function _set_state($state)
	{
		if(!$this->_banner_validate('stvstat', 'stvstat_code', $state))
		{
			throw new ProspectsException(ProspectsException::INVALID_STAT);
		}

		$this->data['stat_code'] = $state;
	}//end _set_state
	
	/**
	 * _set_sex
	 *
	 * Set the gender, validating against hard-coded list.
	 *
	 * @access	public
	 * @param	string $sex character of sex
	 */
	private function _set_sex($sex)
	{
		$valid_sex = array("M", "F", "N"); // from field comment on SRTPERS

		if(!in_array($sex, $valid_sex))
		{
			throw new ProspectsException(ProspectsException::INVALID_SEX);
		}

		$this->data['sex'] = $sex;
	}//end _set_sex

	/**
	 * _set_phone_area
	 *
	 * Validate the area code.
	 *
	 * @access	public
	 * @param	integer $area area code
	 */
	private function _set_phone_area($area)
	{
		if(!filter_var($area, FILTER_VALIDATE_INT, array(100, 999)))
		{
			throw new ProspectsException(ProspectsException::INVALID_PHONE);
		}

		$this->data['phone_area'] = $area;
	}//end _set_phone_area

	/**
	 * _set_phone_number
	 *
	 * Validate the phone number.
	 *
	 * @access	public
	 * @param	integer $number phone number
	 */
	private function _set_phone_number($number)
	{
		$args = array(
			'options' => array(
				'min_range' => 1000000,
				'max_range' => 9999999
			)
		);

		if(!filter_var($number, FILTER_VALIDATE_INT, $args))
		{
			throw new ProspectsException(ProspectsException::INVALID_PHONE);
		}

		$this->data['phone_number'] = $number;
	}//end _set_phone_number

	/**
	 * _set_prel_code
	 *
	 * Validate prel code against STVPREL.
	 *
	 * @access	public
	 * @param	mixed $code prel code
	 */
	private function _set_prel_code($code)
	{
		if(!$this->_banner_validate('saturn.stvprel', 'stvprel_code', $code))
		{
			throw new ProspectsException(ProspectsException::INVALID_PREL_CODE);
		}

		$this->data['prel_code'] = $code;
	}//end _set_prel_code

	/**
	 * _set_tele_code
	 *
	 * Validate phone type against STVTELE.
	 *
	 * @access	public
	 * @param	mixed $code phone code
	 */
	private function _set_tele_code($code)
	{
		if(!$this->_banner_validate('saturn.stvtele', 'stvtele_code', $code))
		{
			throw new ProspectsException(ProspectsException::INVALID_TELE_CODE);
		}

		$this->data['tele_code'] = $code;
	}//end _set_tele_code

	/**
	 * _set_term_code
	 *
	 * Set the term code.
	 *
	 * @access	public
	 */
	private function _set_term_code()
	{
		$term = $GLOBALS['BANNER']->GetOne("SELECT f_get_currentterm('GR') FROM dual");
		
		if($GLOBALS['BANNER']->ErrorNo() > 0)
		{
			throw new ProspectsException(ProspectsException::SQL_ERROR);
		}

		$this->data['term_code'] = $term;
	}//end _set_term_code
}//end BannerProspect

/**
 * Base class for exceptions.
 */
require_once('PSUException.class.php');

/**
 * ProspectException
 *
 * Custom exceptions for GradProspects.
 *
 * @module	BannerProspect.class.php
 */
class ProspectsException extends PSUException
{
	const MISSING_FIELD = 1;
	const SEQUENCE_FAILED = 2;
	const SQL_ERROR = 3;
	const KEY_ERROR = 4;
	const INVALID_ETHN = 5;
	const INVALID_SEX = 6;
	const INVALID_STAT = 7;
	const INVALID_PHONE = 8;
	const INVALID_TELE_CODE = 9;
	const INVALID_PREL_CODE = 10;
	const INVALID_LEVL_CODE = 11;

	private static $_msgs = array(
		self::MISSING_FIELD => 'A required field was missing, insert statement could not be built',
		self::SEQUENCE_FAILED => 'Could not update sequence after multiple retries',
		self::SQL_ERROR => 'A SQL error has occured',
		self::KEY_ERROR => 'An invalid key was specified',
		self::INVALID_ETHN => 'An invalid ethnicity was specified',
		self::INVALID_SEX => 'An invalid sex was specified',
		self::INVALID_STAT => 'An invalid state was specified',
		self::INVALID_PHONE => 'An invalid phone number was specified',
		self::INVALID_TELE_CODE => 'An invalid telephone code was specified',
		self::INVALID_PREL_CODE => 'An invalid prospect code was specified',
		self::INVALID_LEVL_CODE => 'An invalid student level code was specified'
	);

	/**
	 * __construct
	 *
	 * Wrapper construct so PSUException gets our message array.
	 *
	 * @param	mixed $code exception code
	 * @param	mixed $append what to append to exception
	 */
	function __construct($code, $append=null)
	{
		parent::__construct($code, $append, self::$_msgs);
	}
}//end ProspectsException

// vim:ts=2:sw=2:noet:sts=0:
?>
