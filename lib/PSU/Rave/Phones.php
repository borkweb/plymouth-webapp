<?php

namespace PSU\Rave;

class Phones implements \ArrayAccess, \IteratorAggregate {
	public $wp_id;
	public $phones = array();

	public function __construct( $wp_id ) {
		$this->wp_id = $wp_id;
	}//end __construct

	/**
	 * get active phones as an iterator
	 *
	 * @param $it \b Iterator...allows sexy nesting
	 */
	public function active( $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if

		return new Phones\ActiveFilterIterator( $it );
	}//end active

	/**
	 * count records
	 */
	public function count() {
		return count( $this->phones );
	}//end count

	public function current( $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if
		
		return $this->first( $this->active( $it ) );
	}//end current

	/**
	 * returns the first element in the given iterator
	 *
	 * @param $it \b Iterator
	 */
	public function first( $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if
		
		$iterator = iterator_to_array( $it );

		return $iterator[0] ?: null;
	}//end first

	/**
	 * retrieve phones for a person
	 */
	public function get() {
		$args = array(
			'wp_id' => $this->wp_id,
		);

		$sql = "
			SELECT * 
			  FROM person_phone
			 WHERE wp_id = ?
			 ORDER BY id DESC
		";
		
		$rset = \PSU::db('emergency_notification')->Execute($sql, $args);

		return $rset ? $rset : array();
	}//end get

	/**
	 * Iterator magic via IteratorAggregate
	 */
	public function getIterator() {
		return new \ArrayIterator( $this->phones );
	}//end getIterator

	/**
	 * loads phones for the provided pidm into
	 * the phones array
	 *
	 * @param $rows \b iterable phones
	 */
	public function load( $rows = null ) {
		// if phones have already been loaded, return
		if( $this->phones ) {
			return;
		}//end if

		// if no rows were sent, get some
		if( !isset($rows) ) {
			$rows = $this->get();
		}//end if

		// instantiate phones and assign them into the
		// phones array
		foreach( $rows as $row ) {
			$phone = new Phone( $row );
			$this->phones[] = $phone;
		}//end foreach
	}//end load

	/**
	 * ArrayAccess magic
	 */
	public function offsetExists( $offset ) {
		return isset( $this->phones[ $offset ] );
	}//end offsetExists

	/**
	 * ArrayAccess magic
	 */
	public function offsetGet( $offset ) {
		if( ! is_numeric( $offset ) ) {
			return $this->type( $offset );
		}

		return isset( $this->phones[ $offset ] ) ? $this->phones[ $offset ] : null;
	}//end offsetGet

	/**
	 * ArrayAccess magic
	 */
	public function offsetSet( $offset, $value ) {
		if( is_null( $offset ) ) {
			$this->phones[] = $value;
		} else {
			$this->phones[ $offset ] = $value;
		}//end else
	}//end offsetSet

	/**
	 * ArrayAccess magic
	 */
	public function offsetUnset( $offset ) {
		unset( $this->phones[ $offset ] );
	}//end offsetUnset

	/**
	 * returns the Person's (determined via the wp_id) Rave phone
	 * status of the given type.
	 *
	 * @param $type \b Type of phone to check
	 */
	public function status( $type = 'CE' ) {
		if( $phone = $this->current( $this->type( $type ) ) ) {
			return $phone->status();
		}//end if

		return false;
	}//end status

	public function type( $type, $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if
		
		return new Phones\TypeFilterIterator( $type, $it );
	}//end current
}//end class \PSU\Rave\Phones
