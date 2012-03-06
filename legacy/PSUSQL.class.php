<?php

class PSUSQL
{
	/**
	 * Arguments used to instantiate this object along with defaults
	 */
	public $args;

	/**
	 * Bind variables
	 */
	public $bind;

	/**
	 * Array to hold fields that can be bound
	 */
	public $bind_registry;

	/**
	 * Report database registry handle
	 */
	public $database = 'banner';

	/**
	 * enables debug output if set
	 */
	public $debug = false;

	/**
	 * A string containing the SQL of the report
	 */
	public $execute_sql;

	/**
	 * Report id
	 */
	public $id;

	/**
	 * field to index results by
	 */
	public $key;

	/**
	 * An array of the parameters for execution of the report
	 */
	public $params;

	/**
	 * Tracks whether params have changed
	 */
	public $params_changed = false;

	/**
	 * Report data
	 */
	public $records;

	/**
	 * A string containing the SQL of the report
	 */
	public $sql;
	
	/**
	 * Constructor.
	 * @param $id \b id of the report
	 * @param $args \b arguments for the instantiation of this report
	 */
	public function __construct( $sql, $args = null ){
		$default_args = array(
			'database' => 'banner'
		);

		$this->args = $args ? PSU::params($args, $default_args) : array();

		$this->database = $this->args['database'];

		$this->sql = str_replace('%', '%%', $sql);

		$this->sql = "SELECT * FROM (".$this->sql.") psusqltable WHERE 1=1 %s";

		// if there isn't a placeholder for WHERE addons within the query, add one
		/*
		if( strpos( $this->sql, '%s' ) === false ) {
			$where_insert = stripos( $this->sql, 'WHERE' ) === false ? ' WHERE 1 = 1 %s ' : ' %s ';
			$group_index = stripos( $this->sql, 'GROUP BY' );
			$order_index = stripos( $this->sql, 'ORDER BY' );
			if( $group_index === false ) {
				if( $order_index === false ) {
					$this->sql .= $where_insert;
				} else {
					$this->sql = substr( $this->sql, 0, $order_index ) . $where_insert . substr( $this->sql, $order_index );
				}//end else
			} else {
					$this->sql = substr( $this->sql, 0, $group_index ) . $where_insert . substr( $this->sql, $group_index );
			}//end else
		}//end if
		 */

		$this->where_addon = '';

		if($this->args){
			$this->id = $this->args['id'];
			$this->key = $this->args['key'];
			$this->bind_registry = $this->args['bind_registry'] ? $this->args['bind_registry'] : array();
			$this->bind_registry = array_unique(array_merge( $this->bind_registry, array_keys( (array) $_GET ) ));

			if( $this->args['params'] ){
				$this->_add_parameters( $this->args['params'] );
			}//end if
		}//end if
	}//end constructor

	public function addWhere($where, $bind = array()){
		$this->bind = array_merge( (array) $this->bind, $bind );
		$this->where_addon .= ' '.$where;
	}//end addWhere

	/*
	 * allow for CacheExecute of ADOdb calls
	 */
	public static function cacheexecute($sql, $bind = null, $key = null, $database = 'banner'){
		return self::execute($sql, $bind, $key, $database, 'CacheExecute');
	}//end CacheExecute

	/*
	 * allow for CacheGetAll of ADOdb calls.
	 */
	public static function cachegetall($sql, $bind = null, $key = null, $database = 'banner'){
		return self::execute($sql, $bind, $key, $database, 'CacheGetAll');
	}//end CacheGetAll

	/*
	 * allow for CacheGetOne of ADOdb calls.
	 */
	public static function cachegetone($sql, $bind = null, $key = null, $database = 'banner'){
		return self::execute($sql, $bind, $key, $database, 'CacheGetOne');
	}//end CacheGetOne

	/*
	 * allow for CacheGetRow of ADOdb calls.
	 */
	public static function cachegetrow($sql, $bind = null, $key = null, $database = 'banner'){
		return self::execute($sql, $bind, $key, $database, 'CacheGetRow');
	}//end CacheGetRow

	/**
	 * Executes the report and returns the data
	 */
	public function cols( ) {
		if( !$this->sql ){
			throw new Exception('SQL for this report is not set');
		}//end if

		if( $this->debug ){
			PSU::db($this->database)->debug = $this->debug;
		}//end if

		$this->prepSQL();

		// retrieve the results
		$this->execute($this->execute_sql, $this->final_bind, $this->key, $this->database, 'Execute', true, true);

		return $this->cols;
	}//end cols

	public function prepSQL(){
		$database_type = PSU::db( $this->database )->databaseType;
		$bind_regex = '/:([a-zA-Z0-9_]+)/';

		$this->execute_sql = sprintf($this->sql, $this->where_addon);

		$this->final_bind = array();

		if( !in_array( $database_type, array('oci8','oci8po') ) && preg_match_all($bind_regex, $this->execute_sql, $matches) ) {
			foreach( $matches[1] as $match ) {
				$this->final_bind[] = $this->bind[ $match ];
			}//end foreach
			// update the SQL statement to use question marks
			$this->execute_sql = preg_replace($bind_regex, '?', $this->execute_sql);
		} else {
			$this->final_bind = $this->bind;
		}//end else
	}//end prepSQL

	/**
	 * Execute a SQL statement.  If it is a SELECT, the resulting records are stored in one of two ways:
	 *   1) if called statically, the records are stored in a static variable
	 *   2) if called non-statically, the records are stored in $this->records
	 *   If the statement/bind/key/database combination has already been executed, execute simply
	 *   returns the cached records rather than re-runs the query
	 *
	 * @param $sql \b sql statement to execute
	 * @param $bind \b bind variable array
	 * @param $key \b field to use as the array key for the records
	 * @param $database \b database registry identifier (see PSUTools) used for query execution
	 * @param $command \b ADOdb command to execute
	 * @param $return_results \b return the resultset (true) or the records (false)
	 */
	public function execute($sql, $bind = null, $key = null, $database = 'banner', $command = 'Execute', $return_results = false, $column_retrieval = false){
		static $query_cache;

		$db =& PSU::db($database);

		// if this is not a select, execute the statement and return the results
		if( stripos($sql, 'select') !== 0){
			return $db->$command($sql, $bind);
		}//end if

		// if this method was called statically, cache the query
		$static = !(isset($this) && get_class($this) == __CLASS__);

		if($bind){
			$bind = PSU::params($bind);
		}//end if

		if( $column_retrieval ) {
			if( $this->cols ) {
				$results = $this->cols;
			} else {
				if( $results = $db->SelectLimit( $sql, 1, -1, $bind ) ) {
					$field_types = $results->FieldTypesArray();

					foreach( $field_types as $field ){
						$this->cols[ $field->name ] = $field;
					}//end foreach

					$results = $this->cols;
				} else {
					$results = array();
				}//end else
			}//end else
		} else {
			
			// initialize the query_cache if necessary
			if( !is_array($query_cache) ){
				$query_cache = array();
			}//end if

			// create a unique index for the query cache
			$index = md5($sql . ($bind ? http_build_query((array) $bind) : null) . $key . $database);

			// if the query - regardless of how it was called - is in the query_cache, return the data
			if( $query_cache[$index] && !$return_results){
				if( $static ){
					return $query_cache[$index];
				} else {
					return $this->records = $query_cache[$index];
				}//end else
			}//end if

			$records = array();

			if( $command == 'CachePageExecute') {
				$results = $db->$command( 90, $sql, $this->paging, $_GET['page'], $bind );
			} elseif( $command == 'PageExecute' ) {
				$results = $db->$command( $sql, $this->paging, $_GET['page'], $bind );
			} else {
				$results = $db->$command( $sql, $bind );
			}//end else

			if( $results ){
				if( !$static ) {
					if( !$this->cols ) {
						$this->cols = array();

						$field_types = $results->FieldTypesArray();

						foreach( $field_types as $field ){
							$this->cols[ $field->name ] = $field;
						}//end foreach
					}//end if

					$this->results = $results;	
				}//end if	

				if( $return_results ){
					return $results;
				}//end if

				if( $results instanceof ADORecordSet ){
					$records = self::parse_results($results, $key);
				}//end if
			}//end if

			if( $static ){
				$query_cache[$index] = $records;	
			}//end if
		}//end else
		return $records;
	}//end execute

	/*
	 * allow for GetAll of ADOdb calls.
	 */
	public static function getall($sql, $bind = null, $key = null, $database = 'banner'){
		return self::execute($sql, $bind, $key, $database, 'GetAll');
	}//end GetAll

	/*
	 * allow for GetOne of ADOdb calls.
	 */
	public static function getone($sql, $bind = null, $key = null, $database = 'banner'){
		return self::execute($sql, $bind, $key, $database, 'GetOne');
	}//end GetOne

	/*
	 * allow for GetRow of ADOdb calls.
	 */
	public static function getrow($sql, $bind = null, $key = null, $database = 'banner'){
		return self::execute($sql, $bind, $key, $database, 'GetRow');
	}//end GetRow

	/**
	 * adds/updates a param
	 */
	public function param( $key, $value ){
		$this->_add_parameters( array( $key => $value ) );
	}//end param

	/**
	 * parse resultset for records
	 *
	 * @param $rset \b ADOdbRecordSet
	 * @param $key \b key to index the record of of
	 * @param $id \b id of the registered sql statement. 
	 *        If this id is set, for each $row, the psusql_{id} filter 
	 *        is applied if the filter exists in the registry
	 */
	public function parse_results(ADORecordset $rset, $key = null, $id = null){
		$records = array();
		foreach( $rset as $row ){
			if( $id ){
				if(PSU::has_filter('psusql_'.$id.'_parse_results')){
					$row = PSU::apply_filters('psusql_'.$id.'_parse_results', $row);
				}//end if
			}//end if

			if( $key ){
				$records[ $row[ $key ] ] = $row;
			} else {
				$records[] = $row;
			}//end else
		}//end foreach

		if(PSU::has_filter('psusql_'.$id.'_records')){
			$records = PSU::apply_filters('psusql_'.$id.'_records', $records);
		}//end if

		$this->count = count($records);

		return $records;
	}//end parse_results

	/*
	 * allow for CacheExecute of ADOdb calls and return the result set
	 */
	public static function rscacheexecute($sql, $bind = null, $key = null, $database = 'banner'){
		return self::execute($sql, $bind, $key, $database, 'CacheExecute', true);
	}//end rsCacheExecute

	/*
	 * allow for Execute of ADOdb calls and return the result set
	 */
	public static function rsexecute($sql, $bind = null, $key = null, $database = 'banner'){
		return self::execute($sql, $bind, $key, $database, 'Execute', true);
	}//end rsExecute

	/**
	 * Executes the report and returns the data
	 */
	public function run( $return_results = false ) {
		if( !$this->params_changed && $this->records ){
			return $this->records;
		}//end if

		if( !$this->sql ){
			throw new Exception('SQL for this report is not set');
		}//end if

		if( $this->debug ){
			PSU::db($this->database)->debug = $this->debug;
		}//end if

		$this->prepSQL();

		// retrieve the results
		$this->results = $this->execute($this->execute_sql, $this->final_bind, $this->key, $this->database, ($this->paging ? 'CachePageExecute' : ($GLOBALS['NO_CACHE_EXECUTE'] || $this->args['nocache'] ? 'Execute' : 'CacheExecute')), true);

		// parse the result set into records
		if($this->results instanceof ADORecordSet){
			if( $this->paging ) {
				$this->pagination = PSU::paginationInfo($_GET, $this->results);
			}//end if

			$this->records = $this->parse_results($this->results, $this->key, $this->id);
		}//end if

		return $return_results ? $this->results : $this->records;
	}//end run

	/**
	 * Adds parameters into the params property
	 */
	public function _add_parameters( $params = null ){
		foreach( (array) $params as $key => $param ){
			// verify the param has actually changed before bothering with 
			// setting variables
			if( $this->params[ $key ] != $param ){
				$this->params[$key] = $param;

				if( in_array($key, (array) $this->bind_registry) ){
					$this->bind[$key] = $param;
				}//end if

				// if the parameter has changed, indicate so
				$this->params_changed = true;
			}//end if
		}//end foreach
	}//end _parse_parameters

	/**
	 * Magic getter
	 */
	public function __get( $property ) {
		if( $property == 'data' || $property == 'results' || $property == 'count' || $property == 'pagination'){
			// if the data element is being retrieved and either the data element is 
			// null OR the parameters have changed, re-run the report
			if( empty($this->records) || $this->params_changed ){
				$return = $this->run($property == 'results' ? true : false);

				if( $property == 'count' ) {
					return $this->count;
				} elseif( $property == 'pagination' ) {
					return $this->pagination;
				}//end else
			}//end if
			else{
				if( $property == 'count' ) {
					return $this->count;
				} elseif( $property == 'pagination' ) {
					return $this->pagination;
				}//end else

				return $property == 'results' ? $this->results : $this->records;
			}//end else
			return $return;
		} elseif( $property == 'cols' ) {
			$return = $this->cols( );

			return $return;
		}//end if
	}//end __get
}//end class PSUSQL
