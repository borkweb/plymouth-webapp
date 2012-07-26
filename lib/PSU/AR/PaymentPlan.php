<?php

namespace PSU\AR;

abstract class PaymentPlan extends \PSU_Banner_DataObject {
	public static $table = null;
	abstract public function amount();

	/**
	 * returns the attempted date's timestamp
	 */
	public function date_attempted_timestamp() {
		return $this->date_attempted ? strtotime( $this->date_attempted ) : null;
	}//end date_attempted_timestamp

	/**
	 * returns the parse date's timestamp
	 */
	public function date_loaded_timestamp() {
		return $this->date_loaded ? strtotime( $this->date_loaded ) : null;
	}//end date_loaded_timestamp

	/**
	 * returns the parse date's timestamp
	 */
	public function date_parsed_timestamp() {
		return $this->date_parsed ? strtotime( $this->date_parsed ) : null;
	}//end date_parsed_timestamp

	/**
	 * returns the parse date's timestamp
	 */
	public function date_processed_timestamp() {
		return $this->date_processed ? strtotime( $this->date_processed ) : null;
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

		$sql = "DELETE FROM " . static::$table . " WHERE id = :the_id";
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

	public static function document_number( $file_id ) {
		return 'P' . str_pad($file_id, 7, '0', STR_PAD_LEFT);
	}//end document_number

	public static function get( $id ) {
		$sql = "SELECT * FROM " . static::$table . " WHERE id = :the_id";
		$row = \PSU::db('banner')->GetRow( $sql, array('the_id' => $id) );

		return new static( $row );
	}//end get

	/**
	 * returns the person associated with this record
	 */
	public function person() {
		return \PSUPerson::get( $this->psu_id );
	}//end person

	/**
	 * save charge data
	 *
	 * @param $method \b method of saving. insert or merge
	 */
	public function save( $method = 'insert' ) {
		$this->validate( static::$table );

		$args = $this->_prep_args();

		$fields = $this->_prep_fields(  static::$table , $args, false, false );
		$sql_method = '_' . $method . '_sql';
		$sql = $this->$sql_method(  static::$table , $fields, false );

		if( $results = \PSU::db('banner')->Execute( $sql, $args ) ) {
			$sql = "SELECT max(id) FROM " . static::$table . " WHERE psu_id = :psu_id AND file_id = :file_id AND plan_type = :plan_type";
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
}//end class
