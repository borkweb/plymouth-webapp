<?php

require_once('PSUTools.class.php');
require_once('PSUException.class.php');

/**
 * PSUECommerce.class.php
 *
 * Class based code to handle PSU ECommerce 
 *
 * @since       version 1.0.0
 * @author      Nathan Porter <nrporter@plymouth.edu>, Matthew Batchelder <mtbatchelder@plymouth.edu>
 * @copyright   2008, Plymouth State University, ITS
 * @defgroup    psuecommerce PSUEcommerce
 * @{
 */
class PSUECommerce
{
	var $db;
	var $tables=array(
			'eod'=>'ecommerce_eod',
			'parsed_trans'=>'txraccd',
			'bill'=>'tbraccd',
			'transaction'=>'ecommerce_transaction',
			'processor'=>'ecommerce_processor'
		);
		
	var $processors;
	
	/**
	 * __construct 
	 * 
	 * @param string $db
	 */
	function __construct(&$db)
	{
		$this->db = $db;
		$this->processors = $this->getProcessors();
	}

	/**
	 * getProcessors
	 * 
	 * @param mixed  $type
	 */
	function getProcessors($type = null)
	{
		if($type)
		{
			$where = " AND type = '".$type."'";
		}//end if
		
		$data = array();
		
		$sql = "SELECT * FROM {$this->tables['processor']} WHERE 1=1 $where";
		if($results = $this->db->Execute($sql))
		{
			while($row = $results->FetchRow())
			{
				$data[$row['code']] = $row;
			}//end while
		}//end if
		
		return $data;
	}//end getProcessors

	/**
	 * getTransactionReport
	 * 
	 * @param mixed $begin_date
	 * @param mixed $end_date
	 */
	function getTransactionReport($begin_date = null, $end_date = null)
	{
		$begin_date = ($begin_date) ? date('d-M-y', strtotime($begin_date)) : date('d-M-y');
		$end_date = ($end_date) ? date('d-M-y') : date('d-M-y', strtotime($end_date));		
		
		$sql = "SELECT *
							FROM ecommerce_transaction 
						 WHERE (
										(timestamp BETWEEN to_date('{$begin_date}', 'DD-Mon-YY') AND to_date('{$end_date}', 'DD-Mon-YY')) 
										OR 
										timestamp = to_date('{$begin_date}', 'DD-Mon-YY') 
										OR 
										timestamp = to_date('{$end_date}', 'DD-Mon-YY')
									 ) 
							 AND psu_status = 'loaded' 
						 ORDER BY timestamp";
		
		if($results = $GLOBALS['BANNER']->Execute($sql))
		{
			while($row = $results->FetchRow())
			{
				$row['foapal'] = array(
					'fund' => preg_replace('/^([0-9]{4}).*/','\1',$row['userchoice1']),
					'org'  => preg_replace('/^(?:[0-9]{4} *){1}([0-9]{4}).*/','\1',$row['userchoice1']),
					'acct' => preg_replace('/^(?:[0-9]{4} *){2}([0-9]{4}).*/','\1',$row['userchoice1']),
					'prog' => preg_replace('/^(?:[0-9]{4} *){3}([0-9]{4}).*/','\1',$row['userchoice1']),
					'actv' => preg_replace('/^(?:[0-9]{4} *){4}([0-9]{4}).*/','\1',$row['userchoice1']),
					'locn' => preg_replace('/^(?:[0-9]{4} *){5}([0-9]{4}).*/','\1',$row['userchoice1'])
				);
				
				$data[] = $row;
			}//end while
		}//end if
		return $data;
	}//end getTransactionReport
}//end PSUEcommerce

/**
 * Exceptions for PSUECommerce.
 */
class PSUECommerceException extends PSUException
{
	const INSERTING_EOD = 1; // error creating eod record
	const INSERTING_EOD_CLOB = 2; //error inserting CLOB
	const INSERTING_TRANSACTION = 3; //error creating transaction
	const DELETING_PENDING_EOD = 4; //error deleting pending EOD
	const TRANSACTION_STATUS = 5; //error with transaction status setting
	const TXRACCD_INSERT = 6; //error inserting TXRACCD record
	const SPLIT_PAYMENT = 7; //error splitting transaction payment
	const LEGACY_INSERTING_TRANSACTION = 8; //error inserting legacy transaction
	const LEGACY_MISMATCHED_VALUES = 9; //error with inserted values
	const LEGACY_TRANSACTION_STATUS = 10; //error with legacy transaction status update

	private static $_msgs = array(
		self::INSERTING_EOD => 'Error while creating EOD record',
		self::INSERTING_EOD_CLOB => 'Error adding CLOB data to EOD record',
		self::INSERTING_TRANSACTION => 'Error while creating transaction record',
		self::DELETING_PENDING_EOD => 'Error deleting pending EOD',
		self::TRANSACTION_STATUS => 'Error setting transaction status',
		self::TXRACCD_INSERT => 'Error creating TXRACCD record',
		self::SPLIT_PAYMENT => 'Error splitting transaction',
		self::LEGACY_INSERTING_TRANSACTION => 'Error creating legacy TXBEPAY record',
		self::LEGACY_MISMATCHED_VALUES => 'Error: Values inserted did not match the values in ',
		self::LEGACY_TRANSACTION_STATUS => 'Error setting legacy transaction status'
	);

	/**
	 * __construct
	 *
	 * Wrapper construct so PSUException gets our message array.
	 *
	 * @param string $code
	 * @param mixed 
	 */
	function __construct($code, $append=null)
	{
		parent::__construct($code, $append, self::$_msgs);
	}//end __construct
}//end PSUECommerceException

/** @} */
