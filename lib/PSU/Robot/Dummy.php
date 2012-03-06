<?php

namespace PSU\Robot;

/**
 * Empty, do-nothing class.
 */
class Dummy implements RobotInterface {
	public function __construct( $host, $port ) {}
	public function var_dump() {}
	public function write( $message ) {}
	public function _write( $contents ) {}
}//end class Dummy
