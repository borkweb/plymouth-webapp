<?php
function getTrans($begin_date=NULL, $end_date=NULL)
{
	if(!$begin_date) $begin_date=date('d-M-y');
	else $begin_date=date('d-M-y', strtotime($begin_date));
	
	if(!$end_date) $end_date=date('d-M-y');
	else $end_date=date('d-M-y', strtotime($end_date));
	
	$sql = "SELECT transactionid, 
	               totalamount, 
	               payerid, 
	               payerfullname, 
	               timestamp 
	          FROM ecommerce_transaction 
	         WHERE (
	                (timestamp BETWEEN to_date('{$begin_date}', 'DD-Mon-YY') AND to_date('{$end_date}', 'DD-Mon-YY')) 
	                OR 
	                timestamp = to_date('{$begin_date}', 'DD-Mon-YY') 
	                OR 
	                timestamp = to_date('{$end_date}', 'DD-Mon-YY')
	               ) 
	           AND psu_status = 'eod' 
	         ORDER BY timestamp";
	
	$result = $GLOBALS['BANNER']->GetAll($sql);
	return $result;
}//end getTrans
?>