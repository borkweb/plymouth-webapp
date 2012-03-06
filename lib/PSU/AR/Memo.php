<?php

class PSU_AR_Memo extends PSU_Banner_DataObject {
	public $aliases = array(
		'tran_number' => 'transaction_number',
		'billing_ind' => 'billing_indicator',
		'desc' => 'description',
		'srce_code' => 'source_code',
		'crossref_pidm' => 'cross_reference_pidm',
		'crossref_number' => 'cross_reference_number',
		'crossref_detail_code' => 'cross_reference_detail_code',
		'crossref_srce_code' => 'cross_reference_source_code',
	);

	/**
	 * constructor
	 *
	 * @param $row array Array of row elements
	 */
	public function __construct( $row = null ) {
		if( $row ) {
			// get rid of table name from field names
			$row = PSU::cleanKeys('tbrmemo_', '', $row);
		}//end if

		parent::__construct( $row );

		$this->type_ind = $this->type_ind();
		$this->detail_desc = $this->detail_desc();
	}//end constructor

	/**
	 * returns the activity date's timestamp
	 */
	public function activity_date_timestamp() {
		return $this->date2timestamp( $this->activity_date );
	}//end activity_date_timestamp

	/**
	 * deletes the memo record
	 *
	 * minimum required fields are:
	 * 		pidm
	 * 		detail_code
	 * 		user
	 */
	public function delete() {
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
			'data_origin' => $args['data_origin'],
			'create_user' => $args['create_user'],
		);

		$sql = "
			DELETE FROM tbrmemo
		   WHERE tbrmemo_pidm = :pidm
			   AND tbrmemo_detail_code = :detail_code
				 AND tbrmemo_user = :the_user
		     AND (tbrmemo_term_code = :term_code OR :term_code IS NULL)
			   AND (tbrmemo_tran_number = :tran_number OR :tran_number IS NULL)
				 AND (tbrmemo_amount = :amount OR :amount IS NULL)
				 AND (tbrmemo_data_origin = :data_origin OR :data_origin IS NULL)
				 AND (tbrmemo_create_user = :create_user OR :create_user IS NULL)
		";

		return PSU::db('banner')->Execute( $sql, $args );
	}//end delete

	/**
	 * returns the receivable's detail description
	 */
	public function detail_desc() {
		return PSU_AR::detail_code( $this->detail_code )->desc;
	}//end detail_desc

	/**
	 * returns the effective date's timestamp
	 */
	public function effective_date_timestamp() {
		return $this->date2timestamp( $this->effective_date );
	}//end effective_date_timestamp
	
	public function entry_date_timestamp() {
		return $this->date2timestamp( $this->entry_date );
	}//end entry_date_timestamp

	/**
	 * returns the entry date's timestamp
	 */
	public function date2timestamp( $date ) {
		if( is_int($date) ) {
			return $date;
		} elseif( is_numeric($date) ) {
			return (int)$date;
		} else {
			return strtotime($date);
		}
	}

	/**
	 * returns the expiration date's timestamp
	 */
	public function expiration_date_timestamp() {
		return $this->date2timestamp( $this->expiration_date );
	}//end expiration_date_timestamp

	/**
	 * returns the release date's timestamp
	 */
	public function release_date_timestamp() {
		return $this->date2timestamp( $this->release_date );
	}//end release_date_timestamp

	/**
	 * save tbrmemo data
	 *
	 * @param $method \b method of saving. insert or merge
	 */
	public function save( $method = 'insert' ) {
		$this->validate('tbrmemo');

		$args = $this->_prep_args();

		$fields = $this->_prep_fields( 'tbrmemo', $args );

		$sql_method = '_' . $method . '_sql';
		$sql = $this->$sql_method( 'tbrmemo', $fields );

		return PSU::db('banner')->Execute( $sql, $args );
	}//end save

	/**
	 * returns the receivable's type indicator
	 */
	public function type_ind() {
		return PSU_AR::detail_code( $this->detail_code )->type_ind;
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
		// this is the data prepared for binding
		$args = array(
			'pidm' => $this->pidm,
			'tran_number' => $this->tran_number,
			'term_code' => $this->term_code,
			'detail_code' => $this->detail_code,
			'amount' => $this->amount,
			'the_user' => $this->user,
			'entry_date' => $this->entry_date ? PSU::db('banner')->BindDate( $this->entry_date_timestamp() ) : null,
			'billing_ind' => $this->billing_ind,
			'description' => $this->detail_desc,
			'release_date' => $this->release_date ? PSU::db('banner')->BindDate( $this->release_date_timestamp() ) : null,
			'expiration_date' => $this->expiration_date ? PSU::db('banner')->BindDate( $this->expiration_date_timestamp() ) : null,
			'effective_date' => $this->effective_date ? PSU::db('banner')->BindDate( $this->effective_date_timestamp() ) : null,
			'srce_code' => $this->srce_code,
			'crossref_pidm' => $this->crossref_pidm,
			'crossref_number' => $this->crossref_number,
			'crossref_detail_code' => $this->crossref_detail_code,
			'crossref_srce_code' => $this->crossref_srce_code,
			'atyp_code' => $this->atyp_code,
			'atyp_seqno' => $this->atyp_seqno,
			'data_origin' => $this->data_origin,
			'create_user' => $this->create_user,
			'crossref_dcat_code' => $this->crossref_dcat_code,
			'aidy_code' => $this->aidy_code,
		);

		$args['entry_date'] = $args['entry_date'] !== 'null' ? $args['entry_date'] : PSU::db('banner')->BindDate( time() );
		$args['effective_date'] = $args['effective_date'] !== 'null' ? $args['effective_date'] : PSU::db('banner')->BindDate( time() );

		return $args;
	}//end _prep_args
}//end class PSU_AR_Memo
