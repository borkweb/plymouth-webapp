<?php

require_once 'autoload.php';

class PSU_Model_FormNumberTest extends PHPUnit_Framework_TestCase
{
	function testValue() {
		$n = new PSU_Model_FormNumber;
		$n->value(3);

		$this->assertSame( 3, $n->value() );
	}
}
