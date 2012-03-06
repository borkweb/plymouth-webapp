<?php

namespace PSU\Person;

class Addresses implements \ArrayAccess, \IteratorAggregate {
	public $pidm;
	public $addresses = array();

	public function __construct( $pidm ) {
		$this->pidm = $pidm;
	}//end __construct

	/**
	 * get active addresses as an iterator
	 *
	 * @param $it \b Iterator...allows sexy nesting
	 */
	public function active( $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if

		return new Addresses\ActiveFilterIterator( $it );
	}//end active

	/**
	 * get active addresses of a given type as an iterator
	 *
	 * @param $type \b Banner ATYP Code
	 * @param $it \b Iterator...allows sexy nesting
	 */
	public function active_by_type( $type, $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if

		return new Addresses\TypeFilterIterator( $type, $this->active( $it ) );
	}//end active_by_type

	/**
	 * count records
	 */
	public function count() {
		return count( $this->addresses );
	}//end count

	/**
	 * retrieve addresses for a person
	 */
	public function get() {
		$args = array(
			'pidm' => $this->pidm,
		);

		$sql = "BEGIN :c_cursor := gb_address.f_query_all(:pidm); END;";
		$rset = \PSU::db('banner')->ExecuteCursor($sql, 'c_cursor', $args);

		return $rset ? $rset : array();
	}//end get

	/**
	 * Iterator magic via IteratorAggregate
	 */
	public function getIterator() {
		return new \ArrayIterator( $this->addresses );
	}//end getIterator

	/**
	 * loads addresses for the provided pidm into
	 * the addresses array
	 *
	 * @param $rows \b iterable addresses
	 */
	public function load( $rows = null ) {
		// if addresses have already been loaded, return
		if( $this->addresses ) {
			return;
		}//end if

		// if no rows were sent, get some
		if( !isset($rows) ) {
			$rows = $this->get();
		}//end if

		// instantiate addresses and assign them into the
		// addresses array
		foreach( $rows as $row ) {
			$address = new Address( $row );
			$this->addresses[] = $address;
		}//end foreach
	}//end load

	/**
	 * ArrayAccess magic
	 */
	public function offsetExists( $offset ) {
		return isset( $this->addresses[ $offset ] );
	}//end offsetExists

	/**
	 * ArrayAccess magic
	 */
	public function offsetGet( $offset ) {
		if( ! is_numeric( $offset ) ) {
			return $this->active_by_type( $offset );
		}

		return isset( $this->addresses[ $offset ] ) ? $this->addresses[ $offset ] : null;
	}//end offsetGet

	/**
	 * ArrayAccess magic
	 */
	public function offsetSet( $offset, $value ) {
		if( is_null( $offset ) ) {
			$this->addresses[] = $value;
		} else {
			$this->addresses[ $offset ] = $value;
		}//end else
	}//end offsetSet

	/**
	 * ArrayAccess magic
	 */
	public function offsetUnset( $offset ) {
		unset( $this->addresses[ $offset ] );
	}//end offsetUnset

}//end class \PSU\Person\Addresses
