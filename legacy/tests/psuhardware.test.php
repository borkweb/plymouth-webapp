<?php

require_once('simpletest/autorun.php');
require_once('PSUHardware.class.php');

class HardwareTest extends UnitTestCase
{
	function testSanitizeMAC() {
		// test that expected formats work fine
		$this->assertIdentical( PSUHardware::sanitizeMAC("01:23:45:67:89:AB"), "01:23:45:67:89:AB");
		$this->assertIdentical( PSUHardware::sanitizeMAC("01-23-45-67-89-AB"), "01:23:45:67:89:AB");
		$this->assertIdentical( PSUHardware::sanitizeMAC("0123456789AB"), "01:23:45:67:89:AB");

		// test that all expected characters work
		$this->assertIdentical( PSUHardware::sanitizeMAC("000000000000"), "00:00:00:00:00:00");
		$this->assertIdentical( PSUHardware::sanitizeMAC("111111111111"), "11:11:11:11:11:11");
		$this->assertIdentical( PSUHardware::sanitizeMAC("222222222222"), "22:22:22:22:22:22");
		$this->assertIdentical( PSUHardware::sanitizeMAC("333333333333"), "33:33:33:33:33:33");
		$this->assertIdentical( PSUHardware::sanitizeMAC("444444444444"), "44:44:44:44:44:44");
		$this->assertIdentical( PSUHardware::sanitizeMAC("555555555555"), "55:55:55:55:55:55");
		$this->assertIdentical( PSUHardware::sanitizeMAC("666666666666"), "66:66:66:66:66:66");
		$this->assertIdentical( PSUHardware::sanitizeMAC("777777777777"), "77:77:77:77:77:77");
		$this->assertIdentical( PSUHardware::sanitizeMAC("888888888888"), "88:88:88:88:88:88");
		$this->assertIdentical( PSUHardware::sanitizeMAC("999999999999"), "99:99:99:99:99:99");
		$this->assertIdentical( PSUHardware::sanitizeMAC("AAAAAAAAAAAA"), "AA:AA:AA:AA:AA:AA");
		$this->assertIdentical( PSUHardware::sanitizeMAC("BBBBBBBBBBBB"), "BB:BB:BB:BB:BB:BB");
		$this->assertIdentical( PSUHardware::sanitizeMAC("CCCCCCCCCCCC"), "CC:CC:CC:CC:CC:CC");
		$this->assertIdentical( PSUHardware::sanitizeMAC("DDDDDDDDDDDD"), "DD:DD:DD:DD:DD:DD");
		$this->assertIdentical( PSUHardware::sanitizeMAC("EEEEEEEEEEEE"), "EE:EE:EE:EE:EE:EE");
		$this->assertIdentical( PSUHardware::sanitizeMAC("FFFFFFFFFFFF"), "FF:FF:FF:FF:FF:FF");

		// test lowercase
		$this->assertIdentical( PSUHardware::sanitizeMAC("01:23:45:67:89:ab"), "01:23:45:67:89:AB");

		// test that a non-valid character fails
		$this->assertFalse( PSUHardware::sanitizeMAC("GGGGGGGGGGGG") );
		
		// assert that short and long strings fail
		$this->assertFalse( PSUHardware::sanitizeMAC("01:23:45:67:89:A") );
		$this->assertFalse( PSUHardware::sanitizeMAC("01:23:45:67:89:AB:C") );
	}

	function testsanitizeName() {
		// good names
		$this->assertIdentical( PSUHardware::sanitizeName("adam"), "ADAM" );
		$this->assertIdentical( PSUHardware::sanitizeName("adam01"), "ADAM01" );
		$this->assertIdentical( PSUHardware::sanitizeName("adam-01"), "ADAM-01" );

		// bad characters
		$this->assertFalse( PSUHardware::sanitizeName("adam?01") );
		$this->assertFalse( PSUHardware::sanitizeName("adam_01") );
		$this->assertFalse( PSUHardware::sanitizeName("adam01!") );

		// dash at end is invalid
		$this->assertFalse( PSUHardware::sanitizeName("adam-") );

		// nonalpha at beginning is invalid
		$this->assertFalse( PSUHardware::sanitizeName("9adam") );
		$this->assertFalse( PSUHardware::sanitizeName("-adam") );

		// too short
		$this->assertFalse( PSUHardware::sanitizeName("a") );
		$this->assertFalse( PSUHardware::sanitizeName("ad") );
		$this->assertFalse( PSUHardware::sanitizeName("ada") );

		// long enough
		$this->assertTrue( PSUHardware::sanitizeName("adam") );
	}
}
