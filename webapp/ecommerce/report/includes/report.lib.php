<?php
/**
 * Retrieve E-Commerce data
 * See notes in PSU/AR.php for input arguments
 *
 * Return Array:
 * - data : empty, or the selected transaction history indexed by 'fileid'
 *     each fileid entry contains:
 *     --- credit_total
 *     --- debit_total, optional, missing if none
 *     --- transactions, the array of data for this fileid
 *         -- orderamount is amount without decimal, so $50 shows up as '5000'
 *         this function appends to each DB 'transactions' row:
 *         -- dollar_amount, 0 if a debit, otherwsie the decimal amount  ex: 50.75
 *         -- debit_credit, a '+' or '-'
 *         -- debit_amount, only if it was a debit, as a decimal ex: '80.75'
 *         -- foapal, array
 * - foapal: empty if nothing found, or set to the 1st row's foapal.
 *
 * -- Side effect: --
 * - sets $GLOBALS['total']
 */
function getTrans($formatted_processors, $begin_date=NULL, $end_date=NULL, $processor=NULL)
{
	
	$foapal = '';
	
	$GLOBALS['total'] = 0; // used for "Grand Total" at the bottom of the report
	$sql = PSU\AR::get_history($formatted_processors, $begin_date, $end_date, $processor);
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
			if(PSU\AR\Transaction::is_returned($row['transactiontype'], $row['transactionstatus']))
			{
				$row['dollar_amount'] = 0;
				$row['debit_amount'] = $absolute_value_total;
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
			$data[$row['fileid']]['transactions'][] = $row; // put it into this fileid's transactions array
			if(!$foapal) $foapal = $row['foapal'];
		}//end while
	}//end if

	return array('data' => $data, 'foapal' => $foapal);
}//end getTrans
