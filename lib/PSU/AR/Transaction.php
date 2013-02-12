<?php

namespace PSU\AR;

abstract class Transaction {
	public $term_code;
	public $person;
	public $amount;
	public $entries;
	public $level = null;

	abstract protected function generate_entry( $payment );
	abstract protected function prep_save( $entry );

	public function __construct( $person, $amount ) {
		$this->_init_person( $person );
		$this->_init_term();

		$this->amount = $amount;

		$entries = '\PSU\AR\Transaction\Entries\\' . $this->type;
		$this->entries = new $entries;
	}//end constructor

	/**
	 * adds a receivable entry to the Transaction_Entries iterator
	 *
	 * @param $payment \b TBRACCD field array
	 */
	protected function add_entry($payment) {
		if( ! $payment['amount'] ) {
			return;
		}//end if

		if( ! $payment['term_code'] ) {
			$payment['term_code'] = $this->term_code;
		}//end if

		$entry = $this->generate_entry( $payment );
		$this->entries->add( $entry );
	}//end add_entry

	/**
	 * Returns true if this transaction was a returned check or a refund
	 * Originally created for webapp/ecommerce/report
	 */
	public static function is_returned($transactiontype, $transactionstatus) {
		return ($transactiontype == 3 && $transactionstatus == 7) || ($transactiontype == 2 && $transactionstatus == 1);
	}

	/**
	 * save entries into base table
	 */
	public function save() {
		$status = true;

		foreach( $this->entries as $entry ) {
			$entry = $this->prep_save( $entry );

			if( ! $entry->save() ) {
				$status = false;
			}//end if
		}//end foreach

		return $status;
	}//end save

	/**
	 * split transaction into receivable entries
	 *
	 * @param $record_template \b bare-bones TBRACCD field array that will be used for all TBRACCD records during transaction processing
	 */
	public function split( $record_template ) {
		$this->amount_paid_remaining = $this->amount;

		/**
		 * Implementing the below code to work with AR office in an effort 
		 * to have student payments only apply to the term that they select 
		 * within the online billing app.
		 */
		$term_code = $record_template['term_code'] ?: $this->term_code;
		$this->term_payment($term_code, $record_template, $this->amount );
		return $this;
		//------------Currently Ignoring Everything Below Here--------------- 

		// find the earliest unsatisfied term
		$early_term = $this->person->bill->earliest_unsatisfied_term;

		$only_term_types = array();
		$pre_apply = array();
		$skip = array();

		// populate $only_term_types via filter if filter exists and level has been set
		if( \PSU::has_filter('transaction_term_types') && $this->level) {
			$only_term_types = \PSU::apply_filters( 'transaction_term_types', $only_term_types, $this->level );
		}//end if

		// populate $skip via filter if filter exists
		if( \PSU::has_filter('transaction_term_skip') && $this->level ) {
			$skip = \PSU::apply_filters( 'transaction_term_skip', $skip, $this->person->bill, $this->level );
		}//end if

		// populate $pre_apply via filter if filter is set
		if( \PSU::has_filter('transaction_split_pre_apply') ) {
			$pre_apply = \PSU::apply_filters( 'transaction_split_pre_apply', $pre_apply, $this->person->bill );

			// loop over terms to pre-apply payments to
			foreach($pre_apply as $term => $value) {
				$this->term_payment( $term, $record_template, $value );
			}//end while
		}//end if

		$found_term = false;

		// loop over term balances
		foreach((array) $this->person->bill->all_term_balances as $term => $value) {
			// find the current term
			if(!$found_term && $term != $early_term) continue;
			elseif($term == $early_term) $found_term = true;
			elseif( $value <= 0 ) continue;

			// if there are values in $only_term_types then we only want to put
			// transactions in specific terms.  If this term is not in the list
			// of allowable terms, then skip it.
			if( ! empty( $only_term_types ) ) {
				if( ! in_array( \PSU\Student::term_type( $term ), $only_term_types ) ) {
					continue;
				}//end if
			}//end if

			// if there are values in $skip then we want to be sure
			// we skip it.
			if( ! empty( $skip ) ) {
				if( in_array( $term, $skip ) ) {
					continue;
				}//end if
			}//end if

			$this->term_payment( $term, $record_template, $value );
		}//end while

		// if there is STILL money needing to be posted, prep a dummy term and post
		if($this->amount_paid_remaining > 0) {
			// We don't want to find old activity, and if we are here, we have applied funds to the current ter,
			// Target the next term specified by the bursar otherwise fall back to this term
			$term = \PSU\AR::bursar_future_term( strtolower( $this->level ) ) ?: $this->person->bill->last_balance_term();
		
			$payment = $record_template;
			$payment['term_code'] = $term;
			$payment['amount'] = $this->amount_paid_remaining;

			$this->amount_paid_remaining = 0;
			
			$this->add_entry($payment);
		}//end if

		return $this;
	}//end split

	protected function term_payment( $term, $payment, $value ) {
		$payment['term_code'] = $term;
		
		// retrieve any existing amounts for the term for TXRACCD
		$temp_balance = $this->entries->sum( $this->entries->term( $term ) );

		// calculate the new value to insert
		$value += $temp_balance->amount() ?: 0;

		// is amount remaining >= balance?
		if($this->amount_paid_remaining > 0) {

			$payment['amount'] = ($this->amount_paid_remaining >= $value) ? $value : $this->amount_paid_remaining;
			
			$this->amount_paid_remaining -= $payment['amount'];
			
			$this->add_entry($payment);
		}//end if
	}//end term_payment

	/**
	 * inits the person object
	 *
	 * @params $ident \b Person identifier OR PSUPerson object
	 */
	private function _init_person( $ident ) {
		if( is_object( $ident ) ) {
			$this->person = $ident;
		} else {
			$this->person = \PSUPerson::get( $ident );
		}//end if

		// throw an error if this person doesn't have a pidm
		if( ! $this->person->pidm ) {
			throw new \Exception('The Person with an identifier of '.$ident.' does not have a pidm');
		}//end if
	}//end _init_person

	/**
	 * initializes the bursar_term based on the student's level
	 */
	private function _init_term() {
		// if grad, use grad level...otherwise default to ug
		if( $this->person->banner_roles['student_grad'] ) {
			$level = 'gr';
		} else {
			$level = 'ug';
		}//end if

		$this->term_code = \PSU\AR::bursar_term( $level );
	}//end _init_term
}//end class \PSU\AR\Transaction
