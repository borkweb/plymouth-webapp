<?php

require_once('PSUTools.class.php');
require_once('PSULog.class.php');

/**
 * A class to manage hardware associated with users.
 */
class PSUHardware implements Iterator, ArrayAccess, Countable {
	public $hardware = array();
	static $database = 'mysql/calllog';

	const DHCP_DYNAMIC = 1;
	const DHCP_PERSISTENT = 2;

	/**
	 * Class constructor.
	 */
	public function __construct( PSUPerson $person ) {
		$this->person = $person;
		$this->load();

		$this->log = new PSULog('ape');
	}//end __construct

	/**
	 * Add a piece of hardware to this user.
	 * @param $computer_name \b string
	 */
	public function addHardware( $name, $mac, $comments = '' ) {
		$sName = self::sanitizeName($name);
		$sMac = self::sanitizeMAC($mac);
		$sComments = self::sanitizeComments($comments);

		//
		// check for bad name or mac
		//

		$invalid = array();

		if( $sName === false ) {
			$invalid[] = 'computer name';
		}

		if( $sMac === false ) {
			$invalid[] = 'MAC address';
		}

		if( $count = count($invalid) ) {
			$werewas = $count === 1 ? "was" : "were";
			throw new Exception("an invalid " . implode(" and ", $invalid) . " $werewas provided");
		}

		//
		// insert records into the databas
		//

		$db = PSU::db(self::$database);
		$email = $this->person->username . "@plymouth.edu";

		// if a name was provided, use persistent dhcp. otherwise, it's dynamic.
		$dhcp = $this->dhcpForName($sName);

		$sql = "INSERT INTO hardware_inventory (email, mac_address, computer_name, dhcp, user_group, status, last_modified_date, comments)
			VALUES (?, ?, ?, ?, 'FACSTAFF', 1, NOW(), ?)";

		if( $db->Execute( $sql, array($email, $sMac, $sName, $dhcp, $sComments) ) ) {
			$this->log->write( sprintf('Hardware inventory %d: new record, name=%s, mac=%s', $db->Insert_ID(), $sName, $sMac) );
			return true;
		}

		return false;
	}//end addHardware

	/**
	 * Remove a piece of hardware from the database.
	 * @param $id \b int the hardware id number
	 */
	public function deleteHardware( $id ) {
		$db = PSU::db(self::$database);

		// we should have that record in $this->hardware
		if( !isset($this->hardware[$id]) ) {
			$_SESSION['errors'][] = 'That record was not found in the PSUHardware cache. Please notify ITS.';
			return false;
		}

		$name = $this->hardware[$id]['computer_name'];
		$mac = $this->hardware[$id]['mac_address'];

		$sql = "DELETE FROM hardware_inventory WHERE id = ? AND email LIKE ? '@%' LIMIT 1";
		$db->Execute($sql, array($id, $this->person->username));

		if( $db->Affected_Rows() === 0 ) {
			$_SESSION['errors'][] = 'Delete failed.';
			return false;
		}

		unset($this->hardware[$id]);

		$this->log->write( sprintf('Hardware inventory %d: deleted name=%s, mac=%s', $id, $name, $mac) );

		return true;
	}//end deleteHardware

	/**
	 * Return the correct DHCP_* constant based on whether or not a computer name was provided.
	 */
	public function dhcpForName( $name ) {
		// if a name was provided, use persistent dhcp. otherwise, it's dynamic.
		return $name ? self::DHCP_PERSISTENT : self::DHCP_DYNAMIC;
	}//end dhcpForname

	/**
	 * Load all hardware for this user.
	 */
	public function load() {
		$db = PSU::db(self::$database);

		$sql = "SELECT t1.id, 
									 LOWER(t1.mac_address) AS mac_address, 
									 LOWER(t1.computer_name) AS computer_name, 
									 ip_address,
									 comments,
									 (SELECT COUNT(1) FROM hardware_inventory t2 WHERE UPPER(t1.mac_address) = UPPER(t2.mac_address) AND t2.status = 1) as mac_count, 
									 (SELECT COUNT(1) FROM hardware_inventory t2 WHERE UPPER(t1.computer_name) = UPPER(t2.computer_name) AND t2.status = 1) as name_count,
									 (SELECT COUNT(1) FROM hardware_inventory t2 WHERE t1.ip_address = t2.ip_address AND t2.status = 1) as ip_count
							FROM hardware_inventory AS t1 
						 WHERE t1.status = 1 AND t1.email LIKE ? '@%'";
		$rset = $db->Execute($sql, array( $this->person->username ));

		if( $db->ErrorNo() !== 0 ) {
			return false;
		}

		foreach($rset as $row) {
			$id = $row['id'];
			unset($row['id']);

			$this->hardware[$id] = $row;
		}

		return true;
	}//end load

	/**
	 * Change a record's comments
	 * @param $id \b int record id to change
	 * @param $comments \b string the new comments
	 */
	public function changeComments( $id, $comments ) {
		$comments = self::sanitizeComments( $comments );

		if( !isset($this->hardware[$id]) ) {
			throw new Exception("user does not own the specified record");
		}

		$oldcomments = self::sanitizeComments( $this->hardware[$id]['comments'] );

		// do nothing if Comments didn't change
		if( $oldcomments === $comments ) {
			return;
		}

		$this->hardware[$id]['comments'] = $comments;

		$db = PSU::db(self::$database);
		$sql = "UPDATE hardware_inventory SET comments = ? WHERE id = ?";
		$db->Execute($sql, array($comments, $id));

		$this->log->write( sprintf('Hardware inventory %d: Comments changed from %s to %s', $id, $oldcomments, $comments), $this->person->username );
	}//end changeComments

	/**
	 * Change a record's MAC address.
	 * @param $id \b int record id to change
	 * @param $mac \b string the new MAC address
	 */
	public function changeMAC( $id, $mac ) {
		$mac = self::sanitizeMAC( $mac );

		if( $mac === false ) {
			throw new Exception("invalid MAC address provided");
		}

		if( !isset($this->hardware[$id]) ) {
			throw new Exception("user does not own the specified record");
		}

		$oldmac = self::sanitizeMAC( $this->hardware[$id]['mac_address'] );

		// do nothing if MAC didn't change
		if( $oldmac === $mac ) {
			return;
		}

		if( self::valueExists('mac_address', $mac) ) {
			throw new Exception("mac address already exists in database");
		}

		$this->hardware[$id]['mac_address'] = $mac;

		$db = PSU::db(self::$database);
		$sql = "UPDATE hardware_inventory SET mac_address = ? WHERE id = ?";
		$db->Execute($sql, array($mac, $id));

		$this->log->write( sprintf('Hardware inventory %d: MAC changed from %s to %s', $id, $oldmac, $mac), $this->person->username );
	}//end changeMAC

	/**
	 * Change the computer name for a given record.
	 * @param $id \b int the record id
	 * @param $name \b string the new name
	 */
	public function changeName( $id, $name ) {
		$name = self::sanitizeName( $name );

		if( $name === false ) {
			throw new Exception("invalid computer name provided");
		}

		if( !isset($this->hardware[$id]) ) {
			throw new Exception("user does not own the specified record");
		}

		$oldname = self::sanitizeName($this->hardware[$id]['computer_name']);

		// do nothing if MAC didn't change
		if( $oldname === $name ) {
			return;
		}

		//if( $result = self::valueExists('computer_name', $name) ) {
		//	throw new Exception("name already exists in database");
		//}

		$this->hardware[$id]['computer_name'] = $name;

		$dhcp = $this->dhcpForName($name);

		$db = PSU::db(self::$database);
		$sql = "UPDATE hardware_inventory SET computer_name = ?, dhcp = ? WHERE id = ?";
		$db->Execute($sql, array($name, $dhcp, $id));

		$this->log->write( sprintf('Hardware inventory %d: computer name changed from %s to %s', $id, $oldname, $name), $this->person->username );
	}//end changeName

	/**
	 * Check for a duplicate record.
	 */
	public static function valueExists( $column, $value ) {
		if( $column !== 'computer_name' && $column !== 'mac_address' ) {
			throw new Exception('unknown column name provided');
		}

		$db = PSU::db(self::$database);

		$sql = "SELECT 1 FROM hardware_inventory WHERE $column = ? AND status = 1";
		return (bool)$db->GetOne($sql, array($value));
	}//end valueExists

	/**
	 * Get the username for a specified record ID.
	 */
	public static function userForID( $id ) {
		$db = PSU::db(self::$database);

		$username = $db->GetOne("SELECT email FROM hardware_inventory WHERE id = ?", array($id));
		$username = substr( $username, 0, strpos($username, "@") );

		return $username;
	}//end userForID

	/**
	 * Validate a computer name.
	 * @param $name \b string the computer name
	 * @return the formatted name, or false on failure
	 */
	public static function sanitizeName( $name ) {
		// a blank name is ok.
		if( $name === '' ) {
			return null;
		}

		$name = strtoupper( trim($name) );
		if( preg_match( '/^[A-Z][A-Z0-9-]+[A-Z0-9]$/', $name ) ) {
			return $name;
		}

		return false;
	}//end validateName

	/**
	 * Sanitize comments
	 * @param $comments \b string comments to sanitize
	 */
	public function sanitizeComments($comments)
	{
		return filter_var($comments, FILTER_SANITIZE_STRING);
	}//end sanitizeComments

	/**
	 * Santize a MAC address.
	 */
	public static function sanitizeMAC( $mac ) {
		$mac = strtoupper($mac);
		$mac = preg_replace( '/[^A-F0-9]/', '', $mac );

		// mac with extra stuff removed should always be 12 chars
		if( strlen($mac) !== 12 ) {
			return false;
		}

		// break into chunks separated by :
		$mac = str_split( $mac, 2 );
		$mac = implode(":", $mac);

		return $mac;
	}

	// Iterator functions
	public function current() { return current($this->hardware); }
	public function key() { return key($this->hardware); }
	public function next() { return next($this->hardware); }
	public function rewind() { return reset($this->hardware); }
	public function valid() { return key($this->hardware) !== null; }

	// ArrayAccess functions
	public function offsetExists($offset) { return isset($this->hardware[$offset]); }
	public function offsetGet($offset) { return isset($this->hardware[$offset]) ? $this->hardware[$offset] : null; }
	public function offsetSet($offset, $value) { $this->hardware[$offset] = $value; }
	public function offsetUnset($offset) { unset($this->hardware[$offset]); }

	// Countable functions
	public function count() { return count($this->hardware); }
}//end PSUHardware
