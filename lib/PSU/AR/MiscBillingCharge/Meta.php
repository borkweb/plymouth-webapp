<?php

class PSU_AR_MiscBillingCharge_Meta extends PSU_Banner_DataObject {
	public $aliases = array();
	public $children = null;
	public $parent = null;

	/**
	 * constructor
	 *
	 * @param $row array Array of row elements
	 */
	public function __construct( $row = null ) {
		$row['id'] = $row['id'] ?: -1;
		parent::__construct( $row );
	}//end constructor

	/**
	 * returns the entry date's timestamp
	 */
	public function activity_date_timestamp() {
		return strtotime( $this->activity_date );
	}//end activity_date_timestamp

	public function children() {
		if( $this->children === null ) {
			$this->children = new PSU_AR_MiscBillingCharge_MetaContainer( $this->billing_id, $this->id );
			$this->children->load();
		}//end if

		return $this->children;
	}//end children

	/**
	 * deletes the meta record
	 *
	 * minimum required fields are:
	 * 		id
	 */
	public function delete( $commit = false ) {
		$args = $this->_prep_args();

		// limit the args down to what is needed for the
		// delete statement
		$args = array(
			'id' => $args['id'],
		);

		PSU::db('banner')->StartTrans();

		$sql = "DELETE FROM misc_billing_meta WHERE id = :id";
		$result = PSU::db('banner')->Execute( $sql, $args );

		PSU::db('banner')->CompleteTrans( $commit );

		return $result;
	}//end delete

	public static function get( $id ) {
		$sql = "SELECT * FROM misc_billing_meta WHERE id = :id";
		$row = PSU::db('banner')->GetRow( $sql, array('id' => $id) );

		return new self( $row );
	}//end get

	public function parent() {
		if( ! $this->parent_id ) {
			return null;
		}//end if

		if( $this->parent === null ) {
			$this->parent = self::get( $this->parent_id );
		}//end if

		return $this->parent;
	}//end parent

	/**
	 * save meta data
	 *
	 * @param $method \b method of saving. insert or merge
	 */
	public function save( $method = 'merge' ) {
		$this->validate('misc_billing_meta');

		$args = $this->_prep_args();

		$fields = $this->_prep_fields( 'misc_billing_meta', $args, true, false );

		$sql_method = '_' . $method . '_sql';
		$sql = $this->$sql_method( 'misc_billing_meta', $fields );

		if( $results = PSU::db('banner')->Execute( $sql, $args ) ) {
			if( $this->id <= 0 ) {
				$sql = "SELECT seq_misc_billing_meta.currval FROM dual";
				$this->id = PSU::db('banner')->GetOne( $sql );
			}//end if

			return $this->id;
		}//end if

		return null;
	}//end save

	/**
	 * merge record SQL
	 */
	protected function _merge_sql( $table, $fields ) {
		$on = array(
			'billing_id',
			'meta_key',
		);

		return parent::_merge_sql( $table, $fields, $on, false );
	}//end _merge_sql

	/**
	 * prepares arguments for DML
	 */
	protected function _prep_args() {
		if( $this->billing_id <= 0 ) {
			throw new \Exception('Billing ID must be set');
		}// if

		// this is the data prepared for binding.
		// these fields are ordered as they are in the table
		$args = array(
			'the_id' => $this->id,
			'billing_id' => $this->billing_id,
			'meta_key' => $this->meta_key,
			'meta_value' => $this->meta_value,
		);

		return $args;
	}//end _prep_args
}//end class PSU_AR_MiscBillingCharge_Meta
