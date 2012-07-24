<?php
namespace PSU\AR\PaymentPlan;

class Disbursement extends \PSU_Banner_DataObject {
	public $aliases = array();
	public $meta = null;
	public $origin = null;

	/**
	 * constructor
	 *
	 * @param $row array Array of row elements
	 */
	public function __construct( $row = null ) {
		parent::__construct( $row );

		$this->detail_desc = $this->detail_desc();

		$this->entry_date = $this->activity_date = date('Y-m-d H:i:s');
		$this->trans_date = $this->effective_date = date('Y-m-d');

		\PSU::add_filter( 'transaction_term_types', array( &$this, 'apply_to_terms' ), 10, 2 );
		\PSU::add_filter( 'transaction_term_skip', array( &$this, 'apply_skip_terms' ), 10, 3 );
	}//end constructor

	public function amount() {
		return $this->amount;
	}//end amount

	public function applied() {
		$sql = "
			SELECT SUM(tbraccd_amount) 
				FROM tbraccd 
			 WHERE tbraccd_pidm = :pidm 
				 AND tbraccd_user = 'TMS_DISBURSEMENT' 
				 AND tbraccd_document_number = :document_number
				 AND tbraccd_payment_id = :payment_id
		";

		$args = array(
			'pidm' => $this->person()->pidm,
			'document_number' => $this->document_number(),
			'payment_id' => $this->id,
		);

		$applied = \PSU::db('banner')->GetOne( $sql, $args ) ?: 0;

		return $this->amount == $applied;
	}//end applied

	/**
	 * we want to skip terms that aren't in the current aid year
	 */
	public function apply_skip_terms( $value, $bill, $level ) {
		if( ! $value ) {
			$value = array();
		}//end if

		if( strtoupper( $level ) == 'UG' ) {
			foreach( (array) $bill->all_term_balances as $term => $amount ) {
				if( \PSU\Student::getAidYear() != \PSU\Student::getAidYear( $term ) ) {
					$value[] = $term;
				}//end if
			}//end foreach
		}//end if

		return $value;
	}//end apply_to_terms

	public function apply_to_terms( $value, $level ) {
		if( strtoupper( $level ) == 'UG' ) {
			$value[] = 'ug_fall';
			$value[] = 'ug_spring';
		}//end if

		return $value;
	}//end apply_to_terms

	/**
	 * returns the attempted date's timestamp
	 */
	public function date_attempted_timestamp() {
		return strtotime( $this->date_attempted );
	}//end date_attempted_timestamp

	/**
	 * returns the parse date's timestamp
	 */
	public function date_parsed_timestamp() {
		return strtotime( $this->date_parsed );
	}//end date_parsed_timestamp

	/**
	 * returns the parse date's timestamp
	 */
	public function date_processed_timestamp() {
		return strtotime( $this->date_processed );
	}//end date_processed_timestamp

	/**
	 * "deletes" the charge record by flagging it as deleted
	 *
	 * minimum required fields are:
	 * 		id
	 */
	public function delete( $commit = false ) {
		$args = $this->_prep_args();

		// limit the args down to what is needed for the
		// delete statement
		$args = array(
			'the_id' => $args['the_id'],
		);

		\PSU::db('banner')->StartTrans();

		$sql = "DELETE FROM payment_plan_disbursement WHERE id = :the_id";
		$result = \PSU::db('banner')->Execute( $sql, $args );

		\PSU::db('banner')->CompleteTrans( $commit );

		return $result;
	}//end delete

	public function detail_code() {
		return $this->plan_type == 'Annual' ? 'IQPP' : 'IQPQ';
	}//end detail_code

	/**
	 * returns the detail description
	 */
	public function detail_desc() {
		return \PSU\AR::detail_code( $this->detail_code() )->desc;
	}//end detail_desc

	public function document_number() {
		return 'P' . str_pad($this->file_id, 7, '0', STR_PAD_LEFT);
	}//end document_number

	public static function get( $id ) {
		$sql = "SELECT * FROM payment_plan_disbursement WHERE id = :the_id";
		$row = \PSU::db('banner')->GetRow( $sql, array('the_id' => $id) );

		return new self( $row );
	}//end get

	public function init_template() {
		$payment = array(
			'pidm'               => $this->transaction->person->pidm,
			'detail_code'        => $this->detail_code(),
			'user'               => 'TMS_DISBURSEMENT',
			'entry_date'         => strtoupper($this->entry_date),
			'effective_date'     => strtoupper($this->effective_date),
			'desc'               => $this->detail_desc(),
			'srce_code'          => 'Z',
			'acct_feed_ind'      => 'Y',
			'activity_date'      => strtoupper($this->entry_date),
			'session_number'     => '000',
			'trans_date'         => strtoupper($this->trans_date),
			'document_number'    => $this->document_number(),
			'payment_id'         => $this->id,
		);

		return $payment;
	}//end init_template

	/**
	 * returns the person associated with this record
	 */
	public function person() {
		return \PSUPerson::get( $this->psu_id );
	}//end person

	public function process() {
		if( $this->date_processed ) {
			return true;
		}//end if

		\PSU::db('banner')->StartTrans();
		$success = false;

		$amount = $this->amount;
		$this->multiplier = 1;

		if( $amount < 0 ) {
			$this->multiplier = -1;
		}//end if

		$this->transaction = new \PSU\AR\Transaction\Receivable( $this->person(), $amount, $this->multiplier );
		// set the level of the Disbursement (UG/GR).  UG avoids applying to winter and summer terms
		// via the apply_to_terms method assigned to the transaction_term_types filter used in 
		// \PSU\AR\Transaction
		$this->transaction->level = $this->type();
		$receivable_template = $this->init_template();
		$this->transaction->split( $receivable_template );
		$this->transaction->save();

		$this->transaction->person->bill = null;
		$this->transaction->person = null;
		$this->transaction = null;

		if( $this->applied() ) {
			$success = true;
			$this->date_attempted = $this->date_processed = date('Y-m-d H:i:s');
			$results = $this->save('merge');
		} else {
			$results = false;
		}//end else

		\PSU::db('banner')->CompleteTrans( $success );

		return $results;
	}//end process

	/**
	 * save charge data
	 *
	 * @param $method \b method of saving. insert or merge
	 */
	public function save( $method = 'insert' ) {
		$this->validate('payment_plan_disbursement');

		$args = $this->_prep_args();

		$fields = $this->_prep_fields( 'payment_plan_disbursement', $args, false, false );
		$sql_method = '_' . $method . '_sql';
		$sql = $this->$sql_method( 'payment_plan_disbursement', $fields, false );

		if( $results = \PSU::db('banner')->Execute( $sql, $args ) ) {
			$sql = "SELECT max(id) FROM payment_plan_disbursement WHERE psu_id = :psu_id AND file_id = :file_id AND plan_type = :plan_type";
			$select_args = array(
				'psu_id' => $args['psu_id'],
				'file_id' => $args['file_id'],
				'plan_type' => $args['plan_type'],
			);
			return $this->id = \PSU::db('banner')->GetOne( $sql, $select_args );
		}//end if
	}//end save

	public function type() {
		return strpos( $this->file_name, 'Grad' ) === false ? 'UG' : 'GR';
	}//end type

	/**
	 * merge record SQL
	 */
	protected function _merge_sql( $table, $fields, $table_prepend = false ) {
		$on = array(
			'the_id',
		);

		return parent::_merge_sql( $table, $fields, $on, $table_prepend );
	}//end _merge_sql

	/**
	 * prepares arguments for DML
	 */
	protected function _prep_args() {
		// this is the data prepared for binding.
		// these fields are ordered as they are in the table
		$args = array(
			'the_id' => $this->id,
			'psu_id' => $this->psu_id,
			'name' => $this->name,
			'amount' => $this->amount,
			'plan_type' => $this->plan_type,
			'file_id' => $this->file_id,
			'date_attempted' => $this->date_attempted ? \PSU::db('banner')->BindDate( $this->date_attempted_timestamp() ) : null,
			'date_parsed' => $this->date_parsed ? \PSU::db('banner')->BindDate( $this->date_parsed_timestamp() ) : null,
			'date_processed' => $this->date_processed ? \PSU::db('banner')->BindDate( $this->date_processed_timestamp() ) : null,
		);

		return $args;
	}//end _prep_args
}//end class
