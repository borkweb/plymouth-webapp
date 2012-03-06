<?php

require_once 'autoload.php';

class PSU_PopulationTest extends PHPUnit_Framework_TestCase {
	public $_stubbed = array(
		array( 'id' => 1, 'name' => 'adam' ),
		array( 'id' => 2, 'name' => 'matt' ),
		array( 'id' => 3, 'name' => 'dan' ),
	);

	function setUp() {

		$query = $this->getMock('PSU_Population_Query');
		
		$query->expects( $this->any() )
			->method('query')
			->will( $this->returnValue( $this->_stubbed ) );

		$factory = new PSU_Population_UserFactory_Simple;
		
		$this->population = new PSU_Population( $query, $factory );
	}

	function testQuery() {
		$this->population->query();
		$this->assertType( 'array', $this->population->matches );
	}

	function testIteration() {
		$this->population->query();

		$index = 0;

		foreach( $this->population as $user ) {
			$this->assertEquals( $this->_stubbed[$index]['id'], $user->id );
			$this->assertEquals( $this->_stubbed[$index]['name'], $user->name );

			$index += 1;
		}
	}
}
