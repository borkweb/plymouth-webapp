<?php

require 'autoload.php';

use PSU_Student_Finaid_Application_Parent as p;

class ParenTest extends PHPUnit_Framework_TestCase {
	function testEquals() {
		$p1 = new p( array('rcrapp4_fath_ssn' => '111111111') );
		$p2 = new p( array('rcrapp4_fath_ssn' => '111111111') );
		$p3 = new p( array('rcrapp4_fath_ssn' => '111111112') );

		$this->assertTrue( $p1->equals( $p1 ), 'self equals' );
		$this->assertTrue( $p1->equals( $p2 ), 'other equals' );
		$this->assertFalse( $p1->equals( $p3 ), 'other not equal' );
	}
}
