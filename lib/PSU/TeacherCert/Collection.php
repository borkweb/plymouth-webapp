<?php

namespace PSU\TeacherCert;

/**
 * 
 */
abstract class Collection extends \PSU\Collection {
	/**
	 * The identifier(s) which will be used to filter
	 * the rows in the collection. See _get_sql() for details.
	 */
	protected $_collection_key;

	/**
	 * The column name used to filter the collection.
	 *
	 * A collection is often made up of child records of another
	 * related object. In your subclass, set $parent_key to
	 * define the default parent column for the query. When you use
	 * Collection::get($id) where $id is scalar, the query
	 * will filter for `$parent_key = $id`.
	 *
	 * Example: checklist items are children of a gate. Set
	 * $parent_key to 'gate_id' to allow instantiation
	 * via `new ChecklistItems($gate_id)`. Results will auto-
	 * matically filter where `gate_id = $gate_id`.
	 */
	static $parent_key;

	static $join;

	static $child_key = 'id';

	/**
	 * Cache the result of _get_sql()
	 */
	protected $_get_sql_cache = null;

	public function __construct( $id = null ) {
		$this->_collection_key = $id;
	}//end __construct

	/**
	 *
	 */
	public function count() {
		list($sql, $args) = $this->_get_sql();
		$sql = "SELECT COUNT(1) FROM ($sql)";
		$count = \PSU::db('banner')->GetOne($sql);
		return $count;
	}//end count

	/**
	 * Cascade deletes down to ActiveRecord::delete(). Currently disabled.
	 */
	public function delete( $delete_id = null ) {
		throw new \Exception( 'delete is not implemented' );

		foreach( $this as $child ) {
			$child->delete( $delete_id );
		}
	}//end delete

	/**
	 * exclude an iterator by Constituent
	 * 
	 * @param $sau \b Constituent id, object, or slug
	 * @param $it \b iterator object
	 */
	public function exclude_by_constituent( $item, $it = null ) {
		return $this->exclude_by_validation( '\PSU\TeacherCert\ValidationFilterIterator\Constituent', $item, $it );
	}//end exclude_by_constituent

	/**
	 * exclude an iterator by District
	 * 
	 * @param $sau \b District id, object, or slug
	 * @param $it \b iterator object
	 */
	public function exclude_by_district( $item, $it = null ) {
		return $this->exclude_by_validation( '\PSU\TeacherCert\ValidationFilterIterator\District', $item, $it );
	}//end exclude_by_district

	/**
	 * exclude an iterator by SAU
	 * 
	 * @param $sau \b SAU id, object, or slug
	 * @param $it \b iterator object
	 */
	public function exclude_by_sau( $item, $it = null ) {
		return $this->exclude_by_validation( '\PSU\TeacherCert\ValidationFilterIterator\SAU', $item, $it );
	}//end exclude_by_sau

	/**
	 * exclude an iterator by SAU
	 * 
	 * @param $sau \b SAU id, object, or slug
	 * @param $it \b iterator object
	 */
	public function exclude_by_school( $item, $it = null ) {
		return $this->exclude_by_validation( '\PSU\TeacherCert\ValidationFilterIterator\School', $item, $it );
	}//end exclude_by_school

	/**
	 * exclude an iterator by School Approval Level
	 * 
	 * @param $item \b School Approval Level id, object, or slug
	 * @param $it \b iterator object
	 */
	public function exclude_by_school_approval_level( $item, $it = null ) {
		return $this->exclude_by_validation( '\PSU\TeacherCert\ValidationFilterIterator\SchoolApprovalLevel', $item, $it );
	}//end exclude_by_school_approval_level

	/**
	 * exclude an iterator by School Type
	 * 
	 * @param $item \b School Type id, object, or slug
	 * @param $it \b iterator object
	 */
	public function exclude_by_school_type( $item, $it = null ) {
		return $this->exclude_by_validation( '\PSU\TeacherCert\ValidationFilterIterator\SchoolType', $item, $it );
	}//end exclude_by_school_type

	/**
	 * get an iterator by excluding via Validation
	 * 
	 * @param $class_name \b full class name for object used in iterator
	 * @param $item \b Object id, object, or slug
	 * @param $it \b iterator object
	 * @param $inverse \b FALSE = include $item associations.  TRUE = exclude $item associations
	 */
	public function exclude_by_validation( $class_name, $item, $it = null ) {
		return $this->get_by_validation( $class_name, $item, $it, TRUE );
	}//end get_by_validation

	/**
	 *
	 */
	public static function cached_get( $keys = null ) {
		static $cache = array();
		$class = get_called_class();
		$key = serialize($keys);

		if( ! isset($cache[$class]) ) {
			$cache[$class] = array();
		}

		if( ! isset($cache[$class][$key]) ) {
			$cache[$class][$key] = new static( $keys );
		}
		
		return $cache[$class][$key];
	}//end cached_get

	/**
	 * @sa _get_sql()
	 */
	public function get( $keys = null ) {
		list($sql, $args) = $this->_get_sql( $keys );

		$rset = \PSU::db('banner')->Execute( $sql, $args );

		return $rset;
	}//end get

	/**
	 * Return the SQL command to fetch our child rows.
	 */
	protected function _get_sql( $keys = null ) {
		if( $this->_get_sql_cache ) {
			return $this->_get_sql_cache;
		}

		$table = static::$table;
		$parent_key = static::$parent_key;
		$join = static::$join;

		//
		// set up WHERE clause
		//

		$where_sql = array('1=1');
		$args = array();

		if( null !== $this->_collection_key && ! isset( $keys ) ) {
			if( is_array( $this->_collection_key ) ) {
				$keys = $this->_collection_key;
			} else {
				$keys = array( $parent_key => $this->_collection_key );
			}
		}

		foreach( (array) $keys as $key => $value ) {
			$where = '';

			if( is_array($value) && count($value) == 1 ) {
				$value = array_pop($value);
			}

			if( is_array( $value ) ) {
				// array of values; use IN()
				$tmp = array();
				foreach( $value as $v ) {
					$tmp[] = \PSU::db('banner')->qstr( $v );
				}
				$value = implode(', ', $tmp);

				$where .= "{$table}.{$key} IN ({$value})";
			} else {
				// simple scalar value
				$value = \PSU::db('banner')->qstr( $value );
				if( strpos( $key, '.' ) === false ) {
					$where .= "{$table}.{$key} = {$value}";
				} else {
					$where .= "{$key} = {$value}";
				}//end if
			}

			$where_sql[] = $where;
		}

		// Ignore deleted records
		$where_sql[] = "{$table}.delete_id IS NULL";

		//
		// set up ORDER clause
		//

		$order_sql = $this->_get_order();

		if( $order_sql ) {
			$order_sql = "ORDER BY {$order_sql}";
		}

		//
		// do the query
		//

		$where_sql = implode( ' AND ', $where_sql );

		$join_tables = array();
		if( $join ) {
			$join_sql = "";
			foreach( $join as $join_data ) {
				$join_tables[] = str_replace('psu_teacher_cert.', '', $join_data['table']);
				$join_sql .= " {$join_data['type']} {$join_data['table']} ON 1=1 " ;
				foreach( $join_data['fields'] as $fieldset ) {
					$join_sql .= "{$fieldset['logic']} {$fieldset['field1']} = {$fieldset['field2']} "; 
				}//end foreach
			}//end foreach
		}//end if

		if( $join_tables ) {
			$join_tables = ',' . implode( '.*, ', $join_tables ) .'.* ';
		} else {
			unset( $join_tables );
		}//end else

		$sql = "
			SELECT {$table}.* {$join_tables}
			FROM psu_teacher_cert.{$table} {$join_sql}
			WHERE {$where_sql} {$order_sql}
		";

		$this->_get_sql_cache = array($sql, $args);

		return $this->_get_sql_cache;
	}//end _get_sql

	/**
	 * get an iterator by Constituent
	 * 
	 * @param $sau \b Constituent id, object, or slug
	 * @param $it \b iterator object
	 */
	public function get_by_constituent( $item, $it = null ) {
		return $this->get_by_validation( '\PSU\TeacherCert\ValidationFilterIterator\Constituent', $item, $it );
	}//end get_by_constituent

	/**
	 * get an iterator by District
	 * 
	 * @param $sau \b District id, object, or slug
	 * @param $it \b iterator object
	 */
	public function get_by_district( $item, $it = null ) {
		return $this->get_by_validation( '\PSU\TeacherCert\ValidationFilterIterator\District', $item, $it );
	}//end get_by_district

	/**
	 * get an iterator by SAU
	 * 
	 * @param $sau \b SAU id, object, or slug
	 * @param $it \b iterator object
	 */
	public function get_by_sau( $item, $it = null ) {
		return $this->get_by_validation( '\PSU\TeacherCert\ValidationFilterIterator\SAU', $item, $it );
	}//end get_by_sau

	/**
	 * get an iterator by SAU
	 * 
	 * @param $sau \b SAU id, object, or slug
	 * @param $it \b iterator object
	 */
	public function get_by_school( $item, $it = null ) {
		return $this->get_by_validation( '\PSU\TeacherCert\ValidationFilterIterator\School', $item, $it );
	}//end get_by_school

	/**
	 * get an iterator by School Approval Level
	 * 
	 * @param $item \b School Approval Level id, object, or slug
	 * @param $it \b iterator object
	 */
	public function get_by_school_approval_level( $item, $it = null ) {
		return $this->get_by_validation( '\PSU\TeacherCert\ValidationFilterIterator\SchoolApprovalLevel', $item, $it );
	}//end get_by_school_approval_level

	/**
	 * get an iterator by School Type
	 * 
	 * @param $item \b School Type id, object, or slug
	 * @param $it \b iterator object
	 */
	public function get_by_school_type( $item, $it = null ) {
		return $this->get_by_validation( '\PSU\TeacherCert\ValidationFilterIterator\SchoolType', $item, $it );
	}//end get_by_school_type

	/**
	 * get an iterator by Validation
	 * 
	 * @param $class_name \b full class name for object used in iterator
	 * @param $item \b Object id, object, or slug
	 * @param $it \b iterator object
	 * @param $inverse \b FALSE = include $item associations.  TRUE = exclude $item associations
	 */
	public function get_by_validation( $class_name, $item, $it = null, $inverse = false ) {
		if( ! $it ) {
			$it = $this->getIterator();
		}//end if

		return new $class_name( $item, $it, $inverse );
	}//end get_by_validation

	public function sort( $callback ) {
		$this->load();
		usort( $this->children, $callback );
	}

	/**
	 * Column list for ordering the result set.
	 */
	protected function _get_order() {
		return null;
	}//end _get_order
}//end class Collection
