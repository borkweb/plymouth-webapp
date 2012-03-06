<?php

include 'PSUModels/FormNumber.class.php';

class FormNumberTest extends PHPUnit_Framework_TestCase
{
	function testValue() {
		$n = new FormNumber;
		$n->value(3);

		$this->assertSame( 3, $n->value() );
	}
}
