<?php

require_once('PSUTools.class.php');

class FilterTest extends PHPUnit_Framework_TestCase {
	function testFilter() {
		PSU::add_filter( 'testFilter_1', array($this, 'method_filter') );
		$this->assertEquals( PSU::apply_filters( 'testFilter_1', 'foobar' ), 'method_filter_foobar', 'Filter via object method' );

		PSU::add_filter( 'testFilter_2', 'global_function_filter' );
		$this->assertEquals( PSU::apply_filters( 'testFilter_2', 'foobar' ), 'global_function_filter_foobar', 'Filter via global function' );

		PSU::add_filter( 'testFilter_3', array($this, 'multifilter1') );
		PSU::add_filter( 'testFilter_3', array($this, 'multifilter2') );
		$this->assertEquals( PSU::apply_filters( 'testFilter_3', 'foobar' ), 'multifilter2_multifilter1_foobar', 'Chaining filters' );

		PSU::add_filter( 'testFilter_4', array($this, 'multifilter3'), 11 );
		PSU::add_filter( 'testFilter_4', array($this, 'multifilter2') );
		PSU::add_filter( 'testFilter_4', array($this, 'multifilter1'), 9 );
		$this->assertEquals( PSU::apply_filters( 'testFilter_4', 'foobar' ), 'multifilter3_multifilter2_multifilter1_foobar', 'Chaining filters with priority #1' );

		PSU::add_filter( 'testFilter_5', array($this, 'multifilter1'), 9 );
		PSU::add_filter( 'testFilter_5', array($this, 'multifilter3'), 11 );
		PSU::add_filter( 'testFilter_5', array($this, 'multifilter2') );
		$this->assertEquals( PSU::apply_filters( 'testFilter_5', 'foobar' ), 'multifilter3_multifilter2_multifilter1_foobar', 'Chaining filters with priority #2' );
	}

	function testActions() {
		global $psu_actiontest_1;
		$psu_actiontest_1 = null;

		PSU::add_action( 'testAction_1', array($this, 'action1') );
		PSU::do_action( 'testAction_1' );
		$this->assertEquals( $psu_actiontest_1, 'psu_actiontest_1', 'Action via object method' );

		global $psu_actiontest_2;
		$psu_actiontest_2 = null;

		PSU::add_action( 'testAction_2', 'global_action' );
		PSU::do_action( 'testAction_2' );
		$this->assertEquals( $psu_actiontest_2, 'psu_actiontest_2', 'Action via object method' );
	}

	function method_filter( $s ) {
		return 'method_filter_' . $s;
	}

	function multifilter1( $s ) {
		return 'multifilter1_' . $s;
	}

	function multifilter2( $s ) {
		return 'multifilter2_' . $s;
	}

	function multifilter3( $s ) {
		return 'multifilter3_' . $s;
	}

	function action1() {
		global $psu_actiontest_1;
		$psu_actiontest_1 = 'psu_actiontest_1';
	}
}//end FilterTest

function global_function_filter( $s ) {
	return 'global_function_filter_' . $s;
}

function global_action() {
	global $psu_actiontest_2;
	$psu_actiontest_2 = 'psu_actiontest_2';
}
