<?php

/**
 * collects and executes Oracle-based API calls
 */
class PSU_Oracle_Batch extends PSU_Oracle_Caller {
	public $calls = array();

	public function __construct( $db = null ) {
		parent::__construct( $db );
	}//end constructor

	public function add( $id, PSU_Oracle_Call $call ) {
		$this->calls[ $id ] = $call;
	}//end add

	public function execute( $id = null ) {
		if( $id ) {
			$prep = $this->_prep_single( $id );
		} else {
			$prep = $this->_prep_all();
		}//end else

		return parent::execute( $prep['sql'], $prep['in'], $prep['out'], $id );
	}//end execute

	private function _prep_all() {
		$prep = array(
			'sql' => '',
			'in' => array(),
			'out' => array(),
		);

		foreach( $this->calls as $id => $call ) {
			$temp_prep = $this->calls[ $id ]->prep();
			$prep['sql'] .= $temp_prep['sql']."\n";
			$prep['in'] = array_merge( $prep['in'], $temp_prep['in'] );
			$prep['out'] = array_merge( $prep['out'], $temp_prep['out'] );
		}//end foreach

		return $prep;
	}//end _prep_all

	private function _prep_single( $id ) {
		return $this->calls[ $id ]->prep();
	}//end _prep_single
}//end class PSU_Oracle_Batch
