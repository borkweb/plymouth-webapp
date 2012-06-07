<?php

require_once('ecommerce/ETrans.class.php');

/**
 * @ingroup psuecommerce
 */
class ETransFlexCash extends Etrans {
	public function __construct($params = false, $prod = false) {
		parent::__construct($params, $prod);
	}//end constructor

	public function process() {
		$person = \PSUPerson::get( $this->ordernumber );
		$this->ordernumber = $person->id;
		parent::process();
	}//end process

	public function url($user) {
		$person = PSUPerson::get($user);
		
		if(!$person->pidm) return false;
	
		$processor = 'Res Life FlexCash';
		$server = ($_SERVER['URANUS']) ? 'test' : 'prod';
		
		$sql = "SELECT 1 
		          FROM spbcard 
		         WHERE spbcard_pidm=".preg_replace('/[^0-9]/','',$person->pidm)." 
		           AND (spbcard_student_status = 'AS' 
		                OR 
		                spbcard_employee_status = 'A'
		                OR 
		                spbcard_employee_status IS NULL
		               )";
		
		if($can_flex_cash = $GLOBALS['BANNER']->GetOne($sql)) {		
			$this->setURLParam('orderType', $processor);
			$this->setURLParam('orderNumber', $person->username);
			$this->setURLParam('orderName', $person->formatName('l, f m'));
			$this->setURLParam('orderDescription', $processor);
			
			return $this->_url($server);
		} else {
			return false;
		}//end else
	}//end url
}//end class ETransFlexCash
