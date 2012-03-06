<?php

require_once('ecommerce/ETrans.class.php');
require_once('PSUPerson.class.php');

/**
 * @ingroup psuecommerce
 */
class ETransUGDeposit extends ETrans
{
	public function process()
	{
		$success = false;
		
		if($this->psu_status == 'eod')
		{
			PSU::db('banner')->StartTrans();
			$total = $this->totalamount / 100;
			
			if(in_array($total, array(90, 200, 290)))
			{
				$person = PSUPerson::get($this->ordernumber);
				
				if($person->pidm)
				{
					// default flag in the database is "N" (not paid)
					if($this->status_flag == 'success')
					{
						$payments = array();
						
						if($total == 290)
						{
							$payments[] = 'room';
							$payments[] = 'tuition';
						}//end if
						elseif($total == 200)
						{
							$payments[] = 'tuition';
						}//end elseif
						else
						{
							$payments[] = 'room';
						}//end else
						
						$rate_code = PSU::db('banner')->GetOne("SELECT rate_code FROM v_student_account_active WHERE pidm = " . $person->pidm);
						
						$residency = (in_array(substr($rate_code, 0, 2), array('NR', 'EN', 'BN'))) ? 'non-resident' : 'resident';
						
						$remaining = $total;

						$today = strtotime(date('Y-m-d'));

						foreach($payments as $payment)
						{
							// determine which userchoice fields to pull expiration and release dates from
							if(substr($this->userchoice2, -2) == '10') {
								// fall.  pull from 3 & 4
								$release_check = $this->userchoice3;
								$expiration_check = $this->userchoice4;
							} else {
								// not fall.  pull from 5 & 6
								$release_check = $this->userchoice5;
								$expiration_check = $this->userchoice6;
							}

							// convert dates to timestamps to attempt and catch oddly formatted dates
							$release_check = strtotime( $release_check );
							$expiration_check = strtotime( $expiration_check );

							// if today is greater than one of these dates, set the release/expiration date to today + 2 or +5
							$release_date = ($release_check < $today ? strtotime('+2 days') : $release_check);
							$expiration_date = ($expiration_check < $today ? strtotime('+5 days') : $expiration_check);
							
							// convert timestamps to oracle friendly dates
							$release_date = strtoupper( date('d-M-Y', $release_date) );
							$expiration_date = strtoupper( date('d-M-Y', $expiration_date) );
							
							$sql = "DECLARE 
							          v_tran_number_out tbrdepo.tbrdepo_tran_number%TYPE; 
							          v_rowid_out VARCHAR2(2000); 
							        BEGIN 
							          tb_deposit.p_create(
							          	p_tran_number_out => :v_tran_number_out,
							          	p_rowid_out => :v_rowid_out,
							            p_pidm => ".$person->pidm.",
							            p_term_code => '".$this->userchoice2."',
							            p_detail_code_payment => 'IQRE',
							            p_data_origin => 'Banner',
							            p_document_number => '".$this->transactionid."',
							            p_auto_release_ind => 'Y',
							            p_release_date => to_date('" . $release_date . "', 'DD-MON-RRRR'),
							            p_expiration_date => to_date('" . $expiration_date . "', 'DD-MON-RRRR'),
							            p_entry_date => sysdate,
							            p_effective_date => sysdate,
							            p_user => 'NELNET',";
							
							if($payment == 'room')
							{
								if( substr( $this->userchoice2, -2 ) == '10' ) {
									$bind['detail_code_deposit'] = 'IZRM';
								} else {
									$bind['detail_code_deposit'] = 'IZRO';
								}//end else
								$sql .= "p_amount => 90, p_detail_code_deposit => '".$bind['detail_code_deposit']."'";
							}//end if
							elseif($payment == 'tuition')
							{
								if( substr( $this->userchoice2, -2 ) == '10' ) {
									$bind['detail_code_deposit'] = 'IZR';
								} else {
									$bind['detail_code_deposit'] = 'IZRS';
								}//end else

								$sql .= "p_amount => 200, p_detail_code_deposit => '".$bind['detail_code_deposit']."'";
							}//end elseif
							
							$sql .= "); END;";

							$stmt = PSU::db('banner')->PrepareSP($sql);
							PSU::db('banner')->OutParameter($stmt, $tran_number, 'v_tran_number_out');
							PSU::db('banner')->OutParameter($stmt, $row_id, 'v_rowid_out');
							PSU::db('banner')->Execute($stmt);
						}//end foreach						
					}//end if

					$this->psu_status = 'loaded';
					$this->save();
					
					return PSU::db('banner')->CompleteTrans() ? ($this->totalamount/100) : false;
				}//end if
			}//end if
		}//end if
		PSU::db('banner')->CompleteTrans(false);
		return false;
	}//end process
	
	public function url($user)
	{
		$person = PSUPerson::get($user);
		
		if(!$person->pidm) throw new ECommerceException(ECommerceException::INVALID_PIDM);
		
		$processor = 'UG Tuition/Housing Deposit';
		$server = ($_SERVER['URANUS']) ? 'test' : 'prod';
		$term_code_entry = PSU::db('banner')->GetOne("SELECT term_code_entry FROM v_ug_app WHERE pidm = :pidm", array('pidm' => $person->pidm));
		
		if($person->isActiveStudent() || $term_code_entry)
		{	
			$this->setURLParam('userChoice2', PSU::nvl($person->student->ug->term_code_admit, $term_code_entry, \PSU\Student::getCurrentTerm('UG')));
			$this->setURLParam('orderType', $processor);
			$this->setURLParam('orderNumber', $person->id);
			$this->setURLParam('orderName', $person->formatName('l, f m'));
			$this->setURLParam('orderDescription', $processor);

			return $this->_url($server);
		}//end if
		else
		{
			throw new ECommerceException(ECommerceException::INVALID_STUDENT);
		}//end else
	}//end url

	public function __construct($params = false, $prod = false)
	{
		parent::__construct($params, $prod);
	}//end __construct
}//end class ETransUGDeposit
