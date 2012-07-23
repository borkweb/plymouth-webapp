<?php
namespace PSU\AR;

class Receivable extends \PSU_Banner_DataObject {
	public $aliases = array(
		'tran_number' => 'transaction_number',
		'desc' => 'description',
		'srce_code' => 'source_code',
		'acct_feed_ind' => 'account_feed_indicator',
		'cshr_end_date' => 'cashier_end_date',
		'trans_date' => 'transaction_date',
	);

	/**
	 * constructor
	 *
	 * @param $row array Array of row elements
	 */
	public function __construct( $row = null ) {
		if( $row ) {
			// get rid of table name from field names
			$row = \PSU::cleanKeys('tbraccd_', '', $row);
		}//end if

		parent::__construct( $row );

		$this->type_ind = $this->type_ind();
		$this->detail_desc = $this->detail_desc();
	}//end constructor

	public function amount() {
		return $this->amount;
	}//end amount

	/**
	 * returns the bill date's timestamp
	 */
	public function bill_date_timestamp() {
		return strtotime( $this->bill_date );
	}//end bill_date_timestamp

	/**
	 * returns the cashier end date's timestamp
	 */
	public function cashier_end_date_timestamp() {
		return strtotime( $this->cashier_end_date );
	}//end cashier_end_date_timestamp

	/**
	 * deletes the receivable record
	 *
	 * minimum required fields are:
	 * 		pidm
	 * 		detail_code
	 * 		user
	 */
	public function delete( $commit = false ) {
		$args = $this->_prep_args();

		// limit the args down to what is needed for the
		// delete statement
		$args = array(
			'pidm' => $args['pidm'],
			'term_code' => $args['term_code'],
			'detail_code' => $args['detail_code'],
			'tran_number' => $args['tran_number'],
			'amount' => $args['amount'],
			'the_user' => $args['the_user'],
			'create_user' => $args['create_user'],
		);

		$sql = "
			DELETE FROM tbraccd
		   WHERE tbraccd_pidm = :pidm
			   AND tbraccd_detail_code = :detail_code
				 AND tbraccd_user = :the_user
		     AND (tbraccd_term_code = :term_code OR :term_code IS NULL)
			   AND (tbraccd_tran_number = :tran_number OR :tran_number IS NULL)
				 AND (tbraccd_amount = :amount OR :amount IS NULL)
				 AND (tbraccd_create_user = :create_user OR :create_user IS NULL)
		";

		\PSU::db('banner')->StartTrans();

		$result = \PSU::db('banner')->Execute( $sql, $args );

		\PSU::db('banner')->CompleteTrans( $commit );

		return $result;
	}//end delete

	/**
	 * returns the receivable's detail description
	 */
	public function detail_desc() {
		return \PSU\AR::detail_code( $this->detail_code )->desc;
	}//end detail_desc

	/**
	 * returns the due date's timestamp
	 */
	public function due_date_timestamp() {
		return strtotime( $this->due_date );
	}//end due_date_timestamp

	/**
	 * returns the effective date's timestamp
	 */
	public function effective_date_timestamp() {
		return strtotime( $this->effective_date );
	}//end effective_date_timestamp

	/**
	 * returns the entry date's timestamp
	 */
	public function entry_date_timestamp() {
		return strtotime( $this->entry_date );
	}//end entry_date_timestamp

	/**
	 * returns the feed date's timestamp
	 */
	public function feed_date_timestamp() {
		return strtotime( $this->feed_date );
	}//end feed_date_timestamp

	/**
	 * save tbraccd data
	 *
	 * @param $method \b method of saving. insert or merge
	 */
	public function save( $method = 'insert' ) {
		$this->validate('tbraccd');

		$args = $this->_prep_args();

		$fields = $this->_prep_fields( 'tbraccd', $args );

		$sql_method = '_' . $method . '_sql';
		$sql = $this->$sql_method( 'tbraccd', $fields );

		return \PSU::db('banner')->Execute( $sql, $args );
	}//end save

	/**
	 * returns the statement date's timestamp
	 */
	public function statement_date_timestamp() {
		return strtotime( $this->statement_date );
	}//end statement_date_timestamp

	/**
	 * returns the transaction date's timestamp
	 */
	public function transaction_date_timestamp() {
		return strtotime( $this->transaction_date );
	}//end transaction_date_timestamp

	/**
	 * returns the receivable's type indicator
	 */
	public function type_ind() {
		return \PSU\AR::detail_code( $this->detail_code )->type_ind;
	}//end type_ind

	/**
	 * merge record SQL
	 */
	protected function _merge_sql( $table, $fields ) {
		$on = array(
			'pidm',
			'term_code',
			'the_user',
			'detail_code',
		);

		return parent::_merge_sql( $table, $fields, $on );
	}//end _merge_sql

	/**
	 * prepares arguments for DML
	 */
	protected function _prep_args() {
		// this is the data prepared for binding.
		// these fields are ordered as they are in the table
		$args = array(
			'pidm' => $this->pidm,
			'tran_number' => $this->tran_number,
			'term_code' => $this->term_code,
			'detail_code' => $this->detail_code,
			'the_user' => $this->user,
			'entry_date' => $this->entry_date ? \PSU::db('banner')->BindDate( $this->entry_date_timestamp() ) : null,
			'amount' => $this->amount,
			'balance' => $this->balance,
			'effective_date' => $this->effective_date ? \PSU::db('banner')->BindDate( $this->effective_date_timestamp() ) : null,
			'bill_date' => $this->bill_date ? \PSU::db('banner')->BindDate( $this->bill_date_timestamp() ) : null,
			'due_date' => $this->due_date ? \PSU::db('banner')->BindDate( $this->due_date_timestamp() ) : null,
			'description' => $this->desc,
			'receipt_number' => $this->receipt_number,
			'tran_number_paid' => $this->tran_number_paid,
			'crossref_pidm' => $this->crossref_pidm,
			'crossref_number' => $this->crossref_number,
			'crossref_detail_code' => $this->crossref_detail_code,
			'srce_code' => $this->srce_code,
			'acct_feed_ind' => $this->acct_feed_ind,
			'session_number' => $this->session_number,
			'cshr_end_date' => $this->cshr_end_date ? \PSU::db('banner')->BindDate( $this->cshr_end_date_timestamp() ) : null,
			'crn' => $this->crn,
			'crossref_srce_code' => $this->crossref_srce_code,
			'loc_mdt' => $this->loc_mdt,
			'loc_mdt_seq' => $this->loc_mdt_seq,
			'rate' => $this->rate,
			'units' => $this->units,
			'document_number' => $this->document_number,
			'trans_date' => $this->trans_date ? \PSU::db('banner')->BindDate( $this->transaction_date_timestamp() ) : null,
			'payment_id' => $this->payment_id,
			'invoice_number' => $this->invoice_number,
			'statement_date' => $this->statement_date ? \PSU::db('banner')->BindDate( $this->statement_date_timestamp() ) : null,
			'inv_number_paid' => $this->inv_number_paid,
			'curr_code' => $this->curr_code,
			'exchange_diff' => $this->exchange_diff,
			'foreign_amount' => $this->foreign_amount,
			'late_dcat_code' => $this->late_dcat_code,
			'feed_date' => $this->feed_date ? \PSU::db('banner')->BindDate( $this->feed_date_timestamp() ) : null,
			'feed_doc_code' => $this->feed_doc_code,
			'atyp_code' => $this->atyp_code,
			'atyp_seqno' => $this->atyp_seqno,
			'card_type_vr' => $this->card_type_vr,
			'card_exp_date_vr' => $this->card_exp_date_vr ? \PSU::db('banner')->BindDate( $this->card_exp_date_vr_timestamp() ) : null,
			'card_auth_number_vr' => $this->card_auth_number_vr,
			'crossref_dcat_code' => $this->crossref_dcat_code,
			'orig_chg_ind' => $this->orig_chg_ind,
			'ccrd_code' => $this->ccrd_code,
			'merchant_id' => $this->merchant_id,
			'tax_rept_year' => $this->tax_rept_year,
			'tax_rept_box' => $this->tax_rept_box,
			'tax_amount' => $this->tax_amount,
			'tax_future_ind' => $this->tax_future_ind,
			'data_origin' => $this->data_origin,
			'create_source' => $this->create_source,
			'cpdt_ind' => $this->cpdt_ind,
			'aidy_code' => $this->aidy_code,
			'stsp_key_sequence' => $this->stsp_key_sequence,
		);

		return $args;
	}//end _prep_args
}//end class
