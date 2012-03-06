<?php

namespace PSU;

/**
 * A base class for holding a set of similar objects.
 */
abstract class Collection implements \ArrayAccess, \IteratorAggregate, \Countable {
	/**
	 * Class name of the collection child objects, used
	 * for instantiation. Redefine when extending Collection.
	 */
	static $child = 'stdClass';

	/**
	 * If set, children will be stored in $this->children using
	 * this property of the resulting child object.
	 */
	static $child_key = null;

	/**
	 * Name of iterator objects returned by this collection.
	 */
	static $iterator = '\ArrayIterator';

	/**
	 * Cached iterator.
	 */
	protected $it = null;

	protected $children = null;

	abstract public function get();

	public function add_children( $rows ) {
		$this->children = array();

		foreach( $rows as $row ) {
			$obj = new static::$child( $row );

			if( static::$child_key ) {
				$key = $obj->{static::$child_key};
				$this->children[$key] = $obj;
			} else {
				$this->children[] = $obj;
			}
		}
	}

	public function add_children_bare( $children ) {
		$this->children = $children;
	}

	public function count() {
		$this->load();
		return count( $this->children );
	}

	public function getIterator() {
		$this->load();

		if( ! $this->it ) {
			$this->it = new static::$iterator( $this->children );
		} else {
			$this->it->rewind();
		}

		return $this->it;
	}

	public function load() {
		if( null !== $this->children ) {
			return;
		}

		$rows = $this->get();

		$this->add_children( $rows );
	}

	/**
	 * ArrayAccess magic
	 */
	public function offsetExists( $offset ) {
		$this->load();

		return isset( $this->children[ $offset ] );
	}//end offsetExists

	/**
	 * ArrayAccess magic
	 */
	public function offsetGet( $offset ) {
		$this->load();

		return isset( $this->children[ $offset ] ) ? $this->children[ $offset ] : null;
	}//end offsetGet

	/**
	 * ArrayAccess magic
	 */
	public function offsetSet( $offset, $value ) {
		$this->load();

		if( is_null( $offset ) ) {
			$this->children[] = $value;
		} else {
			$this->children[ $offset ] = $value;
		}//end else
	}//end offsetSet

	/**
	 * ArrayAccess magic
	 */
	public function offsetUnset( $offset ) {
		$this->load();

		unset( $this->children[ $offset ] );
	}//end offsetUnset
}//end abstract class Collection
