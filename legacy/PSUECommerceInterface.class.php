<?php

/** Base class for PSUECommerceInterface. */
require_once('PSUECommerce.class.php');

/**
 * PSUECommerceInterface.class.php
 *
 * Class based code to handle PSU ECommerce Interface elements
 *
 * @since			version 1.0.0
 * @access			public
 * @author			Nathan Porter <nrporter@plymouth.edu>, Matthew Batchelder <mtbatchelder@plymouth.edu>
 * @copyright  2008, Plymouth State University, ITS
 * @package    ECommerce
 */
class PSUECommerceInterface extends PSUECommerce
{
	
	var $base_url;
	var $shared_secret;
	
	var $legacy = false;

	var $_params;	
	var $_prod_shared_secret = '';
	var $_prod_base_url = 'https://quikpayasp.com/usnh/psc/{TYPE}/payer.do';
	
	var $_test_shared_secret = '';
	var $_test_base_url = 'https://host2.infinet-inc.com/usnh/psc/{TYPE}/payer.do';
/**
  *assignParams
  *
  *sets all the params to the passed in params
  *
  *@params string $params
  */
	function assignParams($params = '')
	{
		if(!is_array($params))
		{
			parse_str($params, $params);
		}//end if

		if($params['id'])
		{
			$params['id'] = preg_replace('/[^0-9a-zA-Z]/','',$params['id']);
			if(!$params['pidm']) $params['pidm'] = $GLOBALS['BannerIDM']->getIdentifier($params['id'],'psu_id','pid');
			
			$info = $this->db->GetRow("SELECT * FROM psu.v_student_account_active WHERE pidm = {$params['pidm']}");
		
			if(!$params['name']) $params['name'] = $GLOBALS['BannerIDM']->getName($params['pidm'], 'l, f m');
			if(!$params['friendly_name']) $params['friendly_name'] = $GLOBALS['BannerIDM']->getName($params['pidm'], 'f m l');
			
			if(!$params['orderNumber']) $params['orderNumber'] = $params['id'];

			unset($params['id']);
		}//end if

		$params['orderName'] = $params['name'];
		
		foreach($params as $key => $param)
		{
			$this->set($key, $param);
		}//end foreach
	}//end assignParams
/**
  *get
  *
  *gets a param out of the array of params and returns it
  *
  *@param string $param
  *@return string
  */
	function get($param)
	{
		return $this->_params[$param];
	}//end get
/**
  *init
  *
  *creats the proper array of params and old params for further manipulation
  *
  */
	function init()
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
	}//end init
/**
  *set
  *
  *sets the particullar param to the passed in data
  *
  *@param string $param
  *@param mixed $data
  */	
	function set($param,$data = false)
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
	}//end set
/**
  *url
  *
  *returs a complete url take from the params
  *
  *@param string $set_params
  *@return string
  */	
	function url($set_params = '')
	{
		if(is_array($set_params))
		{
			$this->assignParams($set_params);
		}//end if
	
		$this->set('timestamp', time().'000');
		
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
	}//end url

	/**
   * getAdmissionUGAppURL
   *
   * returns the admission application url for undergrads
   *
   * @param mixed $pidm
   * @return string
   */	
	function getAdmissionUGAppURL($pidm)
	{
		$params = array();
		$params['id'] = $GLOBALS['BannerIDM']->getIdentifier($pidm, 'pid', 'psu_id');
		$params['amount'] = 4000;
		$params['orderType'] = 'Admission UG App';
		$params['amountDue'] = $params['amount'];
		$params['orderDescription'] = $params['orderType'];
		
		return $this->url($params);
	}//end getAdmissionUGAppURL

	/**
   * getAdmissionGRAppURL
   *
   * iGrad function. Gets the graduate application url.
   *
   * @param string $psp_user_id
   * @param string $name
   * @param integer $amount
   * @param string $program
   * @return string
   */
	function getAdmissionGRAppURL($appid, $name, $amount, $program = '')
	{
		$psp_user_id = PSU::db('psp')->GetOne('SELECT psp_user_id FROM app_2008_app WHERE app_id = :appid', array('appid' => $appid));

		if( !$psp_user_id )
		{
			throw new Exception('Could not find the psp_user_id for that appid');
		}

		$params = array(
			'orderNumber' => $psp_user_id,
			'amount' => $amount,
			'amountDue' => $amount,
			'orderType' => 'Admission GR App',
			'orderDescription' => 'Graduate Admission App Fee',
			'name' => $name,
			'userChoice2' => $program,
			'userChoice3' => $appid
		);

		return $this->url($params, true);
	}//end getAdmissionGRAppURL
/**
  *__construct
  *
  *contructs the information required for this class to interact with the db and other informational servers
  *
  *@param string $db
  *@param mixed $prod
  *@param string $type
  */
	function __construct(&$db, $prod = false, $type = 'commerce_manager')
	{
		parent::__construct($db);			

		if($prod)
		{
			$this->shared_secret = $this->db->GetOne("SELECT gxvsecr_code FROM gxvsecr WHERE gxvsecr_status_ind IS NULL");
			$this->base_url = $this->_prod_base_url;
		}//end if
		else
		{
			$this->shared_secret = $this->db->GetOne("SELECT gxvsecr_code FROM gxvsecr WHERE gxvsecr_status_ind = 'D'");
			$this->base_url = $this->_test_base_url;
		}//end else
		
		$this->base_url = str_replace('{TYPE}', $type, $this->base_url);
		
		$this->init();
	}//end constructor
}//end class PSUECommerceInterface
