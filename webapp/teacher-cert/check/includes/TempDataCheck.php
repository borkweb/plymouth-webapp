<?php

namespace PSU\TeacherCert;

class TempDataCheck extends ActiveRecord {
	static $table = 'temp_data_checks';
	static $_name = 'Temp Data Check';

	/**
	 * prepares arguments for DML
	 */
	protected function _prep_args() {
		// this is the data prepared for binding.
		// these fields are ordered as they are in the table
		$args = array(
			'key' => $this->key,
			'new_table' => $this->new_table,
			'new_column' => $this->new_column,
			'new_count' => $this->new_count,
			'old_table' => $this->old_table,
			'old_column' => $this->old_column,
			'old_count' => $this->old_count,
		);

		return $args;
	}//end _prep_args

	public function delete() {
		$sql = "DELETE FROM psu_teacher_cert.temp_data_check WHERE key = :key";
		return \PSU::db('banner')->Execute( $sql, array(
			'key' => $this->key,
		));
	}
	/**
	 * merge record SQL
	 */
	protected function _merge_sql( $table, $fields ) {
		$on = array(
			'key',
		);

		return parent::_merge_sql( $table, $fields, $on, false );
	}//end _merge_sql
}
