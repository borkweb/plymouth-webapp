<?php

require_once 'autoload.php';

class PSU_FilterIteratorTest extends PHPUnit_Framework_TestCase {
	function testHasMatch() {
		$ai = new ArrayIterator( array('a', 'b', 'c', 'a') );

		foreach( array('a', 'b', 'c') as $char ) {
			$ai->match = $char;
			$iter = new PSU_FilterIteratorTest_Iter( $ai, $char );
			$this->assertTrue( $iter->has_match(), "find $char" );
		}

		$iter = new PSU_FilterIteratorTest_Iter( $ai, 'd' );
		$this->assertFalse( $iter->has_match() );
	}

	/**
	 * Test that iterators return all items after an initial has_match() call.
	 */
	function testIterateAfterHasMatch() {
		$ai = new ArrayIterator( array('a1', 'b1', 'g1', 'b2', 'c1', 'a2', 'd1', 'c2', 'b3', 'a3', 'c3') );

		foreach( array('a', 'b', 'c') as $char ) {
			$iter = new PSU_FilterIteratorTest_Iter( $ai, $char );
			$this->assertTrue( $iter->has_match(), "successfully find $char after has_match" );

			$found = 0;
			foreach( $iter as $item ) {
				$found += 1;
			}
			$this->assertEquals( 3, $found );
		}

		$iter = new PSU_FilterIteratorTest_Iter( $ai, 'e' );
		$this->assertFalse( $iter->has_match() );

		$found = 0;
		foreach( $iter as $item ) $found += 1;
		$this->assertEquals( 0, $found );
	}

	function testMultiRewind() {
		$input = array(1,2);
		$a = new ArrayIterator( $input );
		$it = new PSU_FilterIteratorTest_All( $a );

		$found = array();
		foreach( $it as $i ) {
			$found[] = $i;
		}
		$this->assertEquals( $input, $found, 'iter all works' );

		$it->rewind();
		$found = array();
		foreach( $it as $i ) {
			$found[] = $i;
		}
		$this->assertEquals( $input, $found, 'iter all works after rewind' );

		$it->rewind();
		$it->rewind();
		$found = array();
		foreach( $it as $i ) {
			$found[] = $i;
		}
		$this->assertEquals( $input, $found, 'iter all works after two rewinds' );
	}

	function testIteratorChain() {
		$input = array(1, 2);
		$a = new ArrayIterator( $input );
		$it1 = new PSU_FilterIteratorTest_All( $a );
		$it2 = new PSU_FilterIteratorTest_All( $it1 );

		$found = array();
		foreach( $it2 as $i ) {
			$found[] = $i;
		}
		$this->assertEquals( $input, $found, 'chain iterators' );
	}
}

class PSU_FilterIteratorTest_Iter extends PSU_FilterIterator {
	public function accept() {
		$item = $this->current();
		return substr($item, 0, 1) == $this->match;
	}

	public function __construct( $iterator, $match ) {
		parent::__construct( $iterator );
		$this->match = $match;
	}
}

class PSU_FilterIteratorTest_All extends PSU_FilterIterator {
	public function accept() {
		return true;
	}
}
