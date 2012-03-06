<?php

require_once 'PSUDatabase.class.php';
require_once 'PSUTools.class.php';

/**
 * A class for dealing with Memcache in a consistent way.
 */
class PSUMemcache extends Memcache
{
	const NS = 'psumc';

	public function __construct($prefix)
	{
		$this->prefix = $prefix;

		$file = 'other/psumc' . (PSU::isdev() ? '_dev' : '');
		$mc = PSUDatabase::connect($file, 'return');

		$this->connect($mc['hostname'], $mc['port']);
	}//end __construct

	/**
	 * Add a key to memcached.
	 */
	function add( $key, $var, $flag = null, $expire = null ) {
		$key = $this->key($key);
		return parent::add( $key, $var, $flag, $expire );
	}//end add

	/**
	 * Decrement a key.
	 */
	function decrement( $key, $value = 1 ) {
		$key = $this->key($key);
		return parent::decrement( $key, $value );
	}//end decrement

	/**
	 * Delete a key.
	 */
	function delete( $key, $timeout = null ) {
		$key = $this->key($key);
		return parent::delete( $key, $timeout );
	}//end delete

	/**
	 * Get a key.
	 */
	function get( $key, $flags = null ) {
		$key = $this->key($key);
		return parent::get( $key, $flags );
	}//end get

	/**
	 * Increment an existing key.
	 */
	function increment( $key, $value = 1 ) {
		$key = $this->key($key);
		return parent::increment( $key, $value );
	}//end increment

	/**
	 * Generate a unique name for this key.
	 */
	public function key( $id ) {
		return self::NS . ":" . $this->prefix . ":" . $id;
	}//end key

	/**
	 * Replace an existing key, failing if key did not exist.
	 */
	function replace( $key, $var, $flag = null, $expire = null ) {
		$key = $this->key($key);
		return parent::replace( $key, $var, $flag, $expire );
	}//end replace

	/**
	 * Set a key, replacing any existing keys.
	 */
	function set( $key, $var, $flag = null, $expire = null ) {
		$key = $this->key($key);
		return parent::set( $key, $var, $flag, $expire );
	}//end set
}//end PSUMemcache
