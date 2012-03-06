<?php
/**
 * PSUScriptUtility.class.php
 *
 * === Modification History ===<br/>
 * 1.0.0  12-dec-2005  [mtb]  original<br/>
 *
 * @package 		Tools
 */

/**
 * PSUScriptUtility.class.php
 *
 * PSU Quick Script Utility
 *
 * @version		1.0.0
 * @author		Matthew Batchelder <mtbatchelder@plymouth.edu>
 * @copyright 2007, Plymouth State University, ITS
 */ 

class PSUScriptUtility
{
	var $table;

	/**
	 * buildParams
	 *
	 * parses and builds a parameter list
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		mixed $params Parameter list (either query string or array)
	 * @param  		boolean $require Require primary fields?
	 * @return    mixed
	 */
	function buildParams($params,$require = true)
	{
		if(!is_array($params))
		{
			parse_str($params,$params);
		}//end if
		
		if($require)
		{
			if(!$params['primary_field'] || !$params['primary_field_data'])
			{
				return false;
			}//end if
		}//end if
		
		return $params;
	}//end buildParams

	/**
	 * delete
	 *
	 * deletes records based on the passed parameters
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		mixed $params Parameter list (either query string or array)
	 * @return    boolean
	 */
	function delete($params)
	{
		$params = $this->buildParams($params);
		if(!$params) return false;

		$where = '';
		$args = array(
			'script' => $this->script,
			'primary_field' => $params['primary_field'],
			'primary_field_data' => $params['primary_field_data']
		);
		
		if($params['field'])
		{
			$where = "AND field_data = :field ";
			if( $params['field_data'] && $params['field_data'] != "NULL" )
			{
				$args['field'] = $params['field_data'];
			}
			else
			{
				$args['field'] = null;
			}
		}//end if
		
		if($params['flag'])
		{
			$where .= "AND flag = :flag ";
			if( $params['flag'] && $params['flag'] != "NULL" )
			{
				$args['flag'] = $params['flag'];
			}
			else
			{
				$args['flag'] = null;
			}
		}//end if

		$sql = "DELETE FROM {$this->table['utility']} 
		              WHERE script = :script
		                AND primary_field = :primary_field
		                AND primary_field_data = :primary_field_data
		                    $where";
		return $this->db->Execute($sql, $args);
	}//end delete

	/**
	 * insert
	 *
	 * insert records with the passed parameters
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		mixed $params Parameter list (either query string or array)
	 * @return    boolean
	 */
	function insert($params)
	{
		$params = $this->buildParams($params);
		if(!$params) return false;

		$args = array(
			'script' => $this->script,
			'primary_field' => $params['primary_field'],
			'primary_field_data' => $params['primary_field_data'],
			'field' => $params['field'] ? $params['field'] : null,
			'field_data' => $params['field_data'] && $params['field_data'] != 'NULL' ? $params['field_data'] : null,
			'flag' => $params['flag'] ? $params['flag'] : null,
		);
		
		$sql = "INSERT INTO {$this->table['utility']} (
							script, 
							primary_field, 
							primary_field_data, 
							field, 
							field_data, 
							flag,
							activity_date
						) 
						VALUES 
						(
							:script,
							:primary_field,
							:primary_field_data,
							:field,
							:field_data,
							:flag,
							sysdate
						)";
		return $this->db->Execute($sql, $args);
	}//end insert

	/**
	 * purge
	 *
	 * purge records that belong to the script
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @return    boolean
	 */
	function purge()
	{
		$sql = "DELETE FROM {$this->table['utility']} WHERE script = :script";
		$args = array('script' => $this->script);
		return $this->db->Execute($sql, $args);
	}//end purge

	/**
	 * select
	 *
	 * select records with the passed parameters.  combines multiple records with identical primary
	 *    keys into a single record.
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		mixed $params Parameter list (either query string or array)
	 * @return    mixed
	 */
	function select($params = array())
	{
		$params = $this->buildParams($params, false);

		$where = '';
		$args = array('script' => $this->script);

		if($params['primary_field'])
		{
			$where .= "AND primary_field = :primary_field AND primary_field_data = :primary_field_data ";

			$args['primary_field'] = $params['primary_field'];
			$args['primary_field_data'] = $params['primary_field_data'] && $params['primary_field_data'] != "NULL" ? $params['primary_field_data'] : null;
		}//end if

		if($params['field'])
		{
			$where .= "AND field = :field AND field_data = :field_data ";

			$args['field'] = $params['field'];
			$args['field_data'] = $params['field_data'] && $params['field_data'] != "NULL" ? $params['field_data'] : null;
		}//end if
		
		if( $params['flag'] === null ) {
			$where .= "AND flag IS NULL ";
		} else {
			$where .= "AND flag = :flag ";
			$args['flag'] = $params['flag'] && $params['flag'] != "NULL" ? $params['flag'] : null;
		}//end else
		
		$data = array();

		$sql = "SELECT * FROM {$this->table['utility']} WHERE script = :script $where";
		if($results = $this->db->Execute($sql, $args))
		{
			while($row = $results->FetchRow())
			{
				$row = PSUTools::cleanKeys('','',$row);
				if(!isset($data[$row['primary_field_data']]))
				{
					$data[$row['primary_field_data']] = array();
				}//end if
				$data[$row['primary_field_data']][$row['primary_field']] = $row['primary_field_data'];
				$data[$row['primary_field_data']][$row['field']] = $row['field_data'];
				$data[$row['primary_field_data']][$row['field'].'_flag'] = $row['flag'];
			}//end while
		}//end if	
		return $data;
	}//end select

	/**
	 * sets a record's flag based on the passed parameters
	 *
	 * @param  		mixed $params Parameter list (either query string or array)
	 * @param $flag \b String containing 1 character flag
	 * @return    boolean
	 */
	function set_flag( $params, $flag ) {
		$params = $this->buildParams($params);
		if(!$params) return false;

		$where = '';
		$args = array(
			'script' => $this->script,
			'primary_field' => $params['primary_field'],
			'primary_field_data' => $params['primary_field_data'],
			'set_flag' => $flag,
		);
		
		if($params['field']) {
			$where = "AND field_data = :field ";
			if( $params['field_data'] && $params['field_data'] != "NULL" ) {
				$args['field'] = $params['field_data'];
			} else {
				$args['field'] = null;
			}
		}//end if
		
		if($params['flag']) {
			$where .= "AND flag = :flag ";
			if( $params['flag'] && $params['flag'] != "NULL" ) {
				$args['flag'] = $params['flag'];
			} else {
				$args['flag'] = null;
			}
		}//end if

		$sql = "UPDATE {$this->table['utility']} 
						   SET flag = :set_flag
							WHERE script = :script
								AND primary_field = :primary_field
								AND primary_field_data = :primary_field_data
										$where";
		return $this->db->Execute($sql, $args);
	}//end set_flag

	/**
	 * upload
	 *
	 * uploads a comma separated file and places the data into the script table
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param     string $file File to upload
	 * @param  		array $fields Field List
	 * @param     string $primary Primary field
	 * @param     boolean $purge Purge the existing data?
	 * @return    boolean
	 */
	function upload($file, $field_data, $purge = true)
	{
		//read in file
		$data = file($file);
		
		//has lines
		if(is_array($data))
		{
			//if this upload should purge data, purge it.
			if($purge) $this->purge();
			
			//initialize primary field's id to -1
			$primary_id = -1;
			
			//look through the field data to find the primary field
			foreach($field_data as $key => $field)
			{
				//if primary is set, we've found the primary field!
				if($field['primary']) $primary_id = $key;
			}//end foreach
			
			//if a primary field was not found, return false.
			if($primary_id == -1) return false;
			
			//loop over the lines of the uploaded file
			foreach($data as $line)
			{
				//explode on comma
				$fields = explode(',',str_replace('"','',$line));
				
				//loop over the field data
				foreach($field_data as $key=>$field)
				{
					//if this is not the primary field, prep for insertion.
					if($key != $primary_id)
					{
						//prep the line for insertion
						$line_data = array(
							'primary_field' => $field_data[$primary_id]['name'],
							'primary_field_data' => trim($fields[$primary_id]),
							'field' => $field['name'],
							'flag' => $field['flag']
						);
						
						//is the field being inserted a "computed" field...e.g. eval?
						if(is_array($field['data']))
						{
							//does this function require parameters?
							if(is_array($field['data']['params']))
							{
								//loop over parameters and see if there is anything that needs replacing
								foreach($field['data']['params'] as $param_key=>$param_data)
								{
									//yup.  Lets see if there is any replacing that needs to happen
									if(preg_match_all('/\%\%([A-Za-z0-9\_]*)\%\%/',$param_data,$matches))
									{
										//found a string to replace.  Lets loop over the matches
										foreach($matches[1] as $match)
										{
											$match_id = -1;
											
											//loop over available fields and find the field index for the match
											foreach($field_data as $match_key => $match_field)
											{
												//if the field name matches the "match", bookmark it!
												if($match_field['name']==$match) $match_id = $match_key;
											}//end foreach
											//do the replacement
											$field['data']['params'][$param_key] = str_replace('%%'.$match.'%%', trim($fields[$match_id]), $param_data);
										}//end foreach
									}//end if
								}//end foreach
							}//end if
							else
							{
								//no...initialize the function parameter to be blank
								$field['data']['params'] = array();
							}//end else
							
							//call the computed value function
							$field['data'] = call_user_func_array($field['data']['function'], $field['data']['params']);
							
							$line_data['field_data'] = $field['data'];
						}//end if
						elseif($field['data'])
						{
							$line_data['field_data'] = trim($field['data']);
						}//end else
						else
						{
							$line_data['field_data'] = trim($fields[$key]);
						}//end else
					
						$this->insert($line_data);
					}//end if
				}//end foreach
			}//end foreach
		}//end if
	}//end upload

	/**
	 * __construct
	 *
	 * Constructor
	 *
	 * @since		version 1.0.0
	 * @access		public
	 * @param  		ADOdb &$db ADOdb object
	 * @param     string $script Script
	 */
	function __construct(&$db, $script)
	{
		$this->db = $db;
		$this->script = $script;
		
		$this->table = array('utility' => 'psu.script_utility');		
	}//end constructor
}//end PSUScriptUtility
