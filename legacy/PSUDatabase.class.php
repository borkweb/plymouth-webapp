<?php

/**
 * PSUDatabase.class.php
 *
 * === Modification History ===<br>
 * 1.0.0  06-apr-2004  [zbt]  original<br>
 * 1.1.0  06-apr-2007  [zbt]  class based<br>
 * 1.1.1  16-nov-2007  [mtb]  added connection<br>
 * 1.1.2  05-dec-2007  [mtb]  renamed class to PSUDatabase<br>
 * 1.1.3  05-dec-2007  [mtb]  using PSUSecurity instead of security<br>
 * 1.1.4  07-jan-2008  [zbt]  changed option passing<br>
 * 1.1.5  07-may-2008  [mtb]  enabled ADOdb Caching<br>
 */

require_once 'autoload.php';

define('ADODB_ASSOC_CASE', 0); // makes all fields returned in queries lowercase

/**
 * PSU Database API: database class for simplifying inserts, 
 *   deletes, updates, and replaces. Requires ADOdb.
 *
 * @section getone Using GetOne()
 *
 * ADOdb::GetOne() can quickly test if a record exists in the database:
 *
 * <pre><code>$result = (bool)$db->GetOne("SELECT 1 FROM foo WHERE baz = :baz", array('baz' => $baz));</code></pre>
 *
 * <var>$result</var> would now be set to True if the record exists, or False if the record was not found or an
 * error occured. (If False, you may want to check for errors using $db->ErrorNo().)
 *
 * @section datefields Date Fields
 *
 * Oracle 10g is able to store time within a DATE column. Try setting SQL Developer's
 * Database -> NLS Parameters -> Date Format to "YYYY-MM-DD HH24:MI:SS" to see the full
 * timestamp by default. PSUDatabase automatically configures the connection to return
 * these extended dates. ADODB_oci8->DBDate() has also been changed to generate the full
 * date with time. This, combined with the helper function PSUDatabase::SQLDate(),
 * simplifies the entry of dates into Oracle. 
 *
 * To generate a date formatted for a SQL statement, pass your database object and a date into PSUDatabase::SQLDate(). This date can be a string date in a format <a href="http://php.net/strtotime">strtotime()</a> understands, a unix timestamp (string or int is fine), or a <a href="http://php.net/datetime">DateTime</a> object.
 *
 * <pre><code>$new = array("birthdate" => PSUDatabase::SQLDate($db, "1982-07-20"));</code></pre>
 *
 * This date can then be used as an argument to ADOdb->GetUpdateSQL():
 *
 * <pre><code>$rs = $db->Execute("SELECT * FROM people WHERE pidm = 200443");
 * $sql = $db->GetUpdateSQL($rs, $new);
 * $db->Execute($sql);
 * // $sql = 'UPDATE people SET "BIRTHDATE"=TO_DATE('1982-07-20 00:00:00','YYYY-MM-DD HH24:MI:SS') WHERE pidm = 200443'</code></pre>
 *
 * Or binding via ADOdb->Execute():
 *
 * <pre><code>$db->Execute("UPDATE people SET birthdate = :birthdate WHERE pidm = 200443", $new);
 * // executes "UPDATE people SET birthdate = '1982-07-20 00:00:00' WHERE pidm = 200443"</code></pre>
 *
 * @version		1.1.5
 * @author		Zachary Tirrell <zbtirrell@plymouth.edu>
 * @author		Matthew Batchelder <mtbatchelder@plymouth.edu>
 * @copyright 2004, Plymouth State University, ITS
 */ 
class PSUDatabase
{
	public $globals;
	public $error;
	public $last_query;

	/**
	 * Connect to a database, or return connection parameters.
	 *
	 * @since     version 1.0.0
	 * @param     string $connect_data Connection identifier(s).
	 * @param     string $return Return type. One of: adodb (ADOdb object), mysql (raw mysql_connect() resource), return (connection parameters as an array).
	 * @return    mixed See the $return parameter.
	 */
	public function connect($connect_data, $return='adodb', $memcache = true)
	{
		//if the connect_data is not an array, lets make it one so life is easier
		if(!is_array($connect_data))
		{
			$connect_data = array($connect_data);
		}//end if

		$dbs = array();
		
		//loop over connection array and connect to the databases!
		foreach($connect_data as $key => $connect)
		{
			$connect = explode('/',$connect);
			//include the db connection info
			include($connect[0].'/'.$connect[1].'.php');			

			StatsD\StatsD::increment( "db.attempt.{$connect[0]}.{$connect[1]}" );

			$options = array_slice($connect, 2);

			//decide which object to create
			switch($return)
			{
				case 'adodb':
					require_once('adodb5/adodb.inc.php');
					switch($connect[0])
					{
						case 'oracle': $driver = in_array('fixcase', $options) ? 'oci8po' : 'oci8'; break;
						case 'mssql': $driver = 'mssqlpo'; break;
						// disabled by defautl because ADO_RecordSet::RecordCount() does not work on
						// PDO connections. PDO supports rowCount(), but ADOdb will not use it unless
						// $ADODB_COUNTRECS is true. -- ambackstrom, 4 sept 2009
						case 'mysql':
							$driver = 'mysql'; break; // DEBUG: disabling pdo support for now, it's not enabled on perseus
							if( !in_array('pdo', $options) ) {
								$driver = 'mysql'; break;
							}
							$driver = 'pdo';
							$port = null;
							$host = $_DB[$connect[0]][$connect[1]]['hostname'];
							if( strpos($host, ':') !== false )
							{
								list($host, $port) = explode(':', $host);
							}
							$_DB[$connect[0]][$connect[1]]['hostname'] = 'mysql:host=' . $host . ($port ? ';port=' . $port : '');
							unset($host, $port);
							break;
						default: $driver = $connect[0]; break;
					}//end switch

					global $ADODB_COUNTRECS;
					global $ADODB_CACHE_DIR;
					global $ADODB_FORCE_TYPE;
					global $ADODB_GETONE_EOF;
					global $ADODB_QUOTE_FIELDNAMES;

					$user = posix_getpwuid(posix_geteuid());

					$ADODB_COUNTRECS = false;
					$ADODB_CACHE_DIR = '/web/temp/ADOdbCache_' . md5($user['name']);
					$ADODB_GETONE_EOF = false;
					$ADODB_QUOTE_FIELDNAMES = true;
					$ADODB_FORCE_TYPE = ADODB_FORCE_IGNORE;

					if(!file_exists($ADODB_CACHE_DIR))
					{
						mkdir($ADODB_CACHE_DIR, 0700);
					}//end if
					if(!ADODB_PREFETCH_ROWS) define('ADODB_PREFETCH_ROWS', 50);
					$db = ADONewConnection($driver);

					if(substr($driver,0,4) == 'oci8') {
						$db->_initdate = false;

						// persistent oracle connections
						$db->autoRollback = true; // must rollback; don't use existing transaction
						$connect_method = 'PConnect';
					} else {
						$connect_method = 'Connect';
					}

					if(in_array('debug',$options)) $db->debug = true;

					if( in_array('nocache',$options) ) {
						$db->cacheSecs = 0;
					} else {
						$db->cacheSecs = 600;
					}

					$db->SetFetchMode(ADODB_FETCH_ASSOC);

					if( $memcache ) {
						$db->memCache = true;
						$db->memCacheHost = 'sualocin.plymouth.edu'; // $db->memCacheHost = $ip1; will work too
						$db->memCachePort = PSU::isdev() ? 21217 : 11217; // this is default memCache port
						$db->memCacheCompress = MEMCACHE_COMPRESSED; // Use 'true' to store the item compressed (uses zlib)
					}

					// modify some fields so date inserts get hour/minute/second as
					// well as the date. affects SYSDATE
					$db->dateformat = $db->NLS_DATE_FORMAT = 'RRRR-MM-DD HH24:MI:SS';
					$db->fmtDate = "'Y-m-d H:i:s'";
					$db->fmtTimeStamp = "'Y-m-d H:i:s'";
					//$db->sysDate = 'TRUNC(SYSDATE)'; // function that returns today's date. this is adodb-oci8's default

					$connected = $db->{$connect_method}($_DB[$connect[0]][$connect[1]]['hostname'], 
											 $_DB[$connect[0]][$connect[1]]['username'],
											 PSUSecurity::password_decode($_DB[$connect[0]][$connect[1]]['password']), 
											 $_DB[$connect[0]][$connect[1]]['database']);

					// need the default PDO MySQL behavior to be non-fixcase, for legacy code
					if( $connected && $driver === 'pdo' && $connect[0] === 'mysql' )
					{
						if( in_array('fixcase', $options) )
						{
							$db->_connectionID->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
						}
						else
						{
							$db->_connectionID->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
						}
					}

					if( ! $connected ) {
						StatsD\StatsD::increment( "db.failure.{$connect[0]}.{$connect[1]}" );
					}

					if( $connect[0] == 'oracle' )
					{
						$sql = "ALTER SESSION SET nls_date_format = 'YYYY-MM-DD HH24:MI:SS'";
						$db->Execute($sql);
					}

				break;
				case 'mysql':
					$db = mysql_connect($_DB[$connect[0]][$connect[1]]['hostname'], 
											 $_DB[$connect[0]][$connect[1]]['username'],
											 PSUSecurity::password_decode($_DB[$connect[0]][$connect[1]]['password']));
					mysql_select_db($_DB[$connect[0]][$connect[1]]['database'], $db);
				break;
				case 'return':
					$db = $_DB[$connect[0]][$connect[1]];
				break;
			}//end switch
			
			//unset the password so it doesn't appear in debug
			unset($db->password);
			
			$dbs[$key] = $db;
		}//end foreach
		
		return (count($dbs)==1)?$dbs[0]:$dbs;
	}//end connect

	/**
	 * Convert the specified date columns to unix timestamps.
	 * @param $rset_or_array \b ADORecordSet|array a record set, or a single row, or a number of rows
	 * @param $columns \b array the columns to convert
	 * @return array
	 */
	public static function dates2timestamps( $rset_or_array, $columns )
	{
		$tmp = array();

		if( is_string($columns) )
		{
			$columns = array($columns);
		}

		// if we find one of the columns in the first dimension of the input argument,
		// we only got one record as an array from the caller, so return a single row in kind
		$return_one = is_array($rset_or_array) && isset($rset_or_array[$columns[0]]);

		// wrap so our foreach works correctly
		if( $return_one )
		{
			$rset_or_array = array($rset_or_array);
		}

		foreach( $rset_or_array as $row )
		{
			foreach($columns as $column)
			{
				$row[$column] = strtotime($row[$column]);
			}

			$tmp[] = $row;
		}

		if( $return_one )
		{
			return $tmp[0];
		}

		return $tmp;
	}//end dates2timestamps

	/**
	 * delete
	 *
	 * Deletes from the given table using the given where clause
	 *
	 * @since		version 1.0.0
	 * @param  		ADOdb $db ADOdb connection
	 * @param     string $table Table name
	 * @param			string $where Where clause
	 * @return  	boolean
	 */
	function delete($db,$table,$where="1=0")
	{
		$ok = $db->Execute($sql = "DELETE FROM $table WHERE $where");	
		
		if($ok === false)
		{
			//$this->error->log("DATABASE", "SQL: $sql\nERROR: ".$db->ErrorMsg());
			return false;
		}
		else
			return true;
	}//end delete
	
	/**
	 * getcsv
	 *
	 * Returns the given rows in csv format
	 *
	 * @since		version 1.0.0
	 * @param     array $rows Array of strings
	 * @param			mixed $fieldnames Field names for rows
	 * @return  	string
	 */
	function getcsv($rows,$fieldnames=false)
	{
		$csv = "";

		if(is_array($rows))
		{
			$data = $rows;
		}
		else
		{
			// not implemented
			//include_once("/adodb-csvlib.inc.php");
			//$data = split("\n", _rs2serialize($rows),3);
			//$data = unserialize($data[2]);
		}
		
		if(is_array($fieldnames))
		{
			foreach($fieldnames as $key => $value)
				$fieldnames[$key] = str_replace(array('"',"\r"),array('""',""),$value);
			$csv .= '"'.implode('","',$fieldnames).'"';
		}
		
		foreach($data as $row)
		{
			foreach($row as $key => $value)
				$row[$key] = str_replace(array('"',"\r"),array('""',""),$value);
			$csv .= "\n\"".implode('","', $row).'"';
		}

		return $csv;
	}//end getcsv

	/**
	 * insert
	 *
	 * Inserts the given fields into the given table
	 *
	 * @since		version 1.0.0
	 * @param  		ADOdb $db ADOdb connection
	 * @param     string $table Table name
	 * @param			array $array array to quote
	 * @param			boolean $gpc_chk check gpc_magic_quotes first
	 * @return  	boolean
	 */
	function insert($db,$table,$array,$gpc_chk=true)
	{
		list($fields,$values) = $this->split_array($db,$array,$gpc_chk);
		$sql = "INSERT INTO $table ($fields) VALUES ($values)";
		$ok = $db->Execute($sql);
		$this->last_query = $sql;

		$GLOBALS['firephp']->log($sql, 'PSUDatabase->insert');
		
		if($ok === false)
		{
			//$this->error->log("DATABASE", "SQL: $sql\nERROR: ".$db->ErrorMsg());
			return false;
		}
		else
		{
			if($db->hasInsertID)
				return $db->Insert_ID();
			else
				return true;
		}
	}//end insert

	/**
	 * join_array
	 *
	 * Quotes then splits an array by keys and returns an array of comma delimitted fields and comma delimitted values
	 *
	 * @since		version 1.0.0
	 * @param  		ADOdb $db ADOdb connection
	 * @param			array $array array to quote
	 * @param			boolean $gpc_chk check gpc_magic_quotes first
	 * @return  	string
	 */
	function join_array($db,$array,$gpc_chk=true)
	{
		$array = $this->quote_array($db,$array,$gpc_chk);
		foreach($array as $field => $value)
			$array[$field] = "$field = $value";
		return implode(",",array_values($array));
	}//end join_array
	
	/**
	 * quote_array
	 *
	 * Quotes values in an array
	 *
	 * @since		version 1.0.0
	 * @param  		ADOdb $db ADOdb connection
	 * @param			array $array array to quote
	 * @param			boolean $gpc_chk check gpc_magic_quotes first
	 * @return  	mixed
	 */
	function quote_array($db,$array,$gpc_chk=true)
	{
		foreach($array as $field => $value)
		{
			if($value == "NULL" || preg_match("/date/i",$field) || preg_match("/time/i",$field) && !preg_match("/timezone/i",$field))
				continue;
			if($gpc_chk)
				$array[$field] = $db->qstr($value, get_magic_quotes_gpc());
			else
				$array[$field] = $db->qstr($value);
		}
		return $array;
	}//end quote_array

	/**
	 * replace
	 *
	 * MySQL Replace Into with the given fields into the given table
	 *
	 * @since		version 1.0.0
	 * @param  		ADOdb $db ADOdb connection
	 * @param     string $table Table name
	 * @param			array $array array to quote
	 * @param			boolean $gpc_chk check gpc_magic_quotes first
	 * @return  	boolean
	 */
	function replace($db,$table,$array,$gpc_chk=true)
	{
		list($fields,$values) = $this->split_array($db,$array,$gpc_chk);
		$ok = $db->Execute($sql = "REPLACE INTO $table ($fields) VALUES ($values)");
		$this->last_query = $sql;
		
		if($ok === false)
		{
			//$this->error->log("DATABASE", "SQL: $sql\nERROR: ".$db->ErrorMsg());
			return false;
		}
		else
		{
			if($db->Insert_ID())
				return $db->Insert_ID();
			else
				return true;
		}
	}//end replace

	/**
	 * split_array
	 *
	 * Quotes then splits an array by keys and returns an array of comma delimitted fields and comma delimitted values
	 *
	 * @since		version 1.0.0
	 * @param  		ADOdb $db ADOdb connection
	 * @param			array $array array to quote
	 * @param			boolean $gpc_chk check gpc_magic_quotes first
	 * @return  	mixed
	 */
	function split_array($db,$array,$gpc_chk=true)
	{
		$array = $this->quote_array($db,$array,$gpc_chk);
		$fields = array_keys($array);
		$values = array_values($array);
		return array(implode(",",$fields),implode(",",$values));
	}//end split_array

	/**
	 * update
	 *
	 * Updates the given fields in the given table using the given where clause
	 *
	 * @since		version 1.0.0
	 * @param  		ADOdb $db ADOdb connection
	 * @param     string $table Table name
	 * @param			array $array array to quote
	 * @param			string $where Where clause
	 * @param			boolean $gpc_chk check gpc_magic_quotes first
	 * @return  	boolean
	 */
	function update($db,$table,$array,$where="1=0",$gpc_chk=true)
	{
		if(is_array($array))
			$set = $this->join_array($db,$array,$gpc_chk);
		else
			$set = $array;
		$sql = "UPDATE $table SET $set WHERE $where";
		$ok = $db->Execute($sql);
		$this->last_query = $sql;

		$GLOBALS['firephp']->log($sql, 'PSUDatabase->update');
		
		if($ok === false)
		{
			//$this->error->log("DATABASE", "SQL: $sql\nERROR: ".$db->ErrorMsg());
			return false;
		}
		else
			return true;
	}//end update

	/**
	 * __construct
	 *
	 * constructor
	 *
	 * @since			version 1.1.0
	 * @param  		mixed $vars global variables
	 * @param			mixed $error errors
	 */
	public function __construct($vars = null, $error = null)
	{
		$this->globals = $vars;
		$this->error = $error;
	}//end __construct

	/**
	 * Use ADOdb's fmtDate to format a date in a way that works with GetUpdateSQL's
	 * comparison.
	 */
	public static function SQLDate($db, $time = null)
	{
		if($time === null)
		{
			$time = time();
		}

		// remove leading and training quote character
		$fmtDate = substr($db->fmtDate, 1, -1);

		$unix_timestamp = 0;

		// did we get a unix timestamp as a string?
		if(is_string($time) && (string)(int)$time == $time)
		{
			$time = (int)$time;
		}

		// try to convert $time to a unix timestamp
		if(is_int($time))
		{
			// this is what we want: a unix timestamp
			$unix_timestamp = $time;
		}
		if($time instanceof DateTime)
		{
			// got a nice DateTime, use its format() function
			return $time->format($fmtDate);
		}
		elseif(is_string($time))
		{
			// got a string, try to convert it ourselves (not optimal, bad
			$unix_timestamp = strtotime($time);

			// couldn't convert, throw an exception
			if($unix_timestamp === false)
			{
				throw new PSUDatabaseException(PSUDatabaseException::UNRECOGNIZED_DATE, $time);
			}
		}

		return date($fmtDate, $unix_timestamp);
	}//end 
}//end class PSUDatabase

class PSUDatabaseException extends PSUException
{
	const UNRECOGNIZED_DATE = 1;

	private static $_msgs = array(
		self::UNRECOGNIZED_DATE => 'Supplied date was not recognized by SQLDate'
	);

	/**
	 * Wrapper construct so PSUException gets our message array.
	 */
	function __construct($code, $append=null)
	{
		parent::__construct($code, $append, self::$_msgs);
	}
}
