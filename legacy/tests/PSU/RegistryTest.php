<?php

require_once('PSUTools.class.php');

class RegistryTest extends PHPUnit_Framework_TestCase {
	// assert that we can get a singleton
	function testGetSingleton() {
		$r = PSU::get();
		$this->assertTrue( $r instanceof PSU );
	}

	// assert that we always get the same object via singleton
	function testSameSingleton() {
		$this->assertSame( PSU::get(), PSU::get() );
	}

	// assert that db() with param gives us a database
	function testGetDatabase() {
		$this->assertTrue( PSU::db('PSC1') instanceof ADODB_oci8po );
		$this->assertTrue( PSU::db('oracle/psc1_psu/fixcase') instanceof ADODB_oci8po );
	}

	// assert that aliases are working (won't test all of them)
	function testSameDatabase() {
		$this->assertSame( PSU::db('PSC1'), PSU::db('PSC1') );
		$this->assertSame( PSU::db('psc1'), PSU::db('PSC1') );
		$this->assertSame( PSU::db('oracle/psc1_psu/fixcase'), PSU::db('PSC1') );
	} 

	// these databases should NOT be the same
	function testNotSameDatabases() {
		$this->assertNotSame( PSU::db('PSC1'), PSU::db('TEST') );
		$this->assertNotSame( PSU::db('oracle/test_psu/fixcase'), PSU::db('oracle/psc1_psu/fixcase') );
		$this->assertNotSame( PSU::db('TEST'), PSU::db('oracle/psc1_psu/fixcase') );
	}

	// assert that unknown aliases fail
	function testBadAlias() {
		$alias = 'asdfeqrqhrqewrkjqhfasdf';
		$this->setExpectedException(Exception);
		PSU::db($alias);
	}

	// shortcut tests
	function testShortcuts() {
		PSU::get()->add_shortcut('shortcut_foo', array('shortcut_foo', 'getInstance'));

		$a = PSU::get('shortcut_foo/1Adam12');
		$b = PSU::get('shortcut_foo/1Adam12');
		$c = PSU::get('shortcut_foo/1Adam13');

		$this->assertSame( $a, $b );
		$this->assertNotSame( $a, $c );
	}

	// shortcut tests w/ reflection
	function testShortcutsReflection() {
		PSU::get()->add_shortcut('shortcut_foo_r1', array('shortcut_foo', '__construct'));
		PSU::get()->add_shortcut('shortcut_foo_r2', array('shortcut_foo', 'shortcut_foo'));

		$a = PSU::get('shortcut_foo_r1/1Joe12');
		$b = PSU::get('shortcut_foo_r1/1Joe12');
		$c = PSU::get('shortcut_foo_r1/1Joe13');

		$this->assertSame( $a, $b );
		$this->assertNotSame( $a, $c );

		$a = PSU::get('shortcut_foo_r2/1Joe14');
		$b = PSU::get('shortcut_foo_r2/1Joe14');
		$c = PSU::get('shortcut_foo_r2/1Joe15');

		$this->assertSame( $a, $b );
		$this->assertNotSame( $a, $c );
	}
}

// Dummy class for testShortcuts above.
class shortcut_foo {
	public function __construct( $id ) {
		$this->id = $id;
	}

	public static function getInstance( $id ) {
		return new self( $id );
	}
}
