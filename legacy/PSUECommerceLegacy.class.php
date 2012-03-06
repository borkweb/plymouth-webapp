<?php

/**
* PSUECommerceLegacy.class.php
*
* Class based code to handle PSU ECommerce 
*
* @since			version 2.0.0
* @access			public
* @author			Nathan Porter <nrporter@plymouth.edu>, Matthew Batchelder <mtbatchelder@plymouth.edu>
* @copyright 2008, Plymouth State University, ITS
* @package    ECommerce
*/

require_once('PSUECommerceTransaction.class.php');

class PSUECommerceLegacy extends PSUECommerceTransaction
{
	var $prod;
	var $old_term;
	
	/**
	* getFeed
	*
	* function to get the feed to be worked with
	*
	* @since			version 1.0.0
	* @access			public
	*/
	function getFeed()
	{
		$sql = "SELECT * FROM ecommerce_pending_eod WHERE file_name like 'psc_%'";
		if($results = $this->db->Execute($sql))
		{
			while($row = $results->FetchRow())
			{
				$this->files[] = array('name' => $row['file_name'], 'content' => $row['contents']);
			}//end while
		}//end if
	}//end getFeed function
	
	/**
	* errorCheck
	*
	* function that checks and assigns errors based on conditions, also deletes files with errors
	* called in processFiles function
	*
	* @since			version 1.0.0
	* @access			public
	*/
	function errorCheck($file)
	{
		$check_value=$this->db->GetOne("SELECT count(*) FROM txbepay WHERE (txbepay_trans_type=1 OR txbepay_trans_type=3) AND txbepay_file='{$file['file_name']}'");

		if($check_value != $file['columns'][1])
		{
		
			$error_num = 1;
			$file['error'] = true;
			
		}//end if
		else
		{
		
			$check_value = $this->db->GetOne("SELECT count(*) FROM txbepay WHERE txbepay_trans_type=2 AND txbepay_file='{$file['file_name']}'");
			
			if($check_value != $file['columns'][2]){
			
				$error_num = 2;
				$file['error'] = true;
				
			}//end if
			else
			{
				
				$check_value = $this->db->GetOne("SELECT sum(txbepay_trans_total) FROM txbepay WHERE txbepay_file='{$file['file_name']}'");
				
				if(!$check_value) $check_value=0;
				
				$check_value=(strlen($check_value)>10)?substr($check_value,-10):$check_value;
				
				if($check_value != $file['columns'][3])
				{
				
					$error_num = 3;
					$file['error'] = true;
					
				}//end if
				else
				{
					
					$check_value = $this->db->GetOne("SELECT sum(txbepay_trans_total) FROM txbepay WHERE (txbepay_trans_type=1 OR txbepay_trans_type=3) AND txbepay_file='{$file['file_name']}'");
											
					if(!$check_value) $check_value=0;
					
					if($check_value != $file['columns'][4]){
					
						$error_num = 4;
						$file['error'] = true;
						
					}//end if
					else
					{
						
						$check_value = $this->db->GetOne("SELECT sum(txbepay_trans_total) FROM txbepay WHERE txbepay_trans_type=2 AND txbepay_file='{$file['file_name']}'");
						
						if(!$check_value) $check_value=0;
						
						if($check_value != $file['columns'][5]){
						
							$error_num = 5;
							$file['error'] = true;
							
						}//end if
					}//end else
				}//end else
			}//end else
		}//end else
		
		if($file['error'])
		{
		
			$this->db->Execute("DELETE FROM txbepay WHERE txbepay_file='{$file['file_name']}'");
			
		}//end if
	}// end errorCheck function
	
	/**
	* processFiles
	*
	* main function to handle the processing of feed files
	* makes calls to errorCheck, decryptFiles, and noErrors functions
	*
	* @since			version 1.0.0
	* @access			public
	*/
	function processFiles()
	{
		$this->handleAlum();
			foreach(($this->files) as $file_data)
			{
			
				//$this->decrypt($file_data);
				
				$file = Array(
					'file_name' => $file_data['name'],
					'lines' => explode("\n",$file_data['content']),
					'columns' => Array(),
					'description' => '',
					'feed_date' => '',
					'error' => false
				);

				for($line = 0; $line < sizeof($file['lines']); $line++)
				{				
					$file['columns'] = explode('|', $file['lines'][$line]);
					
					if($line == 0)
					{					
						//~ the first line contains the date and description
						$file['feed_date'] = $file['columns'][2];
						$file['description'] = $file['columns'][5];						
					}//end if
					elseif($line!=(sizeof($file['lines'])-1))
					{
						//- load the all lines except for the last one which happens to be extraneous data
						$query="INSERT INTO txbepay 
(txbepay_feed_desc,		
txbepay_file,	
txbepay_entry_id,		
txbepay_orig_trans_id,		
txbepay_trans_type,		
txbepay_trans_status,		
txbepay_trans_id,		
txbepay_trans_total,		
txbepay_trans_date,		
txbepay_trans_eff_date,		
txbepay_trans_desc,		
txbepay_trans_result_date,		
txbepay_trans_result_eff_date,		
txbepay_trans_result_code,		
txbepay_trans_result_message,		
txbepay_order_number,		
txbepay_order_type,		
txbepay_order_name,		
txbepay_order_desc,		
txbepay_order_amount,		
txbepay_order_fee,		
txbepay_order_amount_due,		
txbepay_order_due_date,		
txbepay_order_balance,		
txbepay_payer_type,		
txbepay_payer_id,		
txbepay_payer_full_name,		
txbepay_actual_payer_type,		
txbepay_actual_payer_id,		
txbepay_actual_payer_full_name,		
txbepay_holder_name,		
txbepay_street_line_1,		
txbepay_street_line_2,		
txbepay_city,		
txbepay_state,		
txbepay_zip,		
txbepay_country,		
txbepay_daytime_phone,		
txbepay_evening_phone,		
txbepay_email)		
VALUES		
('".$file['description']."',		
'".$file['file_name']."',";
												
												for($c_num = 1; $c_num < 38; $c_num++)
												{
													$query .= $this->db->qstr($file['columns'][$c_num]).",\n";
												}
												$query .= $this->db->qstr(trim(str_replace("\n",'',$file['columns'][38]), ","));
												$query.=")";
												
						if(!$this->db->Execute($query))
						{
							throw new PSUECommerceException(PSUECommerceException::LEGACY_INSERTING_TRANSACTION, '[Transaction #'.$file['columns'][5].'] - '.$this->db->ErrorMsg()."\n");
						}//end if
					}//end elseif
					else
					{
						$this->errorCheck($file);
					}//end else
				}//end for loop
				
				$this->noErrors($file);
			}//end foreach
			
			$query="UPDATE txbepay SET txbepay_pidm=(SELECT spriden_pidm FROM spriden WHERE spriden_id=lpad(txbepay_payer_id,9,'0') AND spriden_change_ind is null) WHERE txbepay_pidm is null and txbepay_payer_id<>'Guest'";
			$this->db->Execute($query);
			
			$query="UPDATE txbepay SET txbepay_pidm=(SELECT spbpers_pidm FROM spbpers WHERE spbpers_ssn=lpad(txbepay_payer_id,9,'0')) WHERE txbepay_pidm is null and txbepay_payer_id<>'Guest'";
			$this->db->Execute($query);
			
	}//end processFiles function		
			
	/**
	* noErrors
	*
	* function run if no erros are detected in a feed
	* actually processes the files
	* called in processFiles function
	*
	* @since			version 1.0.0
	* @access			public	
	*/
	function noErrors($file)
	{
		if(!$file['error'])
		{
			if(($this->db->GetOne("SELECT count(*) FROM txbepay WHERE txbepay_file = '".$file['file_name']."'") > 0) || count($file['lines']) <= 2)
			{
				if(!$this->db->Execute("DELETE FROM ecommerce_pending_eod WHERE file_name = '".$file['file_name']."'"))
				{
					throw new PSUECommerceException(PSUECommerceException::DELETING_PENDING_EOD, ': [File Name: '.$eod_data['file_name'].'] - '.$this->db->ErrorMsg()."\n");
				}//end if
			}//end if	
			
		}//end if
		else
		{
			throw new PSUECommerceException(PSUECommerceException::LEGACY_MISMATCHED_VALUES, $this->file['file_name']);	
		}//end else
	}//end processErrors function
			
	/**
	* handleAlum
	*
	* function to run similar process to processFiles function on alumni feed files
	*
	* @since			version 1.0.0
	* @access			public			
	*/
	function handleAlum()
	{
		$this->db->Execute("DELETE FROM ecommerce_pending_eod WHERE file_name like 'psc_alumni_%'");
	}//end handleAlum function
	
	/**
	* processIntoTables
	*
	* main function to handle transfer of payment data to tables
	* makes calls to noLoad, postPayment, and splitPayment functions
	* 
	* @since		version 1.0.0
	* @access		public
	**/
	function processIntoTables()
	{
		$this->noLoad();
		
		$total_bal = 0;
		$old_term = ' ';

		$c_txbepay = "SELECT * 
				FROM txbepay 
				WHERE txbepay_load_date is null
					AND txbepay_load_status is null
					AND txbepay_pidm is not null
					AND (
							(
								(txbepay_trans_type=1 OR txbepay_trans_type=2)
								AND
								txbepay_trans_status=1
							)
							OR
							(
								txbepay_trans_type=3
								AND
								(txbepay_trans_status=5 OR txbepay_trans_status=6 OR txbepay_trans_status=8)
							)
					)";

		if($cv_trans = $this->db->Execute($c_txbepay))
		{
			for($num_processed = 0;$line = $cv_trans->FetchRow(); $num_processed++)
			{	
			
				$line = PSUTools::cleanKeys('txbepay_','r_',$line);
				
				//preset some values to be handled with every itteration of the while loop
				$trans = array(
					'pidm'=>$line['r_pidm'],
					'file' => $line['r_file'],
					'amount_paid'=>$line['r_trans_total']/100,
					'transactionid' => $line['r_trans_id'],
					'trans_type'=>$line['r_trans_type'],
					'desc'=>'Check-Payent-Thank You',
					'detail_code'=>'',
					'session_number'=>'000',
					'srce_code'=>'T',
					'acct_feed_ind'=>'Y',
					'term_code'=>NULL,
					'default_message'=>' ',
					'dept'=>NULL,
					'total_term_bill_bal'=>0,
					'term_chrg'=>0,
					'term_pay'=>0,
					'term_bal'=>0,
					'term_chrg_var'=>0,
					'term_pay_var'=>0,
					'auth_payments'=>0,
					'term_auth_payments'=>0,
					'term_memo_c_balance'=>0,
					'term_memo_f_balance'=>0,
					'term_bal_total'=>0,
					'total_auth_payments'=>0,
					'total_memo_c_balance'=>0,
					'total_memo_f_balance'=>0,
					'total_billed_balance'=>0,										
					'record_number'=>0,
					'pidm_exists'=>0,
					'tran_number'=>0,
					'multiplier'=>1
				);

				$this->outDebug('START TRANS PARSE', $trans, 'TRANS');
						
				$sql = "SELECT count(txraccd_pidm)
				          FROM txraccd 
				         WHERE txraccd_pidm=".$trans['pidm']." 
				           AND txraccd_epay_file = '".$trans['file']."'";
				
				if($trans['pidm_exists'] = $this->db->GetOne($sql))
				{
					$sql = "SELECT max(txraccd_epay_charge_number) 
					          FROM txraccd 
					         WHERE txraccd_pidm = ".$trans['pidm']." 
					           AND txraccd_epay_file = '".$trans['file']."'";
					$trans['charge_number'] = $this->db->GetOne($sql);
				}//end if
				else
				{
					$trans['charge_number']=0;
				}//end else
				
				//now to handle $v_trans_type
				if($trans['trans_type'] == 1)
				{
					$trans['detail_code'] = 'IQEW';
					$trans['desc'] = 'Credit-Card-Payment-Thank You';
					$trans['multiplier'] = 1;
				}
				elseif($trans['trans_type'] == 2)
				{
					$trans['detail_code'] = 'IREW';
					$trans['desc'] = 'Credit-Card-Refund';
					$trans['multiplier'] = -1;						
				}
				elseif($trans['trans_type'] == 3)
				{
					$trans['detail_code'] = 'IQEC';
					$trans['desc'] = 'E-Check-Payment-Thank You';
					$trans['multiplier'] = 1;						
				}
				
				$sql = "SELECT SUM (tbraccd_balance) acct_total
						FROM tbbdetc, tbraccd
						WHERE tbraccd_pidm=".$trans['pidm']."
							AND tbbdetc_detail_code = tbraccd_detail_code";
				
				//load the appropriate val into $term_bal_total
				$trans['term_bal_total'] = $this->_money($this->db->GetOne($sql));
				
				$this->outDebug('BEFORE SPLIT PAYMENT', $trans, 'TRANS');
				
				$this->splitPayment($trans, $trans['file']);
				
				$this->outDebug('AFTER SPLIT PAYMENT', $trans, 'TRANS');
				
				if($trans['amount_paid'] > 0)
				{
					$payment = array(
						'txraccd_epay_file'          => $trans['file'],
						'txraccd_pidm'               => $trans['pidm'],
						'txraccd_tran_number'        => $trans['transactionid'],
						'txraccd_term_code'          => $trans['term_code'],
						'txraccd_detail_code'        => $trans['detail_code'],
						'txraccd_user'               => $this->user,
						'txraccd_entry_date'         => strtoupper($this->entry_date),
						'txraccd_amount'             => $trans['amount_paid'],
						'txraccd_balance'            => (($trans['amount_paid'] * -1) * $trans['multiplier']),
						'txraccd_effective_date'     => strtoupper($this->effective_date),
						'txraccd_desc'               => $trans['desc'],
						'txraccd_srce_code'          => $trans['srce_code'],
						'txraccd_acct_feed_ind'      => $trans['acct_feed_ind'],
						'txraccd_activity_date'      => strtoupper($this->entry_date),
						'txraccd_session_number'     => $trans['session_number'],
						'txraccd_trans_date'         => strtoupper($this->trans_date)
					);

					$trans['charge_number']++;
					$payment['txraccd_epay_charge_number'] = $trans['charge_number'];

					$this->postPayment($payment);
					$trans['amount_paid']=0;
				}//end if($amount_paid > 0)
				
				if($this->db->GetOne("SELECT count(*) FROM txraccd WHERE txraccd_epay_file = '{$trans['file']}' AND txraccd_pidm = {$trans['pidm']}"))
				{
					if(!$this->db->Execute("UPDATE txbepay SET txbepay_load_status='loaded', txbepay_load_date = to_date('".$this->activity_date."','DD-Mon-YY HH24:MI:SS') WHERE txbepay_file = '".$trans['file']."' AND txbepay_pidm = ".$trans['pidm']." AND txbepay_load_status is null"))
					{
						throw new PSUECommerceException(PSUECommerceException::LEGACY_TRANSACTION_STATUS, ': Could not set transaction [file: '.$trans['file'].'] [pidm: '.$trans['pidm'].'] to "loaded" - '.$this->db->ErrorMsg()."\n");
					}//end if	
				}// end if $v_exists > 0
				
			}//end while
		}//end if $cv_trans = $this->db->Execute($this->c_txbepay)
		return $num_processed;
	}//end  function
	
	/**
	* noLoad
	*
	* function to handle the case where there are no files to load
	* called in processIntoTables function
	*
	* @since		version 1.0.0
	* @access		public
	**/
	function noLoad()
	{
		$c_txbepay_no_load = "SELECT *
						FROM txbepay
						WHERE txbepay_load_date is null
							AND txbepay_load_status is null
							AND txbepay_pidm is not null
							AND (
									(
										(txbepay_trans_type=1 OR txbepay_trans_type=2)
										AND
										txbepay_trans_status<>1
									)
									OR
									(
										txbepay_trans_type=3
										AND
										(txbepay_trans_status<>5 AND txbepay_trans_status<>6 AND txbepay_trans_status<>8)
									)
								)";
		if($cv_trans = $this->db->Execute($c_txbepay_no_load))
		{
			while($row = $cv_trans->FetchRow())
			{			
				$row = PSUTools::cleanKeys('txbepay_','r_',$row);
				if(!$this->db->Execute("UPDATE txbepay SET txbepay_load_status='no load necessary' WHERE txbepay_trans_id=".$row['r_trans_id'].""))
				{
					throw new PSUECommerceException(PSUECommerceException::LEGACY_TRANSACTION_STATUS, ': Could not set transaction [trans_id: '.$row['r_trans_id'].'] to "no load necessary"');
				}//end if
			}
		}			
	}//end no_load function

	function __construct(&$db, $prod = false)
	{
		parent::__construct($db);
		$this->activity_date = date('d-M-Y H:i:s');
		$this->entry_date = date('d-M-Y H:i:s');
		$this->effective_date = date('d-M-Y');
		$this->trans_date = date('d-M-Y');

		$config = \PSU\Config\Factory::get_config();
		
		$this->prod = $prod;
		$this->pass_phrase = $config->get_encoded( 'ecommerce', 'legacy_passwd' );
		$this->directory = $this->base_dir.'/'.(( $this->prod ) ? 'prod' : 'test');
		
		$this->user = 'INFINET';
	}//end constructor
}//end class
?>
