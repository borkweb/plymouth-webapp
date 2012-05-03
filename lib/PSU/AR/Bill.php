<?php
/**
 * PSU\AR\Bill.php
 *
 * Billing Object
 *
 * &copy; 2009 Plymouth State University ITS
 *
 * @author		Matthew Batchelder <mtbatchelder@plymouth.edu>
 */
namespace PSU\AR;

class Bill extends \BannerObject
{
	public $data = array();

	/**
	 * cleans up previous/current/future term decimal oddities
	 * @param $totals \b array containing term values
	 */
	public function cleanTotals($totals)
	{
		if($totals['previous_term'] < 0.0001 && $totals['previous_term'] > -0.0001)
			$totals['previous_term'] = 0;
		if($totals['current_term'] < 0.0001 && $totals['current_term'] > -0.0001)
			$totals['current_term'] = 0;
		if($totals['future_term'] < 0.0001 && $totals['future_term'] > -0.0001)
			$totals['future_term'] = 0;
		return $totals;
	}//end cleanTotals

	/**
	 * converts a given term and season into the appropriate payment plan term
	 */
	public static function convertPaymentPlanTerm( $term_code, $season, $level = 'UG') {
		$term = substr( $term_code, 0, 4 );
		$term_end = substr( $term_code, -2 );

		switch( $season ) {
			case 'fall':
				$term = $term_end == '40' || $term_end == '94' ? $term + 1 : $term;
				$term .= $level == 'UG' ? '10' : '91';
			break;
			case 'winter':
				$term = $term_end == '40' || $term_end == '94' ? $term + 1 : $term;
				$term .= $level == 'UG' ? '20' : '92';
			break;
			case 'spring':
				$term = $term_end == '40' || $term_end == '94' ? $term + 1 : $term;
				$term .= $level == 'UG' ? '30' : '93';
			break;
			case 'summer':
				$term = $term_end == '40' || $term_end == '94' ? $term : $term - 1;
				$term .= $level == 'UG' ? '40' : '94';		
			break;
		}//end switch

		return $term;
	}//end convertPaymentPlanTerm

	public function destroy() {
		$this->data = null;
		parent::destroy();
	}

	public function getMemoNetAmount($params = '')
	{
		$params = \PSU::params($params);

		$token = '';
		
		if(count($params) == 0)
		{
			$token = 'total';
		}//end if
		else
		{
			ksort($params);
			foreach($params as $key => $param)
			{
				$token .= $key.$param;
			}//end foreach
		}//end else
		
		$token = \PSU::createSlug($token);

		if(isset($this->data['memo_balance'][$token]))
		{
			$total = $this->data['memo_balance'][$token];
		}//end if
		else
		{
			$total = 0;
			
			$sql = "BEGIN :val := tb_memo.f_sum_net_amount(p_pidm => :pidm";
			foreach($params as $key => $p)
			{
				if($key == 'expire_date')
					$sql .= ", p_".$key." => to_date('" . $p ."', 'RRRR-MM-DD')";
				else
					$sql .= ", p_".$key." => :".$key;
			}//end foreach
			$sql .= "); END;";
			
			$stmt = \PSU::db('banner')->PrepareSP($sql);
			\PSU::db('banner')->InParameter($stmt, $this->pidm, 'pidm');
			foreach($params as $key => $p)
			{
				if($key != 'expire_date') \PSU::db('banner')->InParameter($stmt, $p, $key);
			}//end foreach
			\PSU::db('banner')->OutParameter($stmt, $total, 'val');
			\PSU::db('banner')->Execute($stmt);
			
			$this->data['memo_balance'][$token] = ($total) ? $total : 0;
		}//end else
				
		return $total;
	}//end getMemoNetAmount

	public function getReceivableBalance($params = null)
	{
		$token = '';
		
		if(count($params) == 0) {
			$token = 'total';
		} else {
			ksort($params);
			foreach($params as $key => $param) {
				$token .= $key.$param;
			}//end foreach
		}//end else
		
		$token = \PSU::createSlug($token);

		if(isset($this->data['receivable_balance'][$token])) {
			$total = $this->data['receivable_balance'][$token];
		} else {
			$balances = new PSU_AR_Sum_Balances( $this->pidm, $params );
			$balances->load();
			
			$total = $this->data['receivable_balance'][$token] = $balances->terms();
		}//end else
		return $total;
	}//end getReceivableBalance
	
	public function getReceivableNetAmount($params = '')
	{
		$params = \PSU::params($params);
		
		$token = '';
		
		if(count($params) == 0)
		{
			$token = 'total';
		}//end if
		else
		{
			ksort($params);
			foreach($params as $key => $param)
			{
				$token .= $key.$param;
			}//end foreach
		}//end else
		
		$token = \PSU::createSlug($token);

		if(isset($this->data['receivable_net_amount'][$token]))
		{
			$total = $this->data['receivable_net_amount'][$token];
		}//end if
		else
		{
			$total = 0;
			
			$sql = "BEGIN :val := tb_receivable.f_sum_net_amount(p_pidm => :pidm";
			foreach($params as $key => $p)
			{
				if($key == 'bill_date' || $key == 'as_of_date')
					$sql .= ", p_".$key." => to_date('" . $p ."', 'RRRR-MM-DD')";
				else
					$sql .= ", p_".$key." => :".$key;
			}//end foreach
			$sql .= "); END;";
			
			$stmt = \PSU::db('banner')->PrepareSP($sql);
			\PSU::db('banner')->InParameter($stmt, $this->pidm, 'pidm');
			foreach($params as $key => $p)
			{
				if($key != 'bill_date' && $key != 'as_of_date') \PSU::db('banner')->InParameter($stmt, $p, $key);
			}//end foreach
			\PSU::db('banner')->OutParameter($stmt, $total, 'val');
			\PSU::db('banner')->Execute($stmt);
			
			$this->data['receivable_net_amount'][$token] = ($total) ? $total : 0;
		}//end else		
		return $total;
	}//end getReceivableNetAmount

	public function has_activity() {
		return $this->memos || $this->deposits || $this->notes['total'] || $this->receivables;
	}//end has_activity

	/**
	 * returns the latest term with a balance
	 */
	public function last_balance_term() {
		$term = null;

		if( is_array( $this->all_term_balances ) ) {
			$keys = array_keys( $this->all_term_balances );
			$term = array_pop( $keys );
		}//end if

		return $term;
	}//end last_balance_term

	public static function markPaymentPlanAsProcessed( $id ) {
		$sql = "UPDATE payment_plan_contract SET date_processed = sysdate WHERE id = :id";
		if( !\PSU::db('banner')->Execute($sql, array('id' => $id)) ) {
			throw new \Exception('Marking Contract ('.$id.') as processed failed.'."\n\n".\PSU::db('banner')->ErrorMsg());
		}//end else
	}//end markPaymentPlanAsProcessed

	public function misc_charges( $term ) {
		return $this->misc_charges->terms[ $term ] ?: 0;
	}//end misc_charges

	/**
	 * parses payment plan data and inserts/updates memos for UG
	 */
	public static function parsePaymentPlanUG( $term_code, $record, $data ) {
		if( $data['funds_not_disbursed'] == 0 && $data['contract_balance'] == 0 ) {
			return true;
		}//end if

		$current_term_code = \PSU_AR::bursar_term('ug');

		$record['expiration_date'] = strtotime( '+5 days', $record['entry_date'] );

		\PSU::add_filter( 'transaction_skip', array( &$this, 'payment_plan_ug_skip_term_filter' ), 10, 2 );

		$transaction = new \PSU\AR\Transaction\Memo( $record['pidm'], $data['contract_balance'] );
		$transaction->billable( FALSE );
		$transaction->split( $record );
		if( ! $transaction->save() ) {
			return false;
		}//end if

		// are there funds not disbursed?
		if( $data['funds_not_disbursed'] > 0 ) {
			$record['term_code'] = $current_term_code;
			$record['amount'] = $data['funds_not_disbursed'];
			$record['billing_ind'] = 'Y';	

			$max_tran_number = \PSU_AR_Memos::max_tran_number( $record['pidm'] );
			$record['tran_number'] = $max_tran_number + 1;

			if( ! self::updateMemo($record) ) {
				return false;
			}//end if
		}//end if

		return true;
	}//end parsePaymentPlanUG

	/**
	 * we want to skip terms that aren't in the current aid year
	 */
	public function payment_plan_ug_skip_term_filter( $value, $bill, $level ) {
		if( strtoupper( $level ) == 'UG' ) {
			foreach( (array) $bill->all_term_balances as $term => $value ) {
				if( \PSU\Student::getAidYear() != \PSU\Student::getAidYear( $term ) ) {
					$value[] = $term;
				}//end if
			}//end foreach
		}//end if

		return $value;
	}//end apply_to_terms

	/**
	 * parses payment plan data and inserts/updates memos for GR
	 */
	public static function parsePaymentPlanGR( $record, $data ) {
		$term_code = \PSU_AR::bursar_term('gr');

		$record['expiration_date'] = strtotime( '+5 days', $record['entry_date'] );

		$newest_term = null;
		$terms = array();
		$contract_success = false;

		//
		// First, apply memos to terms from feed that have amounts
		//
		if( $record['amount'] = $data['fall_contract_balance'] ) {
			$terms[] = $record['term_code'] = self::convertPaymentPlanTerm($term_code, 'fall', 'GR');
			$contract_success = self::updateMemo($record);
			$record['tran_number']++;

			$newest_term = $record['term_code'] > $newest_term ? $record['term_code'] : $newest_term;
		}//end if

		if( $record['amount'] = $data['winter_contract_balance'] ) {
			$terms[] = $record['term_code'] = self::convertPaymentPlanTerm($term_code, 'winter', 'GR');
			$contract_success = self::updateMemo($record);
			$record['tran_number']++;

			$newest_term = $record['term_code'] > $newest_term ? $record['term_code'] : $newest_term;
		}//end if

		if( $record['amount'] = $data['spring_contract_balance'] ) {
			$terms[] = $record['term_code'] = self::convertPaymentPlanTerm($term_code, 'spring', 'GR');
			$contract_success = self::updateMemo($record);
			$record['tran_number']++;

			$newest_term = $record['term_code'] > $newest_term ? $record['term_code'] : $newest_term;
		}//end if

		if( $record['amount'] = $data['summer_contract_balance'] ) {
			$terms[] = $record['term_code'] = self::convertPaymentPlanTerm($term_code, 'summer', 'GR');
			$contract_success = self::updateMemo($record);
			$record['tran_number']++;

			$newest_term = $record['term_code'] > $newest_term ? $record['term_code'] : $newest_term;
		}//end if

		//
		// Now apply the pending payment
		//
		if( $data['funds_not_disbursed'] ) {
			$transaction = new \PSU\AR\Transaction\Memo( $record['pidm'], $data['funds_not_disbursed'] );
			$transaction->billable( TRUE );
			$transaction->split( $record );
			return $transaction->save();
		}//end if

		return $contract_success;
	}//end parsePaymentPlanGR

	/**
	 * returns a payment plan link
	 */
	public function paymentPlanURL($type = 'enr', $force_prod = false)
	{
		$query_string = array(
			'S_Id' => '04007', // TMS School Identifier
			'SA_Id' => '01',    // TMS School Account Identifier
			'a' => $this->person->id, // PSU ID
			'b' => $this->person->formatName('f'), // Student First Name
			'c' => $this->person->formatName('i'), // Student Middle Initial
			'd' => $this->person->formatName('l'), // Student Last Name
			'e' => '', // Payer First Name
			'f' => '', // Payer Middle Initial
			'g' => '', // Payer Last Name
			'h' => $this->person->address['MA'][0] ? $this->person->address['MA'][0]->street_line1 : '', // Address 1
			'i' => $this->person->address['MA'][0] ? $this->person->address['MA'][0]->street_line2 : '', // Address 2
			'j' => $this->person->address['MA'][0] ? $this->person->address['MA'][0]->city : '',         // City
			'k' => $this->person->address['MA'][0] ? $this->person->address['MA'][0]->stat_code : '',    // State
			'l' => $this->person->address['MA'][0] ? $this->person->address['MA'][0]->zip : '',          // Zip
			/*			'm' => number_format($this->balance['current_term'], 2, '.', ''), // Amount*/
			'm' => '0.00',
			'n' => $this->person->phone['MA'][0] ? $this->person->phone['MA'][0]->phone_area . $this->person->phone['MA'][0]->phone_number : '', // Phone
			'o' => $this->person->email['CA'][0] ? $this->person->email['CA'][0]->email_address : '', // Email
			'p' => $type, // Process: eft - Checking/Savings Payment, cc - Credit Card Payment, enr - Enrolling in a Payment Plan
			'c1' => $this->term_code, // Term identifier for enrolling in a payment plan
			'c2' => '', // If process is enr and you want annual plans to appear to student set c2 = -1.  For eft and cc use this field for custom data
			'c3' => '', // Custom Field
			'c4' => '', // Custom Field
			'c5' => '', // Custom Field
			'c6' => '', // Custom Field
			'c7' => '', // Custom Field
			'c8' => '' // Custom Field
		);

		$query_string = http_build_query($query_string);

		$url = 'https://'.(\PSU::isDev() && !$force_prod ? 'demo' : 'www').'.afford.com/IntelliLinks/IntelliLinks.aspx?'.$query_string;

		return $url;
	}//end paymentPlanURL

	/**
	 * returns the number of people with payment plan memos
	 */
	public static function peopleWithPaymentPlanMemos() {
		return \PSU::db('banner')->GetOne("SELECT count( distinct tbrmemo_pidm ) FROM tbrmemo WHERE tbrmemo_detail_code in ('IQPP', 'IQPQ')");
	}//end peopleWithPaymentPlanMemos
	
	public function prepareTermBill()
	{
		if(!$this->term_code) throw new \UnexpectedValueException('Term Code must be set when preparing a bill');

		$this->term_desc = \PSU::db('banner')->GetOne("SELECT stvterm_desc FROM stvterm WHERE stvterm_code = :term", array('term' => $this->term_code));
		$this->_load_term_controls();
		$this->aid;
		$this->memos;
	}//end prepareTermBill

	/**
	 * return the current term's receivables (observing write off state)
	 */
	public function current_term_receivables( $term_code = null ) {
		$term_code = $term_code ?: $this->term_code;

		$receivables = $this->receivables->current_term( $term_code );

		if( ! $this->include_non_bill_entries ) {
			$receivables = $this->receivables->exclude_non_bill_entries( $receivables );
		}//end else

		return $receivables;
	}//end current_term_receivables

  /**                                                                                                                                                                                                                                                                           
   * returns the refund available amount                                                                                                                                                                                                                                        
   */                                                                                                                                                                                                                                                                           
  public function refund_available() {                                                                                                                                                                                                                                          
    $this->_load_refund_available();                                                                                                                                                                                                                                            
                                                                                                                                                                                                                                                                                
    return $this->refund_available;                                                                                                                                                                                                                                             
  }//end refund_available 

	/**
	 * recalculates values on the bill based on a new term code
	 */
	public function set_term( $term_code ) {
		if(!$term_code) throw new \InvalidArgumentException('Term Code must provided');

		$this->term_code = $term_code;
		$this->_calc_aid();
		$this->_load_current_term_aid();

		$this->_calc_deposits();

		$this->_calc_memos();
		$this->_calc_notes();
		$this->_load_current_term_memos();
		$this->_load_current_term_misc_memos();

		$this->_calc_receivables();

		$this->_load_pending_total();
		$this->_load_balance();
		$this->_load_account_balance();

		$this->_load_refund_available();

		if( isset( $this->data['not_billed'] ) ) {
			$this->_load_not_billed();
		}//end if
	}//end set_term

	public function total( $totals ) {
		$totals = $this->cleanTotals( $totals );

		$totals['total'] = $totals['previous_term'] + $totals['current_term'] + $totals['future_term'];

		return $totals;
	}//end total

	/**
	 * updates (or inserts where not exists) the person's GXRDIRD record
	 * @param $data \b GXRDIRD insert/update array
	 */
	public function updateDirectDeposit($data) {
		// make sure amounts aren't set in the DirectDeposit record
		unset($data['amount']);

		$dird = new \PSU_AR_DirectDeposit( $data );
		return $dird->save();
	}//end updateDirectDeposit

	public static function updateMemo( $data, $only_insert = true ) {
		$person = \PSUPerson::get( $data['pidm'] );

		$person->bill->memos;

		$exists = false;

		// look for memos 
		foreach( (array) $person->bill->memos->terms->terms[ $data['term_code'] ] as $memo ) {
			if( $memo->user == $data['userfield'] && 
				  $memo->detail_code == $data['detail_code'] &&
				  $memo->create_user == $data['create_user']
			) {
				$exists = true;
			}//end if
		}//end foreach

		// only save the memo if updates are allowed
		// OR if only inserts are allowed and the memo
		// doesn't already exist
		if( !$only_insert || ( $only_insert && !$exists ) ) {
			$memo = new \PSU_AR_Memo( $data );
			if( ! $memo->expiration_date ) {
				$memo->expiration_date = \PSU::db('banner')->GetOne("SELECT stvterm_end_date + 90 FROM stvterm WHERE stvterm_code = :term_code", array('term_code' => $data['term_code']));
			}//end if

			return $memo->save( $only_insert ? 'insert' : 'merge' );
		}//end if

		return true;
	}//end updateMemo

	/**
	 * create or update a payment plan memo
	 */
	public static function updatePaymentPlan( $data ){
		$time = time();

		try{
			$pidm = \IDMObject::getIdentifier( $data['psu_id'], 'psu_id', 'pidm' );

			if( !$pidm ) {
				throw new \Exception("Invalid ID: ID does not exist");
			}//end if
		} catch(Exception $i) {
			self::markPaymentPlanAsProcessed( $data['id'] );
			throw new \Exception("Invalid ID: ID does not exist");
		}//end catch

		$data['plan_type'] = strtolower( $data['plan_type'] );

		$record = array(
			'pidm' => $pidm,
			'userfield' => 'TMS_CONTRACT',
			'user' => 'TMS_CONTRACT',
			'entry_date' => $time,
			'effective_date' => $time,
			'activity_date' => $time,
			'srce_code' => 'Z',
			'data_origin' => 'feed_'.$data['file_sub_type'],
			'create_user' => 'tms_'.$data['tms_customer_number'],
			'detail_code' => $data['plan_type'] == 'annual' ? 'IQPP' : 'IQPQ',
			'billing_ind' => 'N', // set the billing indicator to N for standard plan memos.
		);

		$term_code = $record['term_code'] = \PSU\Student::getCurrentTerm('ug');

		$del_data = array(
			'pidm' => $pidm,
			'detail_code' => 'IQPP',
			'user' => 'TMS_CONTRACT',
			'data_origin' => 'feed_'.$data['file_sub_type'],
			'create_user' => 'tms_'.$data['tms_customer_number'],
		);

		$memo = new \PSU_AR_Memo( $del_data );
		$memo->delete();

		$del_data['detail_code'] = 'IQPQ';

		$memo = new \PSU_AR_Memo( $del_data );
		$memo->delete();

		$max_tran_number = \PSU_AR_Memos::max_tran_number( $pidm );

		$record['tran_number'] = $max_tran_number + 1;

		if( $data['report_group'] == 'UG' ) {
			self::parsePaymentPlanUG( $term_code, $record, $data );
		} else {
			self::parsePaymentPlanGR( $record, $data );
		}//end else

		// payment plan parsing was successful for this record.  mark it as processed.
		self::markPaymentPlanAsProcessed( $data['id'] );

		$person = null;

		return true;
	}//end updatePaymentPlan

	/**
	 * calculates aid totals
	 */
	private function _calc_aid() {
		if( !$this->data['aid'] ) {
			$this->aid;
			return;
		}//end if

		$this->data['aid_total'] = array();
		$this->data['aid_total']['previous_term'] = 0;
		$this->data['aid_total']['future_term'] = 0;
		$this->data['aid_total']['current_term'] = 0;
		$this->data['aid_total']['total'] = 0;

		if( $this->data['aid']->count() > 0 ) {
			$this->data['aid_total']['previous_term'] = -1 * $this->data['aid']->sum( $this->data['aid']->previous_terms( $this->term_code ) )->amount();
			$this->data['aid_total']['current_term'] = -1 * $this->data['aid']->sum( $this->data['aid']->current_term( $this->term_code ) )->amount();
			$this->data['aid_total']['future_term'] = -1 * $this->data['aid']->sum( $this->data['aid']->future_terms( $this->term_code ) )->amount();
		}//end if

		$this->data['aid_total'] = $this->total( $this->data['aid_total'] );
	}//end _calc_aid

	/**
	 * calculates deposit totals
	 */
	private function _calc_deposits() {
		if( !$this->data['deposits'] ) {
			$this->deposits;
			return;
		}//end if

		$this->data['deposit_total'] = array();
		$this->data['deposit_total']['previous_term'] = 0;
		$this->data['deposit_total']['future_term'] = 0;
		$this->data['deposit_total']['current_term'] = 0;
		$this->data['deposit_total']['total'] = 0;

		if( $this->data['deposits']->count() > 0 ) {
			$this->data['deposit_total']['previous_term'] = $this->data['deposits']->sum( $this->data['deposits']->unexpired( $this->data['deposits']->released_previous_terms( $this->term_code ) ) )->amount();
			$this->data['deposit_total']['current_term'] = $this->data['deposits']->sum( $this->data['deposits']->unexpired( $this->data['deposits']->released_current_term( $this->term_code ) ) )->amount();
			$this->data['deposit_total']['future_term'] = $this->data['deposits']->sum( $this->data['deposits']->unexpired( $this->data['deposits']->released_future_terms( $this->term_code ) ) )->amount();
		}//end if

		$this->data['deposit_total'] = $this->total( $this->data['deposit_total'] );
	}//end _calc_deposits

	/**
	 * calculates memo totals
	 */
	private function _calc_memos() {
		if( !$this->data['memos'] ) {
			$this->memos;
			return;
		}//end if

		$this->data['memo_total'] = array();
		$this->data['memo_total']['previous_term'] = 0;
		$this->data['memo_total']['future_term'] = 0;
		$this->data['memo_total']['current_term'] = 0;
		$this->data['memo_total']['total'] = 0;

		if( $this->data['memos']->count() > 0 ) {
			$this->data['memo_total']['previous_term'] = $this->data['memos']->sum( $this->data['memos']->bill_memos_previous_terms( $this->term_code ) )->amount();
			$this->data['memo_total']['current_term'] = $this->data['memos']->sum( $this->data['memos']->bill_memos_current_term( $this->term_code ) )->amount();
			$this->data['memo_total']['future_term'] = $this->data['memos']->sum( $this->data['memos']->bill_memos_future_terms( $this->term_code ) )->amount();
		}//end if

		$this->data['memo_total'] = $this->total( $this->data['memo_total'] );
	}//end _calc_memos

	/**
	 * calculates note (misc_memo) totals
	 */
	private function _calc_notes() {
		if( !$this->data['memos'] ) {
			$this->memos;
			return;
		}//end if

		if( !$this->data['misc_memos'] ) {
			$this->misc_memos;
			return;
		}//end if

		$this->data['notes']['previous_term'] = 0;
		$this->data['notes']['future_term'] = 0;
		$this->data['notes']['current_term'] = 0;
		$this->data['notes']['total'] = 0;

		if( $this->data['memos']->count() > 0 ) {
			$this->data['notes']['previous_term'] = $this->data['memos']->sum( $this->data['memos']->previous_terms( $this->term_code, $this->data['misc_memos'] ) )->amount();
			$this->data['notes']['current_term'] = $this->data['memos']->sum( $this->data['memos']->current_term( $this->term_code, $this->data['misc_memos'] ) )->amount();
			$this->data['notes']['future_term'] = $this->data['memos']->sum( $this->data['memos']->future_terms( $this->term_code, $this->data['misc_memos'] ) )->amount();
		}//end if

		$this->data['notes'] = $this->total( $this->data['notes'] );
	}//end _calc_notes

	/**
	 * calculates other_term_aid totals
	 */
	private function _calc_other_term_aid() {
		if( !$this->data['aid'] ) {
			$this->aid;
			return;
		}//end if

		if( !$this->data['memos'] ) {
			$this->memos;
		}//end if

		$other_term_aid = &$this->data['other_term_aid'];

		$this->data['other_term_aid_total']['previous_term'] = 0;
		$this->data['other_term_aid_total']['future_term'] = 0;
		$this->data['other_term_aid_total']['total'] = 0;

		if( $this->data['aid']->count() > 0 ) {
			$this->data['other_term_aid_total']['previous_term'] = $this->data['memos']->sum( $this->data['memos']->previous_terms( $this->term_code, $this->data['other_term_aid'] ) )->amount();
			$this->data['other_term_aid_total']['future_term'] = $this->data['memos']->sum( $this->data['memos']->future_terms( $this->term_code, $this->data['other_term_aid'] ) )->amount();
		}//end if

		$this->data['other_term_aid_total'] = $this->total( $this->data['other_term_aid_total'] );
	}//end _calc_other_term_aid

	/**
	 * calculates receivable totals
	 */
	private function _calc_receivables() {
		if( !$this->data['receivables'] ) {
			$this->receivables;
			return;
		}//end if

		$this->data['receivable_total'] = array();
		$this->data['receivable_total']['previous_term'] = 0;
		$this->data['receivable_total']['future_term'] = 0;
		$this->data['receivable_total']['current_term'] = 0;
		$this->data['receivable_total']['total'] = 0;

		if( $this->data['receivables']->count() > 0 ) {
			if( $this->include_non_bill_entries ) {
				$this->data['receivable_total']['previous_term'] = $this->data['receivables']->sum( $this->data['receivables']->previous_terms( $this->term_code ) )->amount();
				$this->data['receivable_total']['current_term'] = $this->data['receivables']->sum( $this->data['receivables']->current_term( $this->term_code ) )->amount();
				$this->data['receivable_total']['future_term'] = $this->data['receivables']->sum( $this->data['receivables']->future_terms( $this->term_code ) )->amount();
			} else {
				$this->data['receivable_total']['previous_term'] = $this->data['receivables']->sum( $this->data['receivables']->exclude_non_bill_entries( $this->data['receivables']->previous_terms( $this->term_code ) ) )->amount();
				$this->data['receivable_total']['current_term'] = $this->data['receivables']->sum( $this->data['receivables']->exclude_non_bill_entries( $this->data['receivables']->current_term( $this->term_code ) ) )->amount();
				$this->data['receivable_total']['future_term'] = $this->data['receivables']->sum( $this->data['receivables']->exclude_non_bill_entries( $this->data['receivables']->future_terms( $this->term_code ) ) )->amount();
			}//end else
		}//end if

		$this->data['receivable_total'] = $this->total( $this->data['receivable_total'] );
	}//end _calc_receivables

	public function _load_balance()
	{
		$this->receivables;
		$this->pending_total;
		$this->data['balance'] = array();
		$this->data['balance']['previous_term'] = 0;
		$this->data['balance']['future_term'] = 0;
		$this->data['balance']['current_term'] = 0;

		$this->data['balance']['previous_term'] = $this->receivable_total['previous_term'] + $this->pending_total['previous_term'];
		$this->data['balance']['current_term'] = $this->receivable_total['current_term'] + $this->pending_total['current_term'];
		$this->data['balance']['future_term'] = $this->receivable_total['future_term'] + $this->pending_total['future_term'];

		$this->data['balance'] = $this->cleanTotals( $this->data['balance'] );

		$this->data['balance']['total'] = $this->data['balance']['previous_term'] + $this->data['balance']['current_term'] + $this->data['balance']['future_term'];
	}//end _load_balance
	
	public function _load_pending_total()
	{
		$this->data['pending_total'] = array();
		$this->data['pending_total']['previous_term'] = 0;
		$this->data['pending_total']['future_term'] = 0;
		$this->data['pending_total']['current_term'] = 0;
		$this->data['pending_total']['total'] = 0;

		$this->data['pending_total']['previous_term'] = $this->aid_total['previous_term'] + $this->memo_total['previous_term'];
		$this->data['pending_total']['current_term'] = $this->aid_total['current_term'] + $this->memo_total['current_term'];
		$this->data['pending_total']['future_term'] = $this->aid_total['future_term'] + $this->memo_total['future_term'];
		$this->data['pending_total']['total'] = $this->data['pending_total']['previous_term'] + $this->data['pending_total']['current_term'] + $this->data['pending_total']['future_term'];
	}//end _load_balance

	public function _load_account_balance()
	{
		// bwskoacc equivalent: acct_bal
		$this->account_balance = $this->balance['total'];
	}//end _load_account_balance

	/**
	 * loads amount available for refunding
	 */
	public function _load_refund_available() {
		$this->refund_available = 0;
		// load balance
		$this->balance;

		// load receivable amounts
		$this->receivables;

		// make sure the user has a balance of less than 0
		if($this->receivable_total['current_term'] < 0) {
			$has_plus = false;

			// Determine if the user has a PLUS loan for a current or future term.
			// Non-GR students with PLUS loans that contain a value < 0 in the balance
			//   field are not allowed the ability to request a refund due to government
			//   regulations.

			if(!$this->person->gr) {
				foreach($this->receivables->current_term($this->term_code) as $entry) {
					if(strstr($entry->desc, 'PLUS') && $entry->balance < 0) {
						$has_plus = true;
					}//end if
				}//end foreach	
			}//end if

			// current term pending activity
			$current_pending = ($this->pending_total['current_term'] > 0 ) ? $this->pending_total['current_term'] : 0;
			// previous term activity
			$previous = ($this->receivable_total['previous_term'] > 0) ? $this->receivable_total['previous_term'] : 0;
			// current term activity
			$current = ($this->receivable_total['current_term'] < 0) ? $this->receivable_total['current_term'] : 0;
			// future term activity
			$future = $this->balance['future_term'] > 0 ? $this->balance['future_term'] : 0;

			$total_balance = $this->balance['total'] * -1;
			$current_balance = $this->balance['current_term'] * -1;

			// set the refund amount to the sum of the previous, current, and future terms (inverted) if the
			//   PLUS indicator has not yet been set.  Otherwise set the refund to 0.
			if( $has_plus ) {
				$refund_available = 0;
			} else {
				$refund_available = ($current + $previous + $future + $current_pending) * -1;

				// make sure the calculated amount is never more than the overall total
				if( $refund_available > $total_balance ) {
					$refund_available = $total_balance;
				}//end if

				// make sure the calculated amount is never more than the current NET term amount
				$current_check = $current * -1;
				if( $refund_available > $current_check ) {
					$refund_available = $current_check;
				}//end if
			}//end else
		}//end if

		$this->refund_available = $refund_available > 0 ? $refund_available : 0;
	}//end _load_refund_available
	
	/**
	 * loads Accounts Receivable Billing Controls
	 */
	public function _load_controls()
	{
		$this->data['controls'] = array();
		$sql = "SELECT * FROM tbbctrl";
		$this->data['controls'] = \PSU::db('banner')->GetRow($sql);
		$this->data['controls'] = \PSU::cleanKeys('tbbctrl_', '', $this->data['controls']);
	}//end _load_controls
	
	/**
	 * populate the current_term_aid array
	 */
	protected function _load_current_term_aid() {
		$this->aid;

		$this->data['current_term_aid'] = $this->data['aid']->current_term( $this->term_code );
	}//end _load_current_term_aid

	/**
	 * populate the current_term_memos array
	 */
	protected function _load_current_term_memos()
	{
		$this->data['current_term_memos'] = $this->memos->bill_memos_current_term( $this->term_code );
	}//end _load_current_term_memos
	
	/**
	 * populate the current_term_misc_memos array
	 */
	protected function _load_current_term_misc_memos()
	{
		$this->data['current_term_misc_memos'] = $this->memos->misc_memos_current_term( $this->term_code );
	}//end _load_current_term_memos

	/**
	 * retrieve Financial Aid Authorization Memos.  Additionally, this function is used
	 *   to populate a number of object properties
	 *
	 * @access		public
	 * @return		array
	 */
	public function _load_aid()
	{
		$this->data['aid'] = array();

		$aid = new \PSU_AR_AidAuthorizations( $this->pidm );
		$aid->load();

		$this->data['aid'] = $aid;

		$this->_calc_aid();
	}//end _load_aid

	public function _load_all_term_balances() {
		$this->all_term_balances = array();
		$this->total_positive_balances = 0;
		$this->total_negative_balances = 0;

		$balances = new \PSU_AR_Sum_Balances( $this->pidm, array_keys( (array) $this->receivables->terms->terms ) );
		$balances->load();

		if( $balances->terms ) {
			$this->data['all_term_balances'] = $balances->terms;

			foreach($this->data['all_term_balances'] as $term => $value) {
				if($value >= 0) {
					$this->total_positive_balances += $value;
				} else {
					$this->total_negative_balances += abs($value);
				}//end else
			}//end foreach
		}//end if
	}//end _load_all_term_balances
	
	/**
	 * load deposits
	 */
	public function _load_deposits()
	{
		$this->data['deposits'] = array();

		$deposits = new \PSU_AR_Deposits( $this->pidm );
		$deposits->load();

		$this->data['deposits'] = $deposits;

		$this->_calc_deposits();
	}//end _load_deposits

	public function _load_earliest_unsatisfied_term()
	{
		$neg = floatval($this->total_negative_balances);
		
		$stored_term = null;
		$outstanding = 0;
		
		// loop over all the term balances to find the appropriate place to start
		foreach($this->all_term_balances as $term => $val)
		{						
			if($val > 0)
			{
				// set outstanding equal to the term's balance
				$outstanding = floatval($val);
				
				// if there is no stored term, assume this one should be stored
				$stored_term = ($stored_term) ? $stored_term : $term;
				
				// negative balance has been exhausted.  leave foreach
				if($neg == 0) break;
				
				// subt
				if($outstanding >= $neg)
				{
					// subtract negative balance from outstanding balance for term
					$outstanding -= $neg;
					
					// fix to the occasional float issue that causes 0 to be 2.XXXXXE-13.
					if($outstanding < 0.01) $outstanding = 0;
					
					// negative balances have been exhausted
					$neg = 0;
					
					// clear the stored term if there is no outstanding charges
					$stored_term = ($outstanding) ? $stored_term : null;
				}//end if
				else
				{
					$neg -= $outstanding;
					
					// fix to the occasional float issue that causes 0 to be 2.XXXXXE-13.
					if($neg < 0.01) $neg = 0;
					
					// set outstanding to 0
					$outstanding = 0;
					
					// clear the stored term because there are no outstanding charges
					$stored_term = null;
				}//end else
			}//end if
		}//end foreach
		
		// if there are no outstanding charges, clear the stored term
		// @todo possibly move to top of function?
		if(!$outstanding) $stored_term = null;
		
		$this->earliest_unsatisfied_term = ($stored_term) ? $stored_term : $this->term_code;
		$this->earliest_unsatisfied_term_remaining = $outstanding;
	}//end _load_earliest_unsatisfied_term
	
	/**
	 * populate the other_term_aid array
	 */
	protected function _load_other_term_aid() {
		$aid = &$this->data['aid'];

		$this->data['other_term_aid'] = $aid->exclude_term( $this->term_code );

		$this->_calc_other_term_aid();
	}//end _load_other_term_aid

	/**
	 * populate the other_term_memos array
	 */
	protected function _load_other_term_memos() {
		$memos = &$this->data['memos'];

		$this->data['other_term_memos'] = $memos->exclude_term( $this->term_code );
	}//end _load_other_term_memos

	/**
	 * load memos and account notes
	 */
	public function _load_memos()
	{
		$this->data['memos'] = array();
		$this->data['misc_memos'] = array();

		$memos = new \PSU_AR_Memos( $this->pidm );
		$memos->load();

		$this->data['misc_memos'] = $memos->misc_memos();
		$this->data['memos'] = $memos;

		$this->_calc_memos();
	}//end _load_memos

	public function _load_not_billed(){
		$this->data['not_billed'] = 0;

		$sql = "
			SELECT SUM(tbraccd_amount) 
				FROM tbraccd 
				     JOIN tbbdetc
						   ON tbbdetc_detail_code = tbraccd_detail_code
							AND tbbdetc_type_ind = 'C'
			 WHERE tbraccd_pidm = :pidm 
				 AND tbraccd_term_code <= :term_code 
				 AND tbraccd_bill_date IS NULL
		";
		$not_billed = \PSU::db('banner')->GetOne( $sql, array('pidm' => $this->pidm, 'term_code' => $this->term_code) );
		$this->data['not_billed'] = ($not_billed ? $not_billed : 0);
	}//end _load_not_billed

	public function _load_notes(){
		$this->misc_memos;
		$this->_calc_notes();
	}//end _load_notes

	public function _load_receivable()
	{
		$this->data['receivables'] = null;
		$this->data['misc_charges'] = null;

		$receivables = new \PSU_AR_Receivables( $this->pidm );
		$receivables->load();

		$this->data['misc_charges'] = $receivables->misc_charges();
		$this->data['receivables'] = $receivables;

		$this->_calc_receivables();
	}//end _load_receivable

	/**
	 * loads Accounts Receivable Term Controls
	 */
	public function _load_term_controls()
	{
		$this->data['term_controls'] = array();
		$sql = "SELECT * FROM tbbterm WHERE tbbterm_term_code = :term_code";
		$this->data['term_controls'] = \PSU::db('banner')->GetRow($sql, array('term_code' => $this->term_code));
		$this->data['term_controls'] = \PSU::cleanKeys('tbbterm_', '', $this->data['term_controls']);
	}//end _load_controls

	public function __construct($identifier, $term_code = null)
	{
		parent::__construct();
		
		if( $identifier instanceof \PSUPerson ) {
			$this->person = $identifier;
		} else {
			$this->person = \PSUPerson::get($identifier);
		}//end else

		if( $this->person->include_non_bill_entries ) {
			$this->include_non_bill_entries = true;
		}//end if

		$this->pidm = $this->person->pidm;
		
		$this->data_loaders = array(
			'all_term_current_amount'    => 'prepareTermBill',
			'earliest_unsatisfied_term_remaining' => 'earliest_unsatisfied_term',
			'other_term_current_amount'  => 'prepareTermBill',
			'other_term_net_amount'      => 'prepareTermBill',
			'other_term_aid_total'		 => 'other_term_aid',
			'term_current_amount'        => 'prepareTermBill',
			'term_future_amount'         => 'prepareTermBill',
			'total_negative_balances'    => 'all_term_balances',
			'total_positive_balances'    => 'all_term_balances',
			'aid_total' => 'aid',
			'memo_total' => 'memos',
			'deposit_total' => 'deposits',
			'misc_memos' => 'memos',
			'receivable_total' => 'receivable',
			'receivables' => 'receivable',
		);
		
		if($term_code) {
			$this->term_code = $term_code;
		}//end if
	}//end constructor

	/**
	 * Destructor
	 */
	public function __destruct(){
	}//end destructor

	/**
	 * Magic __set()
	 */
	public function __set($key, $value)
	{
		$this->data[$key] = $value;
		
		if($key == 'term_code') $this->prepareTermBill();
	}//end __set
}//end class \PSU\AR\Bill
