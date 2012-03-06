<?php

require_once('PSUTools.class.php');

class CurlTest extends PHPUnit_Framework_TestCase {
	static $url = 'http://localhost:2000';

	function testCurlDefault() {
		$f = PSU::curl(self::$url . '/a');
		$get = fread($f, 1024);
		$this->assertEquals('A', $get);
	}

	function testCurlFile() {
		$get = PSU::curl(self::$url . '/b', PSU::FILE);
		$this->assertEquals('B', $get);
	}

	function testCurlFopen() {
		$f = PSU::curl(self::$url . '/c', PSU::FOPEN);
		$get = fread($f, 1024);
		$this->assertEquals('C', $get);
	}

	function testCurlFileGetContents() {
		$get = PSU::curl(self::$url . '/d', PSU::FILE_GET_CONTENTS);
		$this->assertEquals('D', $get);
	}

	function testCurlReadfile() {
		ob_start();
		$f = PSU::curl(self::$url . '/e', PSU::READFILE);
		$get = ob_get_contents();
		ob_end_clean();
		$this->assertEquals('E', $get);
	}

	function testCurlFail() {
		//$f = PSU::curl('http://ip.bwerp.net/?fail');
	}
}
