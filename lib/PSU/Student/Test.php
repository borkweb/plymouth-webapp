<?php

namespace PSU\Student;

use PSU_Banner_DataObject as DataObject;

class Test extends DataObject {
	public $aliases = array(
		'tesc_code' => 'code',
		'test_score' => 'score',
	);

	/**
	 *
	 */
	public function __construct( $row = null ) {
		if( $row ) {
			$row = \PSU::cleanKeys( 'sortest_', '', $row );
		}

		parent::__construct( $row );
	}//end __constructor

	/**
	 * Get the test code.
	 */
	public function code() {
		return $this->code;
	}

	/**
	 * Delete a test record. (Not implemented; throws Exception.)
	 */
	public function delete() {
		throw new \Exception('deleting test records is not implemented');
	}//end delete

	/**
	 * Get the test name.
	 */
	public function name() {
		return $this->_test_field( 'name' );
	}

	/**
	 *
	 */
	public function save( $method = 'insert' ) {
		$args = $this->_prep_args();
		$this->validate( 'sortest', $args );

		$fields = $this->_prep_fields( 'sortest', $args );
		$sql_method = "_{$method}_sql";

		$sql = $this->$sql_method( 'sortest', $fields );

		if( $result = \PSU::db('banner')->Execute( $sql, $args ) ) {
			return (0 == \PSU::db('banner')->ErrorNo());
		}

		return false;
	}//end save

	/**
	 * Generate the validation table cache.
	 */
	protected function _populate_cache() {
		$sql = "
			SELECT stvtesc_code, stvtesc_desc
			FROM stvtesc
		";

		$tests = array();

		if( $rset = \PSU::db('banner')->Execute($sql) ) {
			foreach( $rset as $row ) {
				$tests[ $row['stvtesc_code'] ] = $row['stvtesc_desc'];
			}

			return $tests;
		}

		return false;
	}

	/**
	 * PSU_Banner_DataObject method.
	 */
	public function _prep_args() {
		$args = array(
			'pidm' => $this->pidm,
			'tesc_code' => $this->tesc_code,
			'test_date' => \PSU::db('banner')->BindDate($this->test_date ?: time()),
			'test_score' => $this->test_score,
			'equiv_ind' => 'N',
		);

		return $args;
	}//end _prep_args

	/**
	 * Information about the test itself, rather than the student's
	 * taking of that test.
	 *
	 * @param string $field Field to return. Just 'name', for now.
	 */
	public function _test_field( $field ) {
		static $cache = null;

		if( null === $cache ) {
			$cache = $this->_populate_cache();
		}

		switch($field) {
			case 'name': return $cache[$this->code];
		}

		return null;
	}//end _test_field
}//end \PSU\Student\Test
