<?php

namespace PSU\Template;

/**
 * PSUTemplateException
 *
 * An exception class for PSUTemplate.
 *
 * @package Exceptions
*/
class Exception extends \PSUException {
	const NO_BASE_URL = 1;
	const NO_TMP = 2;
	const UNKNOWN_MESSAGE_TYPE = 3;

	private static $_msgs = array(
		self::NO_BASE_URL => 'global $BASE_URL must be provided for automatic template directories',
		self::NO_TMP => 'No temp directory found, please specify with $GLOBALS[\'TEMPORARY_FILES\']',
		self::UNKNOWN_MESSAGE_TYPE => 'Unknown message type',
	);

	/**
	 * __construct
	 *
	 * Wrapper construct so PSUException gets our message array.
	 */
	function __construct($code, $append=null) { 
		parent::__construct($code, $append, self::$_msgs);
	}
}
