<?php

require_once('PSUTools.class.php');

class HostnameTest extends PHPUnit_Framework_TestCase {
	function testHostname() {
		$this->assertEquals( PSU::hostname(false, 'foo.plymouth.edu'), 'foo' );
		$this->assertEquals( PSU::hostname(false, 'bar'), 'bar' );
		$this->assertEquals( PSU::hostname(false, 'cetus.plymouth.edu'), 'cetus' );
		$this->assertEquals( PSU::hostname(true, 'cetus.plymouth.edu'), 'capricorn' );
		$this->assertEquals( PSU::hostname(true, 'baz.plymouth.edu'), 'baz' );
	}
}
