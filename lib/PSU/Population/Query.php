<?php

abstract class PSU_Population_Query {
	/**
	 * Run the population query, returning an array of matches.
	 */
	abstract public function query( $args = '' );
}//end PSU_Population_Query
