<?php

namespace PSU\TeacherCert;

/**
 * Simple helper cache to reduce queries against the database. Caches
 * the contents of a table, and fetches single rows of the table by
 * key on demand. Example usage:
 *
 *     $rowcache = new TeacherCert\RowCache;
 *     $rowcache->cache( 'PSU\TeacherCert\ChecklistItem' );
 *     TeacherCert\ActiveRecord::$rowcache = $rowcache;
 */
class RowCache {
	/**
	 * Internal cache.
	 */
	protected $cache = array();

	/**
	 * Prime the cache for a class, indexing by the requested fields.
	 *
	 * @param string $class The class whose records should be cached.
	 * @param array $fields Record field(s) to cache by.
	 */
	public function cache( $class, $fields = 'id', $where = '' ) {
		// force to array
		if( is_string($fields) ) {
			$fields = array($fields);
		}

		if( ! isset($this->cache[$class]) ) {
			$this->cache[$class] = array();
		}

		if( $where ) {
			$where = "WHERE $where";
		}

		$table = $class::$table;
		$sql = "SELECT * FROM psu_teacher_cert.{$table} {$where}";
		$rset = \PSU::db('banner')->Execute( $sql );

		foreach( $rset as $row ) {
			foreach($fields as $field ) {
				$value = $row[$field];
				$key = "$field-$value";
				$this->cache[$class][$key] = $row;
			}
		}
	}//end cache

	/**
	 * Return a cached record, or null if no record was found.
	 *
	 * @param string $class Class to fetch
	 * @param string $field Field to query by
	 * @param string $value Value to search for
	 * @return array
	 */
	public function get( $class, $field, $value ) {
		if( ! isset( $this->cache[$class] ) ) {
			return null;
		}

		$key = "$field-$value";

		if( ! isset( $this->cache[$class][$key] ) ) {
			return null;
		}

		return $this->cache[$class][$key];
	}//end get
}//end class RowCache
