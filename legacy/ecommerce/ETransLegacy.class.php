<?php

require_once('BannerObject.class.php');
require_once('PSUPerson.class.php');
require_once('ECommerceException.class.php');

/**
 * @author Matthew Batchelder (mtbatchelder@plymouth.edu)
 * @date   May, 2009
 * @brief  Basic ECommerce transaction handler class.
 * @ingroup psuecommerce
 *
 * Part of the APIs to work with Nelnet, the ETrans class is a
 * base class containing default functions used in both: entry 
 * point access as well as transaction processing.
 */
class ETransLegacy extends BannerObject
{
	public $tables = array(
		'transaction' => 'txbepay'
	);
	public $data = array();

	public $base_url;
	protected $shared_secret;
	
	public $legacy = false;
	public $_params;	
	protected $_prod_shared_secret = '';
	public $_prod_base_url = 'https://quikpayasp.com/usnh/psc/{TYPE}/payer.do';
	
	protected $_test_shared_secret = '';
	public $_test_base_url = 'https://host2.infinet-inc.com/usnh/psc/{TYPE}/payer.do';

/**
  * @brief sets all the params to the passed in params
  *
  * @param mixed $params String or Array of URL parameters
  *
  * Example Code ($params as string):
  * @code
  * $etrans->assignURLParams('name=Bork');
  * @endcode
  *
  * Example Code ($params as array):
  * @code
  * $etrans->assignURLParams(array('name', 'Bork'));
  * @endcode
  */
	public function assignURLParams($params = '')
	{
		if(!is_array($params))
		{
			parse_str($params, $params);
		}//end if

		$params['orderName'] = $params['name'];
		
		foreach($params as $key => $param)
		{
			$this->setURLParam($key, $param);
		}//end foreach
	}//end assignURLParams

/**
  * @brief initializes the default URL
  *
  * @param string $processor ECommerce Processor code
  * @param mixed $params Array or string list of parameters
  * @param string $server Nelnet server (test or prod)
  * @param string $type Type of link (commerce_manager or legacy)
  */
	public function url($processor, $params = false, $server = 'test', $type = 'commerce_manager')
	{
		if(!is_array($params))
		{
			parse_str($params, $params);
		}//end if
		
		$person = PSUPerson::get($params['id']);
		
		$this->setURLParam('orderType', $processor);
		$this->setURLParam('amountDue', $params['amount']);
		$this->setURLParam('orderNumber', $params['id']);
		$this->setURLParam('orderName', $person->formatName('l, f m'));
		$this->setURLParam('orderDescription', $processor);
		
		return $this->_url($server, $type);
	}//end url

/**
  * @brief deletes the transaction from the database
  */	
	public function delete()
	{
		if(!$this->txbepay_trans_id) throw new ECommerceException(ECommerceException::INVALID_TRANSACTION_ID, $this->txbepay_trans_id);
		
		$sql = "DELETE FROM {$this->tables['transaction']} WHERE txbepay_trans_id = " . $this->txbepay_trans_id;
		return PSU::db('banner')->Execute($sql);
	}//end delete

 /**
  * @brief gets a param out of the array of params and returns it
  *
  * @param string $param
  * @return string
  */
	public function getURLParam($param)
	{
		return $this->_params[$param];
	}//end getURLParam

/**
  * @brief initializes the url parameter arrays
  *
  */
	public function initURLParams()
	{
		$this->_params = array(
			'orderType' =>'',
			'orderNumber' =>'',
			'orderName' =>'',
			'orderDescription' =>'',
			'amount' =>'',
			'fee' =>'',
			'amountDue' =>'',
			'currentAmountDue' =>'',
			'balance' =>'',
			'currentBalance' =>'',
			'dueDate' =>'',
			'userChoice1' =>'',
			'userChoice2' =>'',
			'userChoice3' =>'',
			'userChoice4' =>'',
			'userChoice5' =>'',
			'userChoice6' =>'',
			'userChoice7' =>'',
			'userChoice8' =>'',
			'userChoice9' =>'',
			'userChoice10' =>'',
			'paymentMethod' =>'',
			'streetOne' =>'',
			'streetTwo' =>'',
			'city' =>'',
			'state' =>'',
			'zip' =>'',
			'country' =>'',
			'daytimePhone' =>'',
			'eveningPhone' =>'',
			'email' =>'',
			'redirectUrl' =>'',
			'redirectUrlParameters' =>'',
			'retriesAllowed' =>'',
			'contentEmbedded' =>'',
			'timestamp' =>''
		);

		$this->_legacy_params = array(
			'userId' => '',
			'amountDue' => '',
			'fullName' => '',
			'email' =>'',
			'paymentMethod' => '',
			'timestamp' =>''
		);
	}//end initURLParams

/**
  * @brief loads the given transaction from the database
  *
  * @param int $id The transactionid of transaction
  */		
	public function load($id)
	{
		try
		{
			$sql = "SELECT * 
								FROM {$this->tables['transaction']}
							 WHERE txbepay_trans_id = " . ((int) $id);
			
			if($data = PSU::db('banner')->GetRow($sql))
			{
				$this->parse($data);
			}//end if
			else
			{
				throw new ECommerceException(ECommerceException::INVALID_TRANSACTION_ID, $id);
			}//end else
		}//end try
		catch(ECommerceException $e)
		{
			die($e->getMessage());
		}//end catch
	}//end load

/**
  * @brief parses an array into this object's data array
  *
  * @param array $data Transaction data
  */	
	public function parse($data)
	{
		if(!is_array($data))
		{
			parse_str($data, $data);
		}//end if
		
		foreach($data as $key => $value)
		{
			$key = strtolower($key);
			$this->$key = $value;	
		}//end foreach
		
		$this->updateStatusFlag();
	}//end parse
	
	public function process()
	{
		$this->load_status = 'loaded';
		$this->save();
	}//end process

	/**
	 * @brief Updates the status_flag based on txbepay_trans_type and txbepay_trans_status
	 *
	 * Accept a payment transaction type and status from Nelnet, and translate to a single-character flag:
	 * - success -- successful payment
	 * - unpaid -- unpaid (never returned, but should be the default in external table)
	 * - unknown -- unknown type/status supplied
	 * - failed -- payment rejected (cc failed, or echeck returned)
	 * - error -- unknown error in credit card processing
	 */
	function updateStatusFlag()
	{
		// default flag in the database is "N" (not paid)
		$this->status_flag = 'unknown'; // unknown value from ecommerce table

		if($this->txbepay_trans_type == 1 || $this->txbepay_trans_type == 2) // credit card
		{
			switch($this->txbepay_trans_status)
			{
				case 1: // Accepted 
					$this->status_flag = 'success'; break; // payment successful
				case 2: // Rejected
					$this->status_flag = 'rejected'; break; // payment rejected
				case 3: // Error
				case 4: // Unknown
					$this->status_flag = 'error'; break; // error
			}//end switch
		}//end if
		elseif($this->txbepay_trans_type == 3) // echeck
		{
			switch($this->txbepay_trans_status)
			{
				case 7: // returned
					$this->status_flag = 'rejected'; break; // payment rejected
				case 5: // accepted
				case 6: // posted
				case 8: // NOC
					$this->status_flag = 'success'; break; // payment successful
			}//end switch
		}//end else
	}//end setStatusFlag

/**
  * @brief Stores the object's transaction data into the database
  */
	public function save()
	{
		if(!$this->txbepay_trans_id) throw new ECommerceException(ECommerceException::INVALID_TRANSACTION_ID, $this->txbepay_trans_id);
	
		$sql = "SELECT 1 FROM {$this->tables['transaction']} WHERE txbepay_trans_id = " . $this->txbepay_trans_id;
		$exists = PSU::db('banner')->GetOne($sql);

		if($exists)
		{
			$sql = "UPDATE {$this->tables['transaction']}
			           SET ";
			           
			foreach($this->columns as $column)
			{
				$col = strtolower($column['column_name']);
				if($col == 'txbepay_trans_id' || $col == 'txbepay_file') continue;
				
				$sql .= $col . " = ";
				if($this->$col == null)
				{
					$sql .= "NULL";
				}//end if
				else
				{
					switch($column['data_type'])
					{
						case 'NUMBER':
							$sql .= $this->$col;
						break;
						case 'DATE':
							$sql .= "to_date('" . strtoupper(date('d-M-Y', strtotime($this->$col))) . "','DD-MON-RRRR')";
						break;
						default:
							$sql .= PSU::db('banner')->qstr($this->$col);
						break;
					}//end switch
				}//end else
				
				$sql .= ", ";
			}//end foreach
			
			$sql = substr($sql, 0, -2) . " ";
			$sql .= "WHERE txbepay_trans_id = {$this->txbepay_trans_id} AND txbepay_file = '{$this->txbepay_file}'";
		}//end if
		else
		{
			$sql = "INSERT INTO {$this->tables['transaction']}(";
			$sql_vals = "";
			foreach($this->columns as $column)
			{
				$col = strtolower($column['column_name']);
				
				$sql .= $col;
				if($this->$col === '')
				{
					$sql_vals .= "NULL";
				}//end if
				else
				{
					switch($column['data_type'])
					{
						case 'NUMBER':
							$sql_vals .= $this->$col;
						break;
						case 'DATE':
							$sql_vals .= "to_date('" . strtoupper(date('d-M-Y', strtotime($this->$col))) . "','DD-MON-RRRR')";
						break;
						default:
							$sql_vals .= PSU::db('banner')->qstr($this->$col);
						break;
					}//end switch
				}//end else
				
				$sql .= ", ";
				$sql_vals .= ", ";
			}//end foreach
			
			$sql = substr($sql, 0, -2) . " ";
			$sql_vals = substr($sql_vals, 0, -2) . " ";
			$sql .= ") VALUES (" . $sql_vals .")";
		}//end else
		
		return PSU::db('banner')->Execute($sql);
	}//end save

 /**
  * @brief sets the URL param to the passed in data
  *
  * @param string $param
  * @param mixed $data
  */	
	function setURLParam($param,$data = false)
	{
		if(is_array($param))
		{
			$this->_params = array_merge($this->_params, $param);
		}//end if
		elseif($data === false)
		{
			parse_str($param,$param);
			$this->_params = array_merge($this->_params, $param);
		}//end if	
		else
		{
			if(isset($this->_params[$param])) $this->_params[$param] = $data;
			if(isset($this->_legacy_params[$param])) $this->_legacy_params[$param] = $data;
		}//end else
	}//end setURLParam

/**
  * @brief constructor
  *
  * @param mixed $params Transaction data OR the transactionid
  */	
	public function __construct($params= false, $prod = false, $type = 'commerce_manager')
	{
		parent::__construct();
		
		$this->base_url = 'https://'.$_SERVER['HTTP_HOST'].'/webapp/ecommerce';
		
		if(is_numeric($params))
		{
			$this->load($params);
		}//end if
		else
		{
			$this->parse($params);
		}//end else	
		
		$sql = "SELECT * FROM all_tab_columns WHERE table_name = '".strtoupper($this->tables['transaction'])."'";
		$this->columns = PSU::db('banner')->GetAll($sql);
		
		$this->initURLParams();
	}//end constructor

 /**
  * @brief returs the ecommerce url take from the params
  *
  * @param string $set_params
  * @return string
  */	
	public function _url($server = 'test', $set_params = '', $type = 'commerce_manager')
	{
		if(is_array($set_params))
		{
			$this->assignURLParams($set_params);
		}//end if

		// load shared secret and setup base url
		if( 'prod' == $server ) {
			$sql = "SELECT gxvsecr_code FROM gxvsecr WHERE gxvsecr_status_ind IS NULL";
		} else {
			$sql = "SELECT gxvsecr_code FROM gxvsecr WHERE gxvsecr_status_ind = 'D'";
		}//end else

		$this->shared_secret = PSU::db('banner')->GetOne($sql);
		$this->base_url = str_replace('{TYPE}', $type, ($server == 'prod') ? $this->_prod_base_url : $this->_test_base_url);

		$this->setURLParam('timestamp', time().'000');
		
		$hash = '';
		$url_params = '?';
		
		$params = ($this->legacy) ? $this->_legacy_params : $this->_params;

		foreach($params as $key => $param)
		{
			if(strlen($param) > 0)
			{
				$hash .= trim($param);
				$url_params .= (($url_params=='?')?'':'&').$key.'='.trim($param);
			}//end if
		}//end foreach

		$url .= $url_params.'&hash='.md5($hash.$this->shared_secret);

		return $this->base_url.$url;
	}//end _url
}//end class ETransLegacy
