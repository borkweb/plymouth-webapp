<?php

require_once 'autoload.php';

class REU_Ecosystems_PathGeneratorTest extends PHPUnit_Framework_TestCase {
	function setUp() {
		$this->datastore = $this->getMock( 'Datastore' );
		$this->datastore->expects( $this->any() )
			->method( 'get_filename' )
			->will( $this->returnValue( 'pretty.jpg' ) );
	}

	function testGetPath() {
		$pg = new REU_Ecosystems_PathGenerator( $this->datastore );

		$f = new PSU_Model_FormFile;
		$f->filemanager();

		$m = new PSU_Model;
		$this->assertEquals( 'pretty.jpg', $this->get_filename( $this->field ) );
	}
}
