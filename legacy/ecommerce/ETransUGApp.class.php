<?php

require_once('ecommerce/ETrans.class.php');
require_once('PSUPerson.class.php');

/**
 * @ingroup psuecommerce
 */
class ETransUGApp extends ETrans
{
	public function process()
	{
		$success = false;
		
		if($this->psu_status == 'eod')
		{
			PSU::db('banner')->StartTrans();

			$person = PSUPerson::get($this->ordernumber);
			
			if($person->pidm)
			{
				if($this->status_flag == 'success')
				{
					$appl_no = PSU::db('banner')->GetOne("SELECT appl_no FROM psu.v_ug_app WHERE pidm = ".$person->pidm);
					if($appl_no)
					{
						$sql = "UPDATE sarchkl SET sarchkl_receive_date = sysdate WHERE sarchkl_pidm = ".$person->pidm." AND sarchkl_appl_no = ".$appl_no . " AND sarchkl_admr_code = 'APFE'";
						PSU::db('banner')->Execute($sql);
					}//end if
				}//end if
				
				$this->psu_status = 'loaded';
				$this->save();
				
				return PSU::db('banner')->CompleteTrans() ? ($this->totalamount/100) : false;
			}//end if
		}//end if
		PSU::db('banner')->CompleteTrans(false);
		return false;
	}//end process

	public function __construct($params = false, $prod = false)
	{
		parent::__construct($params, $prod);
	}//end __construct
}//end class ETransUGApp
