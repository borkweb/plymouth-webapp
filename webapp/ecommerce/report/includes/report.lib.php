<?php
function getTrans($begin_date=NULL, $end_date=NULL)
{
	global $formatted_processors;
	
	$foapal = '';
	
	$GLOBALS['total'] = 0;
	$sql = "SELECT t.*
	          FROM ecommerce_transaction t
	         WHERE (
	                (transactiontype = 1 and transactionstatus = 1)
	                OR
	                (transactiontype = 3)
									OR
									(transactiontype = 2 and transactionstatus = 1)
	               )
	         	 AND (
	                (t.activity_date BETWEEN to_date('".date('d-M-y',strtotime($begin_date))."', 'DD-Mon-YY') 
	                 AND 
	                 to_date('".date('d-M-y',strtotime($end_date))."', 'DD-Mon-YY')
	                ) 
	               ) ";
	if($_GET['processor'])
	{
		$sql .= "  AND ordertype = '".$formatted_processors[$_GET['processor']]."'";
	}//end if
	else
	{
		$sql .= "  AND ordertype IN ('".implode("','", $formatted_processors)."')";
	}//end else
	         
  $sql .= " AND psu_status = 'loaded' ORDER BY fileid, accounttype, timestamp, transactionid";
	
	if($results = PSU::db('banner')->Execute($sql))
	{
		while($row = $results->FetchRow())
		{
			$row['foapal'] = array(
				'fund' => preg_replace('/^([a-zA-Z0-9]{6}).*/','\1',$row['userchoice1']),
				'org'  => preg_replace('/^(?:[a-zA-Z0-9]{6} *){1}([a-zA-Z0-9]{6}).*/','\1',$row['userchoice1']),
				'acct' => preg_replace('/^(?:[a-zA-Z0-9]{6} *){2}([a-zA-Z0-9]{6}).*/','\1',$row['userchoice1']),
				'prog' => preg_replace('/^(?:[a-zA-Z0-9]{6} *){3}([a-zA-Z0-9]{3}).*/','\1',$row['userchoice1']),
				'actv' => preg_replace('/^(?:[a-zA-Z0-9]{6} *){3}[a-zA-Z0-9]{3} *([0-9]{4}).*/','\1',$row['userchoice1']),
				'locn' => preg_replace('/^(?:[a-zA-Z0-9]{6} *){3}[a-zA-Z0-9]{3} *[0-9]{4} *([0-9]{4}).*/','\1',$row['userchoice1'])
			);
			$row['foapal']['actv'] = (preg_match('/ /',$row['foapal']['actv'])) ? '': $row['foapal']['actv'];
			$row['foapal']['locn'] = (preg_match('/ /',$row['foapal']['locn'])) ? '': $row['foapal']['locn'];
			$absolute_value_total = number_format(abs($row['totalamount'] / 100), 2);
			
			// was it a returned check or a credit card refund?
			if(($row['transactiontype'] == 3 && $row['transactionstatus'] == 7) || ($row['transactiontype'] == 2 && $row['transactionstatus'] == 1))
			{
				$row['debit_amount'] = $absolute_value_total;
				$row['dollar_amount'] = 0;
				$row['debit_credit'] = '-';
				
				$data[$row['fileid']]['debit_total'] += ($row['totalamount']/100);
				$GLOBALS['total'] -= ($row['totalamount']/100);
			}//end if
			else
			{
				$row['dollar_amount'] = $absolute_value_total;
				$row['debit_credit'] = '+';
				$data[$row['fileid']]['credit_total'] += ($row['totalamount']/100);
				$GLOBALS['total'] += ($row['totalamount']/100);
			}//end else
			
			$row['date'] = date('M j, Y', strtotime($row['timestamp']));
			
			$data[$row['fileid']]['transactions'][] = $row;
			if(!$foapal) $foapal = $row['foapal'];
		}//end while
	}//end if
	
	return array('data' => $data, 'foapal' => $foapal);
}//end getTrans
