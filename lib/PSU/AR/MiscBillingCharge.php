<?php

class PSU_AR_MiscBillingCharge extends PSU_Banner_DataObject {
	public $aliases = array();
	public $meta = null;
	public $origin = null;
	public static $days_too_old = 90;
	public static $default_detail_code = null;

	/**
	 * constructor
	 *
	 * @param $row array Array of row elements
	 */
	public function __construct( $row = null ) {
		if( $row['meta'] ) {
			$meta = $row['meta'];
			unset( $row['meta'] );
		}//end if

		parent::__construct( $row );

		foreach( (array) $meta as $key => $value ) {
			$this->set_meta( $key, $value );
		}//end foreach

		if( ! $this->amount ) {
			$this->amount = 0;
		}//end if

		$this->type_ind = $this->type_ind();
		$this->detail_desc = $this->detail_desc();
	}//end constructor

	/**
	 * returns the activity date's timestamp
	 */
	public function activity_date_timestamp() {
		return strtotime( $this->activity_date );
	}//end activity_date_timestamp

	public function adjusted() {
		if( !isset( $this->num_adjustments ) ) {
			$this->adjustments();
		}//end if

		return $this->num_adjustments ? true : false;
	}//end adjusted

	/**
	 * retrieve charge's adjustments
	 */
	public function adjustments() {
		if( ( !isset( $this->num_adjustments ) || $this->num_adjustments > 0 ) && $this->adjustments === null ) {
			$this->adjustments = new PSU_AR_MiscBillingCharges();
			$this->adjustments->set('parent_id', $this->id)->load();

			$this->num_adjustments = $this->adjustments->count();
		}//end if

		return $this->adjustments;
	}//end adjustments

	public function adjustment_total() {
		$adjustment_total = 0;

		if( $this->adjustments() ) {
			$adjustment_total = $this->adjustments()->total();
		}//end if

		return $adjustment_total;
	}//end adjustment_total

	public function deletable() {
		if( $this->processed() ) {
			return false;
		}//end if

		$num = (int) $this->num_adjustments;

		return $num ? false : true;
	}//end deletable

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

		PSU::db('banner')->StartTrans();

		$sql = "UPDATE misc_billing SET deleted = 'Y' WHERE id = :the_id";
		$result = PSU::db('banner')->Execute( $sql, $args );

		PSU::db('banner')->CompleteTrans( $commit );

		return $result;
	}//end delete

	public static function detail_codes() {
		return array(
			static::$default_detail_code => \PSU_AR::detail_code( static::$default_detail_code ),
		);
	}//end detail_codes

	/**
	 * returns the charge's detail description
	 */
	public function detail_desc() {
		return \PSU_AR::detail_code( $this->detail_code )->desc;
	}//end detail_desc

	public function document_number() {
		return 'M' .str_pad(strtoupper($this->type_ind), 7, '0', STR_PAD_LEFT);
	}//end document_number

	/**
	 * returns the entry date's timestamp
	 */
	public function entry_date_timestamp() {
		return strtotime( $this->entry_date );
	}//end entry_date_timestamp

	public function external_data() {
		return array();
	}//end external_data

	public static function get( $id ) {
		$sql = "SELECT * FROM misc_billing WHERE id = :the_id";
		$row = PSU::db('banner')->GetRow( $sql, array('the_id' => $id) );

		return new static( $row );
	}//end get

	/**
	 * create the base array for creating a receivable (TBRACCD) record
	 * 
	 **/
	public function init_template() {
		$date = date('Y-m-d');

		$payment = array(
			'pidm'               => $this->pidm,
			'amount'             => $this->amount,
			'balance'            => $this->amount,
			'term_code'          => $this->term_code,
			'detail_code'        => $this->detail_code,
			'user'               => substr('MISC_BILLING_'. strtoupper( $_SESSION['username'] ), 0, 30),
			'entry_date'         => $date,
			'effective_date'     => $date,
			'desc'               => $this->detail_desc(),
			'srce_code'          => 'Y',
			'acct_feed_ind'      => 'Y',
			'activity_date'      => strtoupper(date('Y-m-d H:i:s')),
			'session_number'     => '000',
			'tran_number'        => \PSU\AR\Transaction\Receivable::next_tran_number( $this->pidm ),
			'trans_date'         => $date,
			'document_number'    => $this->document_number(),
			'payment_id'         => $this->id,
		);

		return $payment;
	}//end init_template

	public function insert_receivable() {
		$template = $this->init_template();

		$receivable = new PSU_AR_Receivable( $template );
		return $receivable->save();
	}//end insert_receivable

	/**
	 * returns the charge meta
	 */
	public function meta( $key = null ) {
		if( $this->meta === null ) {
			$this->meta = new PSU_AR_MiscBillingCharge_MetaContainer( $this->id );
			$this->meta->load();
		}//end if

		if( $key ) {
			return $this->meta->value( $key );
		}//end if

		return $this->meta;
	}//end meta

	public function meta_fields() {
		return $this->fields ?: array();
	}//end meta_fields

	/**
	 * retrieve the number of adjustments
	 */
	public function num_adjustments() {
		$this->adjustments();

		return $this->num_adjustments ?: 0;
	}//end num_adjustments

	/**
	 * returns the parent (if one is specified) associated with this record
	 */
	public function parent() {
		if( ! $this->parent_id ) {
			return null;
		}//end if

		if( $this->parent === null ) {
			$class = get_called_class();
			$this->parent = call_user_func( array( $class, 'get' ), $this->parent_id );
		}//end if

		return $this->parent;
	}//end parent

	/**
	 * returns the person associated with this record
	 */
	public function person() {
		return PSUPerson::get( $this->pidm );
	}//end person

	/**
	 * marks a record as processed
	 */
	public function process( $commit = true ) {
		PSU::db('banner')->StartTrans();

		// fail out if the record is too old
		if( $this->too_old() ) {
			PSU::db('banner')->CompleteTrans( false );
			throw new PSU\AR\MiscBillingCharge\TooOldException('Unable to process charges/adjustments older than ' . self::$days_too_old . ' days.');
		}//end if

		// fail out if the the user doesn't have a valid physical address
		if( ! $this->valid_addresses() ) {
			PSU::db('banner')->CompleteTrans( false );
			throw new PSU\AR\MiscBillingCharge\InvalidAddressException('Invalid Address');
		}//end if

		if( ! $this->receivable_exists() ) {
			// if we get in here, the receivable record has not been inserted yet

			// fail out if the receivable record cannot be inserted
			if( ! $this->insert_receivable() ) {
				PSU::db('banner')->CompleteTrans( false );
				throw new PSU\AR\MiscBillingCharge\FailedToInsertException('Failed to insert receivable record');
			}//end if
		}//end if

		// if the record has not yet been processed
		if( $this->processed != 'Y' || ! $this->process_date ) {
			// record has not been marked as processed...mark it as such
			$this->process_date = date('Y-m-d H:i:s');
			$this->processed = 'Y';

			// save this mofo
			if( $this->save() ) {
				PSU::db('banner')->CompleteTrans( $commit );
				return true;
			}//end if
		}//end if

		PSU::db('banner')->CompleteTrans( false );
		return false;
	}//end process

	/**
	 * returns the process date's timestamp
	 */
	public function process_date_timestamp() {
		return strtotime( $this->process_date );
	}//end process_date_timestamp

	/**
	 * Returns true if this record has been processed.
	 */
	public function processed() {
		return !! $this->process_date;
	}//end processed

	public function receivable_exists() {
		$receivables = new PSU_AR_Receivables( $this->pidm );
		$receivables->load();

		$num = iterator_count( $receivables->misc_billing_charges( $this->detail_code, $this->id ) );

		return $num ? true : false;
	}//end receivable_exists

	/**
	 * save charge data
	 *
	 * @param $method \b method of saving. insert or merge
	 */
	public function save( $method = 'merge' ) {
		$this->validate('misc_billing');

		$args = $this->_prep_args();

		$fields = $this->_prep_fields( 'misc_billing', $args, true, false );

		$sql_method = '_' . $method . '_sql';
		$sql = $this->$sql_method( 'misc_billing', $fields );

		if( $results = PSU::db('banner')->Execute( $sql, $args ) ) {
			if( $this->id <= 0 ) {
				$sql = "SELECT seq_misc_billing.currval FROM dual";
				$this->id = PSU::db('banner')->GetOne( $sql );
			}//end if

			// if meta is already initialized, make sure that the billing_id is set
			if( $this->meta ) {
				foreach( $this->meta as &$meta ) {
					$meta->billing_id = $this->id;
				}//end foreach
			}//end if

			return $this->id;
		}//end if
		return false;
	}//end save

	/**
	 * saves an array of meta data
	 */
	public function save_meta_array( $meta ) {
		// loop over meta fields and save them
		foreach( (array) $meta as $key => $value ) {
			// if there IS a meta value and it fails to save, return false
			if( $value != null && ! $this->set_meta( $key, $value )->save() ) {
				return false;
			}//end if

			// if there ISN'T a meta value passed in and there IS a meta value in the
			// database AND it fails to delete, return false
			if( $value == null && $this->meta($key)->meta_value != null && ! $this->meta( $key )->delete( true ) ) {
				return false;
			}//end if
		}//end foreach

		return true;
	}//end save_meta

	/**
	 * sets a meta value
	 */
	public function set_meta( $key, $value ) {
		if( $this->meta === null ) {
			$this->meta();
		}//end if

		// does the meta value need to be created?
		if( ! isset( $this->meta->meta[ $key ] ) ) {
			// yeah.  Initialize the required data
			$row = array(
				'billing_id' => $this->id,
				'meta_key' => $key,
				'meta_value' => $value,
				'activity_date' => time(),
			);

			// create meta record
			$this->meta->meta[ $key ] = new PSU_AR_MiscBillingCharge_Meta( $row );
		} else {
			$this->meta->meta[ $key ]->meta_value = $value;
		}//end else

		return $this->meta->meta[ $key ];
	}//end set_meta

	/**
	 * returns whether or not the charge is too old to process
	 */
	public function too_old() {
		return ! $this->processed() && $this->entry_date_timestamp() < self::$days_too_old;
	}//end too_old

	/**
	 * total charge
	 */
	public function total() {
		$total = $this->amount + $this->adjustment_total();

		return $total ?: 0;
	}//end total

	/**
	 * returns the charge's type indicator
	 */
	public function type_ind() {
		return PSU_AR::detail_code( $this->detail_code )->type_ind;
	}//end type_ind

	/**
	 * returns whether or not the charge is updatable
	 */
	public function updatable() {
		return ! $this->processed();
	}//end updatable

	/**
	 * does the attached user have valid addresses?
	 **/
	public function valid_addresses() {
		if( ! $this->person()->address['MA'] &&
			  ! $this->person()->address['B3'] &&
				! $this->person()->address['CA']
		) {
			return false;
		}//end if

		return true;
	}//end valid_addresses

	/**
	 * merge record SQL
	 */
	protected function _merge_sql( $table, $fields ) {
		$on = array(
			'the_id',
		);

		return parent::_merge_sql( $table, $fields, $on, false );
	}//end _merge_sql

	/**
	 * prepares arguments for DML
	 */
	protected function _prep_args() {
		// this is the data prepared for binding.
		// these fields are ordered as they are in the table
		$args = array(
			'the_id' => $this->id,
			'pidm' => $this->pidm,
			'term_code' => $this->term_code,
			'detail_code' => $this->detail_code,
			'data_source' => $this->data_source,
			'amount' => $this->amount,
			'entry_date' => $this->entry_date ? PSU::db('banner')->BindDate( $this->entry_date_timestamp() ) : null,
			'process_date' => $this->process_date ? PSU::db('banner')->BindDate( $this->process_date_timestamp() ) : null,
			'username' => $this->username,
			'processed' => $this->processed,
			'parent_id' => $this->parent_id,
			'deleted' => $this->deleted,
			'comments' => $this->comments,
		);

		return $args;
	}//end _prep_args
}//end class PSU_AR_MiscBillingCharge
