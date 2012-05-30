<?php

/**
 * SCPlib.class.php
 */

class SCPlib
{
	private $_server;
	private $_scp_cmd;
	private $_ssh_cmd;
	private $_config;
	private $_rfutil;

	/**
	 * SCPlib constructor. Setup main variables.
	 *
	 * @param	$server the server we will connect to
	 * @param	$config optional. path to an ssh_config file
	 */
	function SCPlib($server, $config=null)
	{
		if(!ctype_alpha($server))
		{
			trigger_error('specified server name has non-alpha characters', E_USER_ERROR);
			return NULL;
		}

		// pre-run error checks
		$old_umask = umask(077);
		$www_user = posix_getuid();
		$info = posix_getpwuid($www_user);
		$home_ssh = $info['dir'].'/.ssh';
		$known_hosts = $home_ssh . '/known_hosts';
		if(!is_readable($known_hosts))
		{
			throw new SCPException(SCPException::KNOWN_HOSTS, $known_hosts);
		}

		$this->_server = $server;
		$this->_scp_cmd = '/usr/bin/scp -o "BatchMode yes"';
		$this->_ssh_cmd = '/usr/bin/ssh -o "BatchMode yes"';

		if($config !== null)
		{
			if(!is_file($config))
			{
				throw new SCPException(SCPException::CONFIG_NOT_FILE, $config);
			}

			if(!is_readable($config))
			{
				throw new SCPException(SCPException::CONFIG_NOT_READABLE, $config);
			}

			$this->_config = $config;
			$this->_scp_cmd .= ' -F ' . escapeshellarg($config);
			$this->_ssh_cmd .= ' -F ' . escapeshellarg($config);
		}

		$this->_rfutil = "~/rfutil";
	}

	function type($path)
	{
		$cmd = sprintf("%s %s '%s type -e %s' 2>&1", $this->_ssh_cmd, $this->_server, $this->_rfutil, base64_encode($path));
		exec($cmd, $output, $exit_code);

		if($exit_code > 0)
		{
			$output = implode("\n", $output);
			throw new SCPException(SCPException::SSH_CMD_ERROR, sprintf('%s (%d)', $output, $exit_code));
		}

		return unserialize($output[0]);
	}

	function __destruct()
	{
	}

	function ls($path='', $sort='name', $order='asc')
	{
		$cmd = sprintf("%s %s '%s ls -e %s' 2>&1", $this->_ssh_cmd, $this->_server, $this->_rfutil, base64_encode($path));
		exec($cmd, $output, $exit_code);

		if($exit_code > 0)
		{
			$output = implode("\n", $output);
			throw new SCPException(SCPException::SSH_CMD_ERROR, sprintf('%s (%d)', $output, $exit_code));
		}

		$return = array(); // resulting array of files

		$ls = unserialize($output[0]);

		if($ls === false)
		{
			throw new SCPException(SCPException::ACCESS_DENIED);
		}

		if(!is_array($ls))
		{
			return $ls;
		}

		$code = 'return (';

		if($order != 'asc')
		{
			$code .= '-1*';
		}

		switch($sort)
		{
			case 'mtime':
			case 'size':
				$code .= '($a["' . $sort . '"] - $b["' . $sort . '"])';
				break;
			default:
				$code .= 'strnatcasecmp($a["name"], $b["name"])';
				break;
		}
		$code .= ');';

		$compare = create_function('$a,$b', $code);
		usort($ls, $compare);

		return $ls;
	}

	/**
	 * Put a file on the server in a specified directory.
	 */
	function put($file, $dest_path = '')
	{
		$dest_path = base64_encode($dest_path);

		$cmd = sprintf("%s %s '%s put -e %s' < %s 2>&1", $this->_ssh_cmd, $this->_server,
			$this->_rfutil, escapeshellarg($dest_path), escapeshellarg($file));

		exec($cmd, $output, $exit_code);

		if($exit_code > 0)
		{
			$output = implode("\n", $output);
			throw new SCPException(SCPException::SSH_CMD_ERROR, sprintf('%s [%s] (%d)', $output, $cmd, $exit_code));
		}

		$output = unserialize(implode("\n", $output));
		if(is_int($output))
		{
			throw new RFUtilException($output);
		}

		return true;
	}//end put

	/**
	 * Chmod a file on the remote host.
	 */
	function chmod($path, $mode)
	{
		$data = array('path' => $path, 'mode' => $mode);

		$cmd = sprintf("%s %s '%s chmod -e %s' 2>&1", $this->_ssh_cmd, $this->_server,
			$this->_rfutil, base64_encode(serialize($data)));

		exec($cmd, $output, $exit_code);

		$output = implode("\n", $output);

		if($exit_code > 0)
		{
			throw new SCPException(SCPException::SSH_CMD_ERROR, sprintf('%s [%s] (%d)', $output, $cmd, $exit_code));
		}

		$output = unserialize($output);

		if($output === true)
		{
			return true;
		}

		// raise the exception that rfutil tried to raise
		throw new RFUtilException($output);
	}//end chmod

	/**
	 * Rename a file on the remote host.
	 */
	function rename($path, $from, $to)
	{
		$data = array('path' => $path, 'from' => $from, 'to' => $to);

		$cmd = sprintf("%s %s '%s rename -e %s' 2>&1", $this->_ssh_cmd, $this->_server,
			$this->_rfutil, base64_encode(serialize($data)));

		exec($cmd, $output, $exit_code);

		$output = implode("\n", $output);

		if($exit_code > 0)
		{
			throw new SCPException(SCPException::SSH_CMD_ERROR, sprintf('%s [%s] (%d)', $output, $cmd, $exit_code));
		}

		$output = unserialize($output);

		if($output === true)
		{
			return true;
		}

		// raise the exception that rfutil tried to raise
		throw new RFUtilException($output);
	}

	/**
	 * Set a custom rfutil app.
	 *
	 * @param    string $path path to rfutil
	 */
	function set_rfutil($path)
	{
		$this->_rfutil = $path;
	}//end set_rfutil

	/**
	 * Fetch a remote file and return it directly to the client.
	 *
	 * @param	string $remote_file the path to the remote file
	 */
	function stream($remote_file, $view = true)
	{
		$basename = basename($remote_file);
		
		$file = $this->ls($remote_file);	
		if(count($file) == 0)
		{
			// FIXME: file did not exist
			return;
		}

		// $ ssh host 'cat filename'
		$cmd = sprintf("%s %s '%s cat -e %s'", $this->_ssh_cmd, $this->_server, $this->_rfutil, base64_encode($remote_file));

		// prep the headers and output the file
		if($view)
		{
			header('Content-type: text/plain');
		}
		else
		{
			// not viewing, downloading
			header('Content-Transfer-Encoding: binary');
			header('Content-type: application/octet-stream'); // FIXME: what's the content type?
			header('Content-Disposition: attachment; filename="' . addslashes($basename) . '"');

			// IE7 will fail to download files over HTTPS unless caching is enabled
			header('Pragma: cache');
			header('Cache-control: cache');
		}

		header('Content-length: ' . $file[0]['size']);
		passthru($cmd, $exit_code);

		return $exit_code;
	}

	/**
	 * Fetch a remote file into the /tmp directory.
	 *
	 * @param     string $remote_file the path to the remote file.
	 * @return    string|boolean the path to the fetched file, or false if there was an error
	 */
	function get($remote_file)
	{
		umask(077);
		$local_file = tempnam('/tmp', 'rf');
		$cmd = sprintf('%s %s:%s %s', $this->_scp_cmd, $this->_server, escapeshellarg($remote_file),
			$local_file);
		exec($cmd, $output, $exit_code);

		if($exit_code == 0)
		{
			return $local_file;
		}

		return false;
	}//end get

	/**
	 * Unlink a file on the server.
	 */
	function unlink($remote_file)
	{
		$remote_file = base64_encode($remote_file);
		$cmd = sprintf("%s %s '%s rm -e %s' 2>&1", $this->_ssh_cmd, $this->_server, $this->_rfutil, $remote_file);
		exec($cmd, $output, $exit_code);

		if($exit_code > 0)
		{
			$output = implode("\n", $output);
			throw new SCPException(SCPException::SCP_CMD_ERROR, sprintf('%s (%d)', $output, $exit_code));
		}

		return unserialize($output[0]);
	}//end unlink
}

require_once('PSUException.class.php');
class SCPException extends PSUException
{
	const KNOWN_HOSTS = 1;
	const SCP_CMD_ERROR = 2;
	const SSH_CMD_ERROR = 3;
	const CONFIG_NOT_FILE = 4;
	const CONFIG_NOT_READABLE = 5;
	const PUT_WRITE_FAILED = 6; 
	const PUT_FILE_EXISTS = 7;
	const DIR_NOT_FOUND = 8;
	const ACCESS_DENIED = 9;

	private static $_msgs = array(
		self::KNOWN_HOSTS => 'Error reading authorized keys file',
		self::SCP_CMD_ERROR => 'SCP encountered a general error',
		self::SSH_CMD_ERROR => 'SSH encountered a general error',
		self::CONFIG_NOT_FILE => 'Specified config is not a file',
		self::CONFIG_NOT_READABLE => 'Specified config file is not readable',
		self::PUT_WRITE_FAILED => 'Upload failed: could not write to file',
		self::PUT_FILE_EXISTS => 'Upload failed: file exists',
		self::DIR_NOT_FOUND => 'Directory not found',
		self::ACCESS_DENIED => 'You do not have access to the specified path',
	);

	/**
	 * Wrapper construct so PSUException gets our message array.
	 */
	function __construct($code=-1, $append=null)
	{
		parent::__construct($code, $append, self::$_msgs);
	}
}

// vim:noet:ts=2:sw=2:
?>
