<?php

require_once('ecommerce/ETrans.class.php');
require_once('PrintUser.class.php');

/**
 * @ingroup psuecommerce
 */
class ETransPay2Print extends Etrans
{
	public function process()
	{
		$print_user = new PrintUser($this->ordernumber);

		if($print_user)
		{
			if($this->prod) 
			{
				$print_user->adjustBalance($this->orderamount / 100);
			}//end if
			$this->psu_status = 'loaded';
			$this->save();
		}//end if
	}//end process

	public function url($user)
	{
		$person = PSUPerson::get($user);
		
		if(!$person->pidm) return false;
	
		$processor = 'IT Pay2Print';
		$server = ($_SERVER['URANUS']) ? 'test' : 'prod';
		
		$params = array(
			'id' => $person->id
		);

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
		
		$this->setURLParam('redirectUrl', $this->base_url . '/receipt.html');
		$this->setURLParam('retriesAllowed', 5);
		
		$this->setURLParam('redirectUrlParameters', implode(',', $this->_redirect_params));

		return $this->_url($server, $type);
	}//end url
	
	public function __construct($params = false, $prod = false)
	{
		parent::__construct($params, $prod);
	}//end constructor
}//end class ETransPay2Print
