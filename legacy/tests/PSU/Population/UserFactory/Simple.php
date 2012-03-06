<?php

require_once 'autoload.php';

class PSU_Population_UserFactory_SimpleTest extends PHPUnit_Framework_TestCase {
	function testCreate() {
		$factory = new PSU_Population_UserFactory_Simple;

		$input = array( 'id' => 1024, 'name' => 'Adam Backstrom' );
		$user = $factory->create( $input );

		$this->assertEquals( $input['id'], $user->id );
		$this->assertEquals( $input['name'], $user->name );
	}
}
