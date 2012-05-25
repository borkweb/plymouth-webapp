<?php

/**
 * RFPermissions.class.php
 */

class RFPermissions
{
	var $db; // database object
	var $idm; // idm object
	var $server; // server we're dealing with

	var $dirs; // array to hold all valid directories

	/**
	 * RFPermissions
	 */
	function RFPermissions(&$idm, &$mysql, $server)
	{
		$this->db =& $mysql;
		$this->idm =& $idm;
		$this->server = $this->db->qstr($server);

		$sql = "SELECT * FROM remote_files WHERE server = {$this->server}";
		$result = $this->db->CacheExecute($sql);

		if($result === false)
		{
			trigger_error('failed to get master directory list', E_USER_ERROR);
		}

		$this->dirs = array();
		while($row = $result->FetchRow())
		{
			$this->dirs[$row['title']] = $row;
		}
	}

	/**
	 * Return a list of directories that a user can read.
	 *
	 * @access		public
	 * @param			int $pidm person identifier
	 */
	function directoriesForUser($pidm)
	{
		$result = array();

		foreach($this->dirs as $dir)
		{
			if($this->canRead($pidm, $dir))
			{
				if(substr($dir['path'], -1) !== '/')
				{
					$dir['path'] .= '/';
				}

				$result[] = $dir;
			}
		}

		usort($result, create_function('$a,$b', 'return strnatcasecmp($a["title"], $b["title"]);'));

		return $result;
	}

	/**
	 * Get the type of a remote file using SCPlib.
	 *
	 * @param       string $path the target file
	 * @return      string One of "dir," "file," or null
	 */
	function type($path)
	{
		if(substr($path, -1) == '/')
		{
			$path = substr($path, 0, -1);
		}

		$qpath = $this->db->qstr($path);
		$sql = "SELECT type FROM remote_files_cache WHERE server = {$this->server} AND path = $qpath AND stamp > UNIX_TIMESTAMP() - 6000";
		$cache = $this->db->GetOne($sql);

		if(count($cache) == 0)
		{
			$type = $GLOBALS['SCP']->type($path);

			if($type === null)
			{
				$type = 'NULL';
			}
			else
			{
				$type = $this->db->qstr($type);
			}

			$sql = "INSERT INTO remote_files_cache (server, path, type, stamp) VALUES ({$this->server}, $qpath, $type, UNIX_TIMESTAMP())
			        ON DUPLICATE KEY UPDATE type = $type";
			$this->db->Execute($sql);
		}

		return $type;
	}

	/**
	 * Determine the root directory for a given path.
	 *
	 * @param			string $path the full path to the file we'll be acting on.
	 */
	function directoryForPath($path)
	{
		$max_len = null;
		$match = null;

		if(substr($path, -1) != '/')
		{
			$path = dirname($path) . '/';
		}

		// enforce absolute paths
		if(substr($path, 0, 1) != '/')
		{
			throw new RFException(RFException::RF_RELATIVE_PATH);
		}

		// don't allow paths to move up a directory
		$result  = preg_match('!/\.\./|/\.\.$!', $path);
		$result += preg_match('!/\\\.\\\./|/\\\.\\\.$!', $path);
		if($result > 0)
		{
			throw new RFException(RFException::RF_INVALID_TRAVERSAL);
		}

		foreach($this->dirs as $dir)
		{
			$dir_len = strlen($dir['path']);

			$path_trimmed = substr($path, 0, $dir_len);

			if($dir['path'] == $path_trimmed && ($max_len === null || $dir_len > $max_len))
			{
				$max_len = $dir_len;
				$match = $dir;
			}
		}

		if($match === null)
		{
			throw new RFException(RFException::RF_OUTSIDE_DIRS);
		}
		
		return $match;
	}//end directoryForPath

	/**
	 * Determine if a person can write to the specified path.
	 */
	function canWrite($pidm, $path)
	{
		return $this->checkPermission($pidm, $path, 'write');
	}//end canWrite

	/**
	 * Determine if a person can read from the specified path.
	 */
	function canRead($pidm, $path)
	{
		return $this->checkPermission($pidm, $path, 'read');
	}//end canRead

	/**
	 * Determine if a person can delete the specified path.
	 */
	function canDelete($pidm, $path)
	{
		return $this->checkPermission($pidm, $path, 'delete');
	}//end canDelete

	/**
	 * Does the actual permission checking work.
	 *
	 * @access		public
	 * @param			int $pidm person identifier
	 * @param			string|array $path the path as a directory array or string path
	 * @param			string $method the attempted method
	 */
	function checkPermission($pidm, $path, $method)
	{
		if(!is_array($path))
		{
			try
			{
				$dir = $this->directoryForPath($path);
			}
			catch(RFException $e)
			{
				if($e->getCode() == RFException::RF_OUTSIDE_DIRS)
				{
					return false;
				}

				// wasn't RF_OUTSIDE_DIRS, throw it again
				throw $e;
			}
		}
		else
		{
			$dir = $path;
		}

		if($this->idm->hasAttribute($pidm, 'permission', 'remotefiles_admin'))
		{
			return true;
		}

		$permission = sprintf('remotefiles_%s_%s', $dir['title'], $method);

		if($this->idm->hasAttribute($pidm, 'permission', $permission))
		{
			return true;
		}

		return false;
	}

	/**
	 * Return a list of valid servers.
	 */
	function servers()
	{
		$sql = "SELECT DISTINCT server FROM remote_files ORDER BY server";
		return $this->db->GetCol($sql);
	}//end servers
}

// custom exception for RFPermissions
require_once('PSUException.class.php');
class RFException extends PSUException {
	const RF_RELATIVE_PATH = 1;
	const RF_INVALID_TRAVERSAL = 2;
	const RF_OUTSIDE_DIRS = 3;

	private static $_msgs = array(
		self::RF_RELATIVE_PATH => 'Relative paths not allowed',
		self::RF_INVALID_TRAVERSAL => 'Attempt to travese up directory tree',
		self::RF_OUTSIDE_DIRS => 'Path is outside all directories'
	);

	/**
	 * Wrapper construct so PSUException gets our message array.
	 */
	function __construct($code, $append=null)
	{
		parent::__construct($code, $append, self::$_msgs);
	}
}

// vim:ts=2:sw=2:noet:
?>
