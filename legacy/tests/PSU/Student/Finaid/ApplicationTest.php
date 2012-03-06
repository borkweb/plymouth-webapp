<?php

require_once 'autoload.php';

use PSU_Student_Finaid_Application as app,
	PSU_Student_Finaid_Application_Parent as par;

class PSU_Student_Finaid_ApplicationTest extends PHPUnit_Framework_TestCase {
	function testParentsMatch() {
		$app1 = new app( 1, 1010 );
		$app2 = new app( 1, 1010 );

		$truepar->getMock('par');
		$truepar->expects( $this->any() )->method('equals')->will( $this->returnValue(true) );

		$falsepar->father = $this->getMock('par');
		$falsepar->father->expects( $this->any() )->method('equals')->will( $this->returnValue(false) );
	}
}
