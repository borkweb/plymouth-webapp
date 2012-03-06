<?php

require_once('PSUTools.class.php');

class SessionStartCase extends PHPUnit_Framework_TestCase {
	function testFlags() {
		$this->assertEquals(
			PSU::session_start_flags(true),
			PSU::FORCE_SSL | PSU::NOLOG,
			'legacy true flag'
		);

		$this->assertEquals(
			PSU::session_start_flags(false),
			PSU::LOG,
			'legacy false flag'
		);

		$this->assertEquals(
			PSU::session_start_flags(),
			PSU::FORCE_SSL | PSU::NOLOG,
			'default flags'
		);

		$this->assertEquals(
			PSU::session_start_flags(PSU::NOLOG),
			PSU::NOLOG,
			'only specify nolog'
		);

		$this->assertEquals(
			PSU::session_start_flags(PSU::LOG),
			PSU::LOG,
			'only specify log'
		);

		$this->assertEquals(
			PSU::session_start_flags(PSU::ABORT_NOSSL),
			PSU::ABORT_NOSSL | PSU::LOG,
			'abort on no ssl adds logging'
		);

		$this->assertEquals(
			PSU::session_start_flags(PSU::FORCE_SSL),
			PSU::FORCE_SSL,
			'force ssl does not add logging'
		);
	}

	function testQA() {
		$get = $_GET;

		unset($_GET['psu-qa']);
		$this->assertFalse( PSU::qa( null, true ), 'false when psu-qa missing' );

		$_GET['psu-qa'] = 'foobar';
		$this->assertEquals( PSU::qa( null, true ), 'foobar', 'string result when area is not passed' );
		$this->assertTrue( PSU::qa('foobar', true), 'string result when qa matches' );
		$this->assertFalse( PSU::qa('baz', true), 'false when psu-qa does not match' );

		$_GET = $get;
	}
}
