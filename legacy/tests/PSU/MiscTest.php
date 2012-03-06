<?php

require_once('PSUTools.class.php');

class MiscTests extends PHPUnit_Framework_TestCase {
	function testRandomString() {
		$this->assertEquals( preg_match( '/^[A-Za-z0-9]{8}$/', PSU::randomString(8) ), 1, 'Length test: 8' );
		$this->assertEquals( preg_match( '/^[A-Za-z0-9]{20}$/', PSU::randomString(20) ), 1, 'Length test: 20' );

		$this->assertEquals( preg_match( '/^[0-9]{20}$/', PSU::randomString(20, '0123456789') ), 1, 'Pattern test (numbers)' );
		$this->assertEquals( preg_match( '/^A{20}$/', PSU::randomString(20, 'A') ), 1, 'Pattern test (letter A)' );

		$s = PSU::randomString(62, null, false);
		$len = strlen($s);
		$found = array();
		for( $i = 0; $i < $len; $i++ )
		{
			$c = $s[$i];
			$found[$c] = true;
		}
		$this->assertTrue( count($found) === 62, 'no duplicates');
	}
}
