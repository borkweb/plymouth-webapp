<?php

namespace PSU\Banner;

/**
 * Nation data from Banner.
 */
class Nations extends \PSU\Nations {
	private $cache = array();

	private $cache_by_code = array();
	private $cache_by_name = array();

	public function __construct() {
	}//end __construct

	public function init_cache() {
		if( empty($this->cache) ) {
			$this->cache_nations();
		}
	}//end init_cache

	public function cache_nations( $results = false ) {
		if( false === $results ) {
			$results = \PSU::db('banner')->Execute( "SELECT stvnatn_code, stvnatn_nation FROM stvnatn" );
		}

		foreach( $results as $nation ) {
			$obj = new $this->_nationClass( $nation['stvnatn_code'], $nation['stvnatn_nation'] );
			$this->cache[] =& $obj;

			unset($obj);
		}
	}//end cache_nations

	/**
	 * A single nation, by code.
	 */
	public function get_by_code( $code ) {
		$this->init_cache();

		foreach( $this->cache as $nation ) {
			if( $nation->code == $code ) {
				return $nation;
			}
		}

		return null;
	}//end get_by_code

	/**
	 * A single nation, by name.
	 */
	public function get_by_name( $name ) {
		$this->init_cache();

		foreach( $this->cache as $nation ) {
			if( $nation->name == $name ) {
				return $nation;
			}
		}

		return null;
	}//end get_by_name

	/**
	 * An iterator for nations sorted by name.
	 *
	 * @return Iterator
	 */
	public function sort_by_name() {
		return $this->sort_by_field( 'name' );
	}//end sort_by_name

	/**
	 * An iterator for nations sorted by code.
	 *
	 * @return Iterator
	 */
	public function sort_by_code() {
		return $this->sort_by_field( 'code' );
	}//end sort_by_code

	/**
	 * A general-purpose function for sorting nations by a field.
	 */
	public function sort_by_field( $field ) {
		$this->init_cache();

		$obj =& $this;

		uasort( $this->cache, function( &$a, &$b ) use ( $obj, $field ) {
			return strnatcasecmp( $a->$field, $b->$field );
		} );
	}//end sort_by_field

	public function getIterator() {
		$this->init_cache();
		return new \ArrayIterator( $this->cache );
	}//end getIterator
}//end class \PSU\Banner\Nations
