<?php

namespace PSU\Person;

class Phones implements \ArrayAccess, \IteratorAggregate {
	public $pidm;
	public $phones = array();

	public function __construct( $pidm ) {
		$this->pidm = $pidm;
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
	 * get active phones of a given type as an iterator
	 *
	 * @param $type \b Banner ATYP Code
	 * @param $it \b Iterator...allows sexy nesting
	 */
	public function active_by_type( $type, $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if

		return new Phones\TypeFilterIterator( $type, $this->active( $it ) );
	}//end active_by_type

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
	 * get duplicate phones as an iterator
	 *
	 * @param $it \b Iterator...allows sexy nesting
	 */
	public function duplicates( $phone, $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if

		return new Phones\DuplicateFilterIterator( $phone, $it );
	}//end duplicates

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
			'pidm' => $this->pidm,
		);

		$sql = "BEGIN :c_cursor := gb_telephone.f_query_all(:pidm); END;";
		$rset = \PSU::db('banner')->ExecuteCursor($sql, 'c_cursor', $args);

		return $rset ? $rset : array();
	}//end get

	/**
	 * Iterator magic via IteratorAggregate
	 */
	public function getIterator() {
		return new \ArrayIterator( $this->phones );
	}//end getIterator

	public function is_empty() {
		return empty( $this->phones );
	}//end is_empty

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
	 * Return an active Phone object matching a given number, with or
	 * without area code). Returns false if no phone number matched.
	 */
	public function match( $number ) {
		foreach( $this->active() as $phone ) {
			if( false !== $phone->match($number) ) {
				return $phone;
			}
		}

		return false;
	}//end match 

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
			return $this->active_by_type( $offset );
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

	public function not_empty() {
		return ! $this->is_empty();
	}//end not_empty

	/**
	 * get primary phones as an iterator
	 *
	 * @param $it \b Iterator...allows sexy nesting
	 */
	public function primary( $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if

		return new Phones\PrimaryFilterIterator( $it );
	}//end primary

	/**
	 * get unlisted phones as an iterator
	 *
	 * @param $it \b Iterator...allows sexy nesting
	 */
	public function unlisted( $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if

		return new Phones\UnlistedFilterIterator( $it );
	}//end unlisted

}//end class \PSU\Person\Phones
