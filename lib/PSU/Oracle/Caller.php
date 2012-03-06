<?php

class PSU_Oracle_Caller {
	public $alias = array();
	public $db;
	public $id;
	public $output = array();

	public function __construct( $db = null ) {
		if( !$db ) {
			$db = PSU::db('banner');
		}//end if

		$this->db = $db;
	}//end __construct

	public function alias( $alias, $field ) {
		$this->alias[ $alias ] = $field;

		return $this;
	}//end alias

	public function execute( $sql, $in, $out, $id = null ) {
		if( $sql ) {
			$dml = "BEGIN \n";
			$dml .= $sql;
			$dml .= " END;";

			$stmt = $this->db->PrepareSP($dml);

			// prepare the in parameters
			foreach( $in as $key => &$value ) {
				$this->db->InParameter($stmt, $value, $key);
			}//end foreach

			// prepare the out parameters
			foreach( $out as $key ) {
				$this->db->OutParameter($stmt, $this->output[ $key ], $key);
			}//end foreach

			// execute the dml
			$results = $this->db->Execute($stmt);

			$old_out = $this->output;
			$this->output = array();
			if( $old_out ) {
				foreach( $old_out as $key => $value ) {
					$this->output[ preg_replace( '/^return_/', '', $key ) ] = $value;
				}//end foreach
			}//end if

			return $results;
		}//end if

		return true;
	}//end execute

}//end PSU_Oracle_Caller
