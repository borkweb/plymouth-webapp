<?php

namespace PSU\Student;

class Tests extends \PSU\Collection {
	// Active student
	public $student;

	static $child = '\\PSU\\Student\\Test';

	/**
	 *
	 */
	public function __construct( $student ) {
		$this->student = $student;
	}//end __construct

	/**
	 * Return the composite score for a set of scores.
	 */
	public function composite( $it = null ) {
		if( null === $it ) {
			$it = $this->getIterator();
		}

		$composite = 0;

		foreach( $it as $test ) {
			$composite += $test->score;
		}

		return $composite;
	}//end composite

	/**
	 * Return the highest available praxis scores in this collection.
	 */
	public function max_praxis() {
		$tests = new self( $this->student );
		$children = array();

		foreach( array('PRXR', 'PRXW', 'PRXM') as $code ) {
			$test = $this->max( $code );
			if( $test ) {
				$children[] = $test;
			}
		}

		$tests->add_children_bare( $children );

		return $tests;
	}//end max_praxis

	/**
	 * Filter the tests by code.
	 */
	public function filter() {
		$codes = func_get_args();

		// allow passing an array of codes as the first argument.
		// typecast in case there was just one code passed.
		if( count($codes) == 1 ) {
			$codes = (array)$codes[0];
		}
		$it = new Tests\FilterIterator( $this->getIterator() );
		$it->codes( $codes );

		return $it;
	}//end filter

	/**
	 *
	 */
	public function get() {
		$sql = "
			SELECT *
			FROM sortest
			WHERE sortest_pidm = :pidm
		";

		$args = array(
			'pidm' => $this->student->pidm,
		);

		if( $rset = \PSU::db('banner')->Execute($sql, $args) ) {
			return $rset;
		}

		return array();
	}//end get

	/**
	 * Returns the highest score for a given code.
	 * @param string $code
	 * @return PSU\Student\Test The test, or null.
	 */
	public function max( $code ) {
		$this->load();

		$highest_test = 0;

		foreach( $this->filter( $code ) as $test ) {
			if( ! $highest_test || $test->score > $highest_test->score ) {
				$highest_test = $test;
			}
		}

		return $highest_test;
	}//end max
}//end \PSU\Student\Tests
