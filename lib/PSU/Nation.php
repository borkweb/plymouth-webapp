<?php

namespace PSU;

/**
 * A simple class for representing a nation.
 */
class Nation {
	/**
	 * The country code.
	 */
	public $code;

	/**
	 * The country's name.
	 */
	public $name;

	public function __construct( $code, $name ) {
		$this->code = $code;
		$this->name = $name;
	}//end __construct
}//end class \PSU\Nation
