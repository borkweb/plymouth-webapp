<?php

require_once 'PSUTools.class.php';

/**
 * Container class to hold metadata.
 */
class PSUMeta {
	static $meta = array();

	static function delete( $webapp, $key ) {
		// precache this webapp's options
		if( ! isset( self::$meta[$webapp] ) ) {
			self::_init_options($webapp);
		}

		if( self::$meta[$webapp][$key] && self::$meta[$webapp][$key]->delete() ) {
			unset( self::$meta[$webapp][$key] );
		}
	}//end delete

	static function &get( $webapp, $key ) {
		// precache this webapp's options
		if( ! isset( self::$meta[$webapp] ) ) {
			self::_init_options($webapp);
		}

		// return the requested key
		if( isset( self::$meta[$webapp] ) && isset( self::$meta[$webapp][$key] ) ) {
			return self::$meta[$webapp][$key];
		}

		$n = null;
		return $n;
	}//end get

	static function set( $webapp, $key, $value ) {
		if( $option =& self::get($webapp, $key) ) {
			$option->value = $value;
			$option->save();
		} else {
			self::$meta[$webapp][$key] = PSUOption::set( $webapp, $key, $value );
		}
	}//end set

	static function _init_options( $webapp ) {
		$rset = PSU::db('myplymouth')->Execute("SELECT * FROM webapp_meta WHERE meta_webapp = ?", array($webapp));

		self::$meta[ $webapp ] = array();

		foreach($rset as $row) {
			$option = new PSUOption($row);
			self::$meta[ $option->webapp ][ $option->key ] =& $option;
			unset($option);
		}
	}//end _init_options
}//end PSUMeta

/**
 * A single option.
 */
class PSUOption {
	var $id;
	var $webapp;
	var $key;
	var $value;

	var $_orig_value;

	function __construct($row) {
		$row = (object)$row;

		if( isset($row->id) ) {
			$this->id = $row->id;
		}

		if( isset( $row->meta_webapp ) ) {
			$this->webapp = $row->meta_webapp;
		} else {
			$this->webapp = $row->webapp;
		}

		if( isset( $row->meta_key ) ) {
			$this->key = $row->meta_key;
		} else {
			$this->key = $row->key;
		}

		if( isset( $row->meta_value ) ) {
			$this->value = $row->meta_value;
		} else {
			$this->value = $row->value;
		}

		if( isset( $this->id ) ) {
			$this->orig_value = $this->value;
		}

		$this->changed = false;
	}//end __construct

	/**
	 * Remove this key.
	 */
	function delete() {
		if( isset($this->id) ) {
			$sql = "DELETE FROM webapp_meta WHERE id = ?";
			PSU::db('myplymouth')->Execute($sql, array($this->id));
		}

		return true;
	}//end delete

	/**
	 * Save this key.
	 */
	function save() {
		if( $this->value === $this->_orig_value ) {
			return;
		}

		$sql = "
			INSERT INTO webapp_meta (`meta_webapp`, `meta_key`, `meta_value`) VALUES (?, ?, ?)
			ON DUPLICATE KEY UPDATE `meta_value` = ?
		";

		$this->changed = false;

		PSU::db('myplymouth')->Execute($sql, array($this->webapp, $this->key, $this->value, $this->value));
	}//end save

	/**
	 * Set a key.
	 */
	public static function set( $webapp, $key, $value ) {
		$o = new self( array('webapp' => $webapp, 'key' => $key, 'value' => $value) );
		$o->save();
		return $o;
	}//end set

	function value( $value ) {
		$this->value = $value;
		return $this;
	}//end value

	function __toString() {
		return $this->value;
	}//end __toString
}//end PSUOption
