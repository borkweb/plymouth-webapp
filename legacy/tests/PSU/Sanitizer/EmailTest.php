<?php

require_once 'autoload.php';

class PSU_Sanitizer_EmailTest extends PHPUnit_Framework_TestCase {
	function testClean() {
		$san = new PSU_Sanitizer_Email;

		$this->assertSame( null, $san->clean( '' ) );
		$this->assertSame( null, $san->clean( 'bad' ) );
		$this->assertEquals( 'user@plymouth.edu', $san->clean( 'USER@mail.plymouth.edu' ) );
	}

	function testLower() {
		$san = new PSU_Sanitizer_Email;

		$this->assertEquals( 'a@a.com', $san->lower( 'a@a.com' ) );
		$this->assertEquals( 'a@a.com', $san->lower( 'A@A.COM' ) );
	}

	function testConsolidate() {
		$san = new PSU_Sanitizer_Email;

		$this->assertEquals( 'test@plymouth.edu', $san->consolidatePlymouthDomains( 'test@plymouth.edu' ) );
		$this->assertEquals( 'test@plymouth.edu', $san->consolidatePlymouthDomains( 'test@mail.plymouth.edu' ) );
		$this->assertNotEquals( 'user@example.com', $san->consolidatePlymouthDomains( 'user@mail.example.com' ) );
	}

	function testValidate() {
		$san = new PSU_Sanitizer_Email;

		$this->assertEquals( 'user@example.com', $san->validate( 'user@example.com' ) );
		$this->assertEquals( false, $san->validate( 'test' ), 'bad email returns false' );
	}
}
