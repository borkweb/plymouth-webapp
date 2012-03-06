<?php

namespace PSU\TeacherCert;

/**
 * 
 */
abstract class ActiveRecord extends \PSU_Banner_DataObject implements \PSU\ActiveRecord {
	/**
	 * The table holding data for this object.
	 */
	static $table = null;

	/**
	 * A container for related objects (i.e. parents, children)
	 * @sa _get_related()
	 */
	protected $_related_objs = array();

	/**
	 * An array for keeping track of elements that have fallen over to a parent object
	 */
	public $failover = array();

	/**
	 * Optional, static RowCache. (Set at runtime.)
	 */
	static $rowcache = null;

	/**
	 *
	 */
	public function __construct( $row = null ) {
		// is row actually a row identifier (unique key)?
		if( $row && ! is_array( $row ) ) {
			$row = static::row( $row );
		}

		parent::__construct( $row );
	}//end __construct

	/**
	 * returns the activity date's timestamp
	 */
	public function activity_date_timestamp() {
		return $this->date_timestamp( 'activity_date' );
	}//end activity_date_timestamp

	/**
	 * returns the a date var's timestamp
	 */
	public function date_timestamp( $var ) {
		if( isset( $this->$var ) ) {
			if( is_numeric( $this->$var ) ) {
				return $this->$var;
			}

			return strtotime( $this->$var );
		}//end if

		return null;
	}//end date_timestamp

	/**
	 * Implement ActiveRecord::delete().
	 * 
	 * @param int $delete_id A preexisting delete_id coming from a parent delete. Currently unused, as deletes do not cascade.
	 */
	public function delete( $delete_id = null ) {
		$table_name = static::$table;

		$pidm = $_SESSION['pidm'];

		if( null === $delete_id ) {
			$sql = "INSERT INTO psu_teacher_cert.deletes (pidm, table_name) VALUES (:pidm, :table_name)";
			$args = compact( 'pidm', 'table_name' );
			\PSU::db('banner')->Execute( $sql, $args );

			$sql = "SELECT psu_teacher_cert.deletes_seq.currval FROM dual";
			$delete_id = \PSU::db('banner')->GetOne( $sql );
		}

		$this->delete_id = $delete_id;

		$sql = "UPDATE psu_teacher_cert.{$table_name} SET delete_id = :delete_id WHERE id = :the_id";
		$args = array( 'delete_id' => $this->delete_id, 'the_id' => $this->id );
		\PSU::db('banner')->Execute( $sql, $args );

		return $delete_id;
	}//end delete

	/**
	 * Get a single object of the static type.
	 */
	public static function get( $key ) {
		static $cache = array();

		if( ! is_scalar( $key ) ) {
			throw new \InvalidArgumentException( 'key must be scalar' );
		}

		$class = get_called_class(); // get_called_class() is full class name, including namespaces
		$field = self::_get_field( $key );
		$idx = "{$class}-{$field->field}-{$field->value}";

		if( ! isset($cache[$idx]) ) {
			$args = null;

			if( null !== self::$rowcache ) {
				$args = self::$rowcache->get( $class, $field->field, $field->value );
			}

			if( null === $args ) {
				$args = $key;
			}

			$obj = new static( $args );

			if( $obj->id ) {
				$cache["{$class}-id-{$obj->id}"] = $obj;
			}

			if( $obj->slug ) {
				$cache["{$class}-slug-{$obj->slug}"] = $obj;
			}
		}

		return $cache[$idx];
	}//end get

	/**
	 * Return a record array from the data store, identified
	 * by the given key
	 *
	 * @param mixed $key Id or slug
	 * @return array
	 */
	public static function row( $key ) {
		$field = self::_get_field( $key );

		$where = "{$field->field} = :key";
		$args  = array( 'key' => $field->value );

		if( ! static::$table ) {
			throw new \Exception( 'static::$table must be defined' );
		}

		$table = static::$table;

		$sql = "
			SELECT *
			FROM psu_teacher_cert.{$table}
			WHERE $where
		";

		$row = \PSU::db('banner')->GetRow( $sql, $args );

		if( 0 === count($row) ) {
			throw new \PSU\ActiveRecord\NotFoundException( "{$table}.{$field->field} = {$key}" );
		}

		return $row;
	}//end row

	public function save( $method = 'merge' ) {
		$args = $this->_prep_args();

		$table = static::$table;

		$this->validate("psu_teacher_cert.{$table}", $args);

		$fields = $this->_prep_fields( "psu_teacher_cert.{$table}", $args, true, false );

		$sql_method = "_{$method}_sql";
		$sql = $this->$sql_method( "psu_teacher_cert.{$table}", $fields );

		if( $results = \PSU::db('banner')->Execute( $sql, $args ) ) {
			if( ! $this->id || $this->failover['id'] ) {
				if( \PSU::db('banner')->GetOne("SELECT count(*) FROM all_objects WHERE owner = 'PSU_TEACHER_CERT' AND object_name = :the_name", array(
					'the_name' => strtoupper( $table . '_seq' ),
				))) {
					$sql = "SELECT psu_teacher_cert.{$table}_seq.currval FROM dual";
					$this->id = \PSU::db('banner')->GetOne( $sql );
				}//end if
			}
			return $this->id;
		}//end if

		return false;
	}//end save

	/**
	 * retrieve and cache validation data
	 */
	public function validation( $table, $field, $what ) {
		static $validation = array();
		if( $what == 'id' ) {
			return $this->$field;
		}//end if

		if( ! $validation[ $table ] ) {
			$sql = "SELECT * FROM psu_teacher_cert.{$table}";
			if( $results = \PSU::db('banner')->Execute( $sql ) ) {
				foreach( $results as $row ) {
					$types[ $row['id'] ] = $row;
				}//end foreach
			}//end if
		}//end if

		return $validation[ $table ][ $this->$field ][ $what ];
	}//end validation

	/**
	 * Helper to determine whether we are querying based on a numeric
	 * row ID or a slug.
	 * 
	 * @param string|int $ident The identifier.
	 * @return object An object with two properties: field (id, or slug) and value (the sanitized value).
	 */
	public static function _get_field( $ident ) {
		if( is_numeric($ident) ) {
			$field = 'id';
			$value = (int)$ident;
		} else {
			$field = 'slug';
			$value = $ident;
		}

		return (object)array( 'field' => $field, 'value' => $value );
	}//end _get_field

	/**
	 * @param string $key Identifier for this related object type.
	 * @param string $callback Class name or callback used to instantiate this object
	 * @param mixed $id Record identifer, or array of key/value identifier pairs
	 */
	protected function _get_related( $key, $callback, $id ) {
		$id_str = is_array($id) ? serialize($id) : $id;

		if( ! isset( $this->_related_objs[$key] ) ) {
			$this->_related_objs[$key] = array();
		}

		if( ! isset( $this->_related_objs[$key][$id_str] ) ) {
			if( is_callable( $callback ) ) {
				$obj = call_user_func( $callback, $id );
			} else {
				$obj = new $callback( $id );
			}

			$this->_related_objs[$key][$id_str] = $obj;
		}

		return $this->_related_objs[$key][$id_str];
	}

	/**
	 * merge record SQL
	 */
	protected function _merge_sql( $table, $fields, $on = null ) {
		if( ! $on ) {
			$on = array(
				'the_id',
			);
		}//end if

		return parent::_merge_sql( $table, $fields, $on, false );
	}//end _merge_sql
}//end class ActiveRecord
