<?php

require_once 'autoload.php';

class PSU_Population_IteratorTest extends PHPUnit_Framework_TestCase {
	public $_stubbed = array(
		array( 'id' => 1, 'name' => 'adam'),
		array( 'id' => 2, 'name' => 'matt'),
		array( 'id' => 3, 'name' => 'dan'),
	);

	function setUp() {
		$userfactory = new PSU_Population_UserFactory_Simple;

		$this->iterator = new PSU_Population_Iterator( $this->_stubbed );
		$this->iterator->userfactory = $userfactory;
	}

	function testIteration() {
		$index = 0;

		foreach( $this->iterator as $user ) {
			$this->assertEquals( $this->_stubbed[$index]['id'], $user->id );
			$this->assertEquals( $this->_stubbed[$index]['name'], $user->name );

			$index += 1;
		}
	}

	function testOffsetGet() {
		$this->assertEquals( 1, $this->iterator[0]->id );
		$this->assertEquals( 'adam', $this->iterator[0]->name );

		$this->assertEquals( 3, $this->iterator[2]->id );
		$this->assertEquals( 'dan', $this->iterator[2]->name );

		$this->assertNotEquals( 3, $this->iterator[0]->id );
		$this->assertNotEquals( 'dan', $this->iterator[0]->name );
	}

	function testCurrent() {
		$user = $this->iterator->current();

		$this->assertEquals( 1, $user->id );
		$this->assertEquals( 'adam', $user->name );
	}

	function testNext() {
		$this->iterator->next();
		$user = $this->iterator->current();

		$this->assertEquals( 2, $user->id );
		$this->assertEquals( 'matt', $user->name );
	}

	function testRewind() {
		$this->iterator->next();
		$this->iterator->rewind();
		$user = $this->iterator->current();

		$this->assertEquals( 1, $user->id );
		$this->assertEquals( 'adam', $user->name );
	}
}
