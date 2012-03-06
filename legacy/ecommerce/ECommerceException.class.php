<?php 

/**
 * @ingroup psuecommerce
 */
class ECommerceException extends PSUException
{
	const INVALID_TRANSACTION_ID = 1;
	const INVALID_PIDM = 2;
	const INVALID_STUDENT = 3;

	private static $_msgs = array(
		self::INVALID_TRANSACTION_ID => 'Invalid Transaction ID',
		self::INVALID_PIDM => 'The user specified could not be found',
		self::INVALID_STUDENT => 'The user specified is not an active student'
	);

	/**
	 * Wrapper construct so PSUException gets our message array.
	 */
	function __construct($code, $append=null)
	{
		parent::__construct($code, $append, self::$_msgs);
	}
}//end class ECommerceException
