<?php

namespace PSU;

/**
 * 
 */
interface ActiveRecord {
	/**
	 * Return a class instance based off a unique key.
	 *
	 * @sa row
	 *
	 * @param mixed $key The key to fetch by.
	 * @return object Return an instance of the static class.
	 */
	public static function get( $key );

	/**
	 * Return an array of record data for a unique key.
	 *
	 * @sa get
	 *
	 * @param mixed $key The key to fetch by
	 * @return array
	 */
	public static function row( $key );

	/**
	 * Save the current record.
	 *
	 * @param string $method The method to use during saving.
	 */
	public function save( $method = 'insert' );

	/**
	 * Delete the current record.
	 */
	public function delete();
}//end class ActiveRecord
