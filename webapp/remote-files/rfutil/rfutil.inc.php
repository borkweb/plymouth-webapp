<?php

/**
 * Get information for the file with the given path.
 */
function fileInformation($path, $f)
{
	// skip . and ..
	if($f == '.' || $f == '..')
	{
		return false;
	}

	$full_path = $path . $f;

	$tmp = array();
	$tmp['name'] = $f;
	$tmp['type'] = filetype($full_path);
	$tmp['mtime'] = filemtime($full_path);
	$tmp['write'] = is_writable($full_path);
	$tmp['read'] = is_readable($full_path);

	// only log directories and regular files
	if($tmp['type'] !== 'dir' && $tmp['type'] !== 'file')
	{
		return false;
	}

	if($tmp['type'] == 'file')
	{
		$tmp['size'] = filesize($full_path);
	}

	return $tmp;
}//end fileInformation

/**
 * Chmod a file.
 */
function rf_chmod($args)
{
	$args = unserialize($args);

	$path = $args['path'];
	$mode = $args['mode'];

	try
	{
		if(!is_writable($path))
		{
			throw new RFUtilException(RFUtilException::UNWRITABLE_PATH);
		}

		$owner = fileowner($path);
		$self = posix_getuid();

		// can write, not owner owner
		if($owner != $self)
		{
			$t = tempnam('/tmp', 'rf');
			$result = copy($path, $t);
			if(!$result)
			{
				throw new RFUtilException(RFUtilException::CHMOD_COPY_FAILED);
			}

			$result = unlink($path);
			if(!$result)
			{
				throw new RFUtilException(RFUtilException::CHMOD_UNLINK_FAILED);
			}

			$result = rename($t, $path);
			if(!$result)
			{
				throw new RFUtilException(RFUtilException::CHMOD_RENAME_FAILED);
			}

			chgrp($path, 'ousers');
		}
	}
	catch(RFUtilException $e)
	{
		return $e->getCode();
	}

	$result = chmod($path, $mode);

	return $result;
}//end rf_chmod

/**
 * Return the file's type.
 */
function rf_type($path)
{
	$type = filetype($path);

	if($type != 'dir' && $type != 'file')
	{
		$type = null;
	}

	return $type;
}

/**
 * Cat a file.
 *
 * @return      int status code indicating success. 0 = success; 1 = access denied; 2 = not a regular file; 3 = unknown error from readfile()
 */
function rf_cat($path)
{
	if(!file_exists($path))
	{
		return RFUtilException::FILE_NOT_FOUND;
	}

	if(!is_readable($path))
	{
		return RFUtilException::UNREADABLE_PATH;
	}

	// calling function should check for the file first. double-check here
	if(!is_file($path))
	{
		return RFUtilException::BAD_FILE_TYPE;
	}

	$result = readfile($path);

	if($result === false)
	{
		return RFUtilException::READFILE_ERROR;
	}

	return 0;
}//end rf_cat

/**
 * Log to a file.
 */
function rfutil_log($msg)
{
	file_put_contents('/tmp/rfutil.log', $msg . "\n", FILE_APPEND);
}//rfutil_log

/**
 * Get a directory listing, or the listing for a single file.
 *
 * @param			string $path the target's path
 * @return		array|int array of files, or int indicating lack of success (see RFUtilException for possible values)
 */
function rf_ls($path)
{
	$data = array(); // return data array
	$wildcard = false;

	if(strpos($path, '*') !== false)
	{
		$wildcard = basename($path);
		$path = dirname($path) . '/';

		// was glob inside path?
		if(strpos($path, '*') !== false)
		{
			return RFUtilException::BAD_WILDCARD_LOCATION;
		}

		$wildcard = explode('*', $wildcard);
		foreach($wildcard as &$token)
		{
			$token = preg_quote($token);
		}
		$wildcard = implode('.*', $wildcard);
		$wildcard = sprintf('/^%s$/i', $wildcard);
	}

	if(!file_exists($path))
	{
		return RFUtilException::FILE_NOT_FOUND;
	}

	if(!is_readable($path))
	{
		return RFUtilException::UNREADABLE_PATH;
	}

	// directory
	if(is_dir($path))
	{
		// fail if path doesn't end in a slash (client should redirect)
		if(substr($path, -1) !== '/')
		{
			return RFUtilException::DIR_MISSING_SLASH;
		}

		if($d = opendir($path))
		{
			while(($f = readdir($d)) !== false)
			{
				// wildcard check
				if($wildcard !== false && preg_match($wildcard, $f) == 0)
				{
					continue;
				}

				$tmp = fileInformation($path, $f);
				if($tmp)
				{
					$data[$f] = $tmp;
				}
			}
			closedir($d);
		}
	}

	// plain file
	elseif(is_file($path))
	{
		$f = basename($path);
		$dir = dirname($path) . '/';
		$data[$f] = fileInformation($dir, $f);
	}

	// other (bad request)
	else
	{
		return RFUtilException::BAD_FILE_TYPE;
	}

	return $data;
}//end rf_ls

/**
 * Read a file from stdin and write to a location.
 */
function rf_put($path)
{
	$destination = dirname($path);

	if(!is_writable($destination))
	{
		return RFUtilException::UNWRITABLE_PATH;
	}

	if(is_file($path))
	{
		unlink($path);
	}

	$stdin = fopen('php://stdin', 'r');
	$out = fopen($path, 'w');
	while($line = fgets($stdin, 4096))
	{
		if(!fwrite($out, $line))
		{
			return RFUtilException::FWRITE_FAILED;
		}
	}

	chgrp($path, 'ousers');
	chmod($path, 0664);

	return true;
}//end rf_put

/**
 * Rename a file.
 */
function rf_rename($data)
{
	$data = unserialize($data);

	$result = rename($data['path'] . $data['from'], $data['path'] . $data['to']);

	if($result == false)
	{
		return RFUtilException::RENAME_FAILED;
	}

	return true;
}//end rf_rename

/**
 * Unlinks a file.
 *
 * @param			string $path the regular file to unlink
 * @return		bool true on success, false on failure, null on not found/not a file
 */
function rf_unlink($path)
{
	$result = null;

	if(is_file($path))
	{
		$result = unlink($path);
	}

	return $result;
}//end rf_unlink

class RFUtilException extends PSUException
{
	const UNREADABLE_PATH = 1;
  const BAD_FILE_TYPE = 2;
	const BAD_WILDCARD_LOCATION = 3;
	const READFILE_ERROR = 4;
	const DIR_MISSING_SLASH = 5;
	const FILE_NOT_FOUND = 6;
	const RENAME_FAILED = 7;
	const UNWRITABLE_PATH = 8;
	const CHMOD_COPY_FAILED = 9;
	const CHMOD_RENAME_FAILED = 10;
	const CHMOD_UNLINK_FAILED = 11;
	const FWRITE_FAILED = 12;

	private static $_msgs = array(
		self::UNREADABLE_PATH => 'You do not have read access to the specific path',
		self::BAD_FILE_TYPE => 'Specified path was not a valid type for this operation',
		self::BAD_WILDCARD_LOCATION => 'Wildcards are not allowed in the file path',
		self::READFILE_ERROR => 'Readfile was not able to open the specified path',
		self::DIR_MISSING_SLASH => 'Directory paths must end in a slash',
		self::FILE_NOT_FOUND => 'No file was found at the specified path',
		self::RENAME_FAILED => 'Could not rename file',
		self::UNWRITABLE_PATH => 'No write access to path',
		self::CHMOD_COPY_FAILED => 'chmod() could not copy file',
		self::CHMOD_RENAME_FAILED => 'chmod() could not rename file',
		self::CHMOD_UNLINK_FAILED => 'chmod() could not unlinke file',
		self::FWRITE_FAILED => 'Could not write to the given path'
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
