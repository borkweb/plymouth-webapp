<?php


/**
 * A simple class for data storage. Absorbs some incoming iterable into its
 * own property list, with the ability to alias fields under new names.
 */
abstract class PSU_DataObject implements Serializable {
	public $aliases = array();

	public function __construct( $row = null ) {
		if( $row ) {
			$this->populate( $row );
		}
	}//end __construct

	public function alias( $original, $alias ) {
		if( isset($this->$original) ) {
			$this->$alias =& $this->$original;
		}
	}//end alias

	public function aliases() {
		foreach( $this->aliases as $original => $alias ) {
			$this->alias( $original, $alias );
		}
	}//end aliases

	public function populate( $row ) {
		foreach( $row as $key => $value ) {
			if( isset($value) ) {
				$this->$key = $value;
			}
		}

		$this->aliases();
	}//end populate

	/**
	 *
	 */
	public function serialize() {
		$s = get_object_vars($this);
		unset($s['aliases']);

		return serialize($s);
	}//end serialize

	public function get_static( $var ) {
		return static::$$var;
	}//end static

	/**
	 *
	 */
	public function unserialize( $serialized ) {
		$s = unserialize($serialized);
		$this->populate($s);
	}//end unserialize
}//end class PSU_DataObject
