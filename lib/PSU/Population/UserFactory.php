<?php

/**
 * Accept some row of data, and create a new user record of some type based on that data.
 */
abstract class PSU_Population_UserFactory {
	/**
	 * Create new user based on the incoming row.
	 */
	abstract public function create( $row );
}//end PSU_Population_UserFactory
