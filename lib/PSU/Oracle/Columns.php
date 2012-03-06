<?php

class PSU_Oracle_Columns implements \IteratorAggregate, \ArrayAccess {
	public $db;
	public $table;
	public $owner;
	public $link;
	public $_key;
	static $columns = array();

	/**
	 * constructor
	 *
	 * @param $table \b table the update/insert will be done against
	 * @param $db \b database to connect to
	 */
	public function __construct( $table, $db = 'banner') {
		$this->db = $db;
		$this->_key = $table;

		// retrieve the possible pieces of the table
		$parsed = PSU_Oracle_Table::name_parse( $table );

		$this->table = $parsed['table'];
		$this->owner = $parsed['owner'];
		$this->link = $parsed['link'];
	}//end constructor

	/**
	 * retrieves a tables columns
	 */
	public function get() {
		// set up the column detail query
		$query_args = array( 'the_table' => $this->table );

		$sql = "
			SELECT c.*,
		         LOWER(column_name) column_name
				FROM all_tab_columns".($this->link ? '@'.$this->link : '')." c
			 WHERE table_name = UPPER(:the_table)
		";

		if( $this->owner ) {
			$sql .= " AND owner = UPPER(:the_owner)";
			$query_args['the_owner'] = $this->owner;
		}//end if

		$sql .= " ORDER BY column_id";

		if( ! ($results = PSU::db( $this->db )->Execute( $sql, $query_args )) ) {
			// if no fields were returned, this was an invalid table
			throw new Exception($this->table.' is not a valid table name');
		}//end if

		return $results;
	}//end get

	/**
	 * returns the table columns as an iterator
	 */
	public function getIterator() {
		return new ArrayIterator( self::$columns[ $this->_key ] );
	}//end getIterator

	/**
	 * assign the columns as records in the column array
	 */
	public function load( $rows = null ) {
		if( self::$columns[ $this->_key ] ) {
			return $this;
		}//end if

		if( !isset($rows) ) {
			$rows = $this->get();
		}//end if

		foreach( $rows as $row ) {
			self::$columns[ $this->_key ][ $row[ 'column_name' ] ] = new PSU_Oracle_Column( $row );
		}//end foreach

		return $this;
	}//end load

	/**
	 * ArrayAccess magic
	 */
	public function offsetExists( $offset ) {
		return isset( self::$columns[ $offset ] );
	}//end offsetExists

	/**
	 * ArrayAccess magic
	 */
	public function offsetGet( $offset ) {
		return isset( self::$columns[ $offset ] ) ? self::$columns[ $offset ] : null;
	}//end offsetGet

	/**
	 * ArrayAccess magic
	 */
	public function offsetSet( $offset, $value ) {
		if( is_null( $offset ) ) {
			self::$columns[] = $value;
		} else {
			self::$columns[ $offset ] = $value;
		}//end else
	}//end offsetSet

	/**
	 * ArrayAccess magic
	 */
	public function offsetUnset( $offset ) {
		unset( self::$columns[ $offset ] );
	}//end offsetUnset

	/**
	 * returns the required columns as an iterator
	 */
	public function required( $it = null ) {
		if( !$it ) {
			$it = $this->getIterator();
		}//end if

		return new PSU_Oracle_Columns_RequiredFilterIterator( $it );
	}//end required

	/**
	 * validates against given table
	 *
	 * @param $args \b associative array of fields to be validated
	 * @param $strip \b value to strip out of field names
	 */
	public function validate( $args, $strip = '' ) {
		if( is_object( $args ) ) {
			$args = get_object_vars( $args );
		}//end if

		foreach( $args as $field ) {
			// strip out any extra field cruft
			$field_name = str_replace( $strip, '', $field->column_name );

			$exception_start = 'The field "'.$field->column_name.( $field_name != $field->column_name ? ' ('.$field_name.')':'').'" in '.strtoupper($this->table).' must';

			if( !isset( $args[ $field_name ] ) ) {
				// the given field is missing from the passed in arguments
				if( $field->nullable == 'N' ) {
					// if the field is not nullable, throw an error
					throw new Exception($exception_start.' not be NULL');
				} else {
					// if the field is nullable, move to the next field
					continue;
				}//end else
			}//end if

			// validate the fields based on data type/length

			switch( $field->data_type ) {
				case 'CHAR':
				case 'VARCHAR2':
					if( strlen( $args[ $field_name ] ) > $field->data_length ) {
						throw new Exception($exception_start.' be between 1 and '.$field->data_length.' characters in length (inclusive)');
					}//end if
				break;
				case 'INT':
					$max_range = str_pad('', $field->data_length, '9');

					if( filter_var( $args[ $field_name ], FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 0, 'max_range' => $max_range ) ) ) === false ) {
						throw new Exception($exception.' be an integer between 0 and '.$max_range.' (inclusive)');
					}//end if
				break;
				case 'FLOAT':
				case 'NUMBER':
					$max_range = str_pad('', $field->data_length, '9');

					if( filter_var( $args[ $field_name ], FILTER_VALIDATE_FLOAT ) === false ) {
						throw new Exception($exception.' be a decimal number');
					}//end if
				break;
			}//end switch
		}//end foreach	

		return true;
	}//end validate
}//end class PSU_Oracle_Columns
