<?php
/**
 * PSUSecurity.class.php
 *
 * === Modification History ===<br/>
 * 1.0.0  15-nov-2007  [mtb]  original<br/>
 *
 * @package 		security
 */

/**
 * PSUSecurity.class.php
 *
 * PSU Security API
 *
 * @version		1.0.0
 * @module		security.class.php
 * @author		Matthew Batchelder <mtbatchelder@plymouth.edu>
 * @copyright 2007, Plymouth State University, ITS
 */ 

require_once('PSUTools.class.php');

class PSUSecurity
{
	/**
	 * password_decode
	 *
	 * Decodes single or array of passwords
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		mixed $data data element
	 * @return  	mixed
	 */
	function password_decode($data)
	{
		//PSUTools::logOldCode('/includes/PSUSecurity.class.php->password_decode');
		if(is_array($data))
		{
			for($i = 0;$i<sizeof($data);$i++)
			{
				$data[$i] = self::_decode_string($data[$i]);
			}//end for
		}//end if
		else
		{
			$data = self::_decode_string($data);
		}//end else
		
		return $data;
	}//end password_decode

	/**
	 * password_encode
	 *
	 * Encodes passwords
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		mixed $data data element
	 * @return  	mixed
	 */
	function password_encode($data)
	{
		//PSUTools::logOldCode('/includes/PSUSecurity.class.php->password_encode');
		if(is_array($data))
		{
			for($i = 0;$i<sizeof($data);$i++)
			{
				$data[$i] = self::_encode_string($data[$i]);
			}//end for
		}//end if
		else
		{
			$data = self::_encode_string($data);
		}//end else
		
		return $data;
	}//end password_encode

	/**
	 * Return a boolean value indicating if the user's password is expired.
	 *
	 * @param       string $username
	 * @return      boolean
	 */
	function passwordExpired($username)
	{
		//PSUTools::logOldCode('/includes/PSUSecurity.class.php->passwordExpired');
		// ripped from the go sidebar
		if(!isset($GLOBALS['AD']))
		{
			require_once('adldap/adLDAP.php');
		}
		
		$groups = $GLOBALS['AD']->user_groups($username);
		if(!in_array('faculty', $groups) && !in_array('staff', $groups))
		{
			return false;
		}
	
		$ad_info = $GLOBALS['AD']->user_info($username, array('pwdlastset'));
	
		// 116444736000000000 = 10000000 * 60 * 60 * 24 * 365 * 369 + 89 leap days huh.
		$ad_stamp = round(($ad_info[0]['pwdlastset'][0]-116444736000000000)/10000000);
		$change_date = date('F j, Y',$ad_stamp);
	
		$seconds = time()-$ad_stamp;
		$days = round(($seconds)/60/60/24);
	
		if($days >= 180)
		{
			return true;
		}
	
		return false;
	}//end isPasswordExpired

	/**
	 * decrypts a string
	 *
	 * @param $string \b string to decrypt
	 */
	static function string_decrypt($string)
	{
		require_once 'encryption.class.php';
		include 'other/pw_key.php';

		$crypt = new encryption_class;
		return $crypt->decrypt($_PSU_KEY['key'], $string);
	}//end string_decrypt

	/**
	 * encrypts a string
	 *
	 * @param $string \b string to encrypt
	 */
	static function string_encrypt($string)
	{
		require_once 'encryption.class.php';
		include 'other/pw_key.php';

		$crypt = new encryption_class;
		return $crypt->encrypt($_PSU_KEY['key'], $string);
	}//end string_encrypt

	/**
	 * __construct
	 *
	 * Constructor
	 *
	 * @since		version 1.0.0
	 * @access		public
	 */
	function __construct()
	{
		//PSUTools::logOldCode('/includes/PSUSecurity.class.php');
	}//end constructor

	/**
	 * _decode_string
	 *
	 * Decodes string
	 *
	 * @since		version 1.0.0
	 * @access		protected
	 * @param  		string $data data element
	 * @return  	string
	 */
	protected function _decode_string($data)
	{
		//PSUTools::logOldCode('/includes/PSUSecurity.class.php->_decode_string');
		return base64_decode($data);
	}//end _decode_string

	/**
	 * _encode_string
	 *
	 * Encodes string
	 *
	 * @since		version 1.0.0
	 * @access		protected
	 * @param  		string $data data element
	 * @return  	string
	 */
	protected function _encode_string($data)
	{
		//PSUTools::logOldCode('/includes/PSUSecurity.class.php->_encode_string');
		return base64_encode($data);
	}//end _encode_string	
}//end class PSUSecurity
 
?>
