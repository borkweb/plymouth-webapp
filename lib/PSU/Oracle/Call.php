<?php

class PSU_Oracle_Call extends PSU_Oracle_Caller {
	public $date_fields = array();
	public $in = array();
	public $out = array();
	public $procedure;
	public $type = 'procedure';

	public function __construct( $id, $procedure, $in = array(), $out = array(), $date_fields = array() ) {
		$this->id = $id;

		$this->procedure = $procedure;

		$this->in = PSU::params($in);
		$this->out = $out;

		$this->date_fields = PSU::params($date_fields);

		return $this;
	}//end constructor

	public function execute() {
		$prep = $this->prep();

		return parent::execute( $prep['sql'], $prep['in'], $prep['out'], $this->id );
	}//end execute

	public function prep() {
		$prep = array(
			'in' => array(),
			'out' => $this->out,
			'sql' => '',
		);

		$params = '';

		// parse params into parameter string
		foreach( $this->in as $key => $param ) {
			// check if there is an alias for this field
			$actual_key = $this->alias[ $key ] ?: $key;

			if( in_array( $actual_key, $this->date_fields ) ) {
				$params .= ", p_".$actual_key." => to_date('" . $param ."', 'YYYY-MM-DD')";
			} else {
				$params .= ", p_".$actual_key." => :".$key;
				$prep['in'][ $key ] = $param;
			}//end else
		}//end foreach

		if( $this->type == 'function' ) {
			$return = ':'.$this->return.' := ';
		}//end if

		$prep['sql'] .= $return.$this->procedure."(".trim($params, ',')."); \n";

		return $prep;
	}//end prep

	/**
	 * sets this Oracle Call as an oracle function rather than an oracle procedure
	 */
	public function is_function() {
		// set the type
		$this->type = 'function';

		// initialize a return variable name for ADOdb
		$this->return = 'return_'.$this->id;

		// add the return variable name to the out array
		$this->out[] = $this->return;

		return $this;
	}//end return
}//end PSU_Oracle_Call
