<?php

namespace PSU\Person;

class Emails implements \ArrayAccess, \IteratorAggregate {
	public $pidm;
	public $emails = array();

	public function __construct( $pidm ) {
		$this->pidm = $pidm;
	}//end __construct

	/**
	 * get active emails as an iterator
	 *
	 * @param $it \b Iterator...allows sexy nesting
	 */
	public function active( $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if

		return new Emails\ActiveFilterIterator( $it );
	}//end active

	/**
	 * get active emails of a given type as an iterator
	 *
	 * @param $type \b Banner ATYP Code
	 * @param $it \b Iterator...allows sexy nesting
	 */
	public function active_by_type( $type, $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if

		return new Emails\TypeFilterIterator( $type, $this->active( $it ) );
	}//end active_by_type

	/**
	 * count records
	 */
	public function count() {
		return count( $this->emails );
	}//end count

	/**
	 * retrieve emails for a person
	 */
	public function get() {
		$args = array(
			'pidm' => $this->pidm,
		);

		$sql = "BEGIN :c_cursor := gb_email.f_query_all(p_pidm => :pidm, p_emal_code => '%', p_email_address => '%'); END;";
		$rset = \PSU::db('banner')->ExecuteCursor($sql, 'c_cursor', $args);

		return $rset ? $rset : array();
	}//end get

	/**
	 * Iterator magic via IteratorAggregate
	 */
	public function getIterator() {
		return new \ArrayIterator( $this->emails );
	}//end getIterator

	/**
	 * loads emails for the provided pidm into
	 * the emails array
	 *
	 * @param $rows \b iterable emails
	 */
	public function load( $rows = null ) {
		// if emails have already been loaded, return
		if( $this->emails ) {
			return;
		}//end if

		// if no rows were sent, get some
		if( !isset($rows) ) {
			$rows = $this->get();
		}//end if

		// instantiate emails and assign them into the
		// emails array
		foreach( $rows as $row ) {
			$email = new Email( $row );
			$this->emails[] = $email;
		}//end foreach
	}//end load

	/**
	 * ArrayAccess magic
	 */
	public function offsetExists( $offset ) {
		return isset( $this->emails[ $offset ] );
	}//end offsetExists

	/**
	 * ArrayAccess magic
	 */
	public function offsetGet( $offset ) {
		if( ! is_numeric( $offset ) ) {
			return $this->active_by_type( $offset );
		}

		return isset( $this->emails[ $offset ] ) ? $this->emails[ $offset ] : null;
	}//end offsetGet

	/**
	 * ArrayAccess magic
	 */
	public function offsetSet( $offset, $value ) {
		if( is_null( $offset ) ) {
			$this->emails[] = $value;
		} else {
			$this->emails[ $offset ] = $value;
		}//end else
	}//end offsetSet

	/**
	 * ArrayAccess magic
	 */
	public function offsetUnset( $offset ) {
		unset( $this->emails[ $offset ] );
	}//end offsetUnset

	/**
	 * get preferred emails as an iterator
	 *
	 * @param $it \b Iterator...allows sexy nesting
	 */
	public function preferred( $it = null ) {
		if( $it === null ) {
			$it = $this->getIterator();
		}//end if

		return new Emails\PreferredFilterIterator( $it );
	}//end preferred

}//end class \PSU\Person\Emails
