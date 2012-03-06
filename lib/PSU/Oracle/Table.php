<?php

class PSU_Oracle_Table extends PSU_DataObject {
	public $aliases = array();
	private $columns = null;
	static $table_regex = '/^((?<owner>[a-zA-Z0-9_]+)\.)?(?<table>[a-zA-Z0-9_]+)(@(?<link>[a-zA-Z0-9_]+))?$/';

	/**
	 * constructor
	 *
	 * @param $table \b table the update/insert will be done against
	 * @param $db \b database to connect to
	 */
	public function __construct( $table, $db = 'banner') {
		$this->db = $db;

		// retrieve the possible pieces of the table
		$parsed = self::name_parse( $table );

		$this->name = $parsed['table'];
		$this->owner = $parsed['owner'];
		$this->link = $parsed['link'];

		parent::__construct( $this->get() );
	}//end constructor

	/**
	 * Given an attribute, this returns an array of column 
	 * attribute values for the table indexed by column.
	 *
	 * @param $attribute \b column attribute
	 */
	public function column_attribute_by_column( $attribute ) {
		$data = array();

		foreach( $this->columns() as $column ) {
			$data[ $column->column_name ] = $column->$attribute;
		}//end foreach

		return $data;
	}//end column_attribute_by_column

	/**
	 * retrieves the columns for the given table
	 */
	public function columns() {
		if( $this->columns === null ) {
			$this->columns = new PSU_Oracle_Columns( $this->name, $this->db );
			$this->columns->load();
		}//end if

		return $this->columns;
	}//end columns

	/**
	 * Given an attribute, this returns an array of distinct column 
	 * attribute values for the table.
	 *
	 * @param $attribute \b column attribute
	 */
	public function distinct_column_attribute( $attribute ) {
		$data = array();

		foreach( $this->columns() as $column ) {
			$data[ $column->$attribute ] = $column->$attribute;
		}//end foreach

		return $data;
	}//end distinct_column_attribute

	/**
	 * retrieves info about the table
	 */
	public function get() {
		$query_args = array( 'the_table' => $this->name );

		$sql = "
			SELECT t.*,
			       LOWER(table_name) table_name
			  FROM all_tables t
			 WHERE table_name = UPPER(:the_table)
		";

		if( $this->owner ) {
			$sql .= " AND owner = UPPER(:the_owner)";
			$query_args['the_owner'] = $this->owner;
		}//end if

		if( ! ($row = PSU::db( $this->db )->GetRow( $sql, $query_args )) ) {
			// if no fields were returned, this was an invalid table
			throw new Exception($this->name.' is not a valid table name');
		}//end if

		return $row;
	}//end get

	/**
	 * timestamp of last analysis
	 */
	public function last_analyzed_timestamp() {
		return strtotime( $this->last_analyzed );
	}//end last_analyzed_timestamp

	/**
	 * returns the non-null columns for the table
	 */
	public function required_columns() {
		return $this->columns()->required();
	}//end required_columns

	/**
	 * parse table name with a regex!
	 */
	public static function name_parse( $name ) {
		preg_match( self::$table_regex, $name, $matches);

		return $matches;
	}//end name_parse

	/**
	 * validates a record against the table
	 */
	public function validate( $args, $strip = '' ) {
		return $this->columns()->validate( $args, $strip );
	}//end validate
}//end class PSU_Oracle_Table
