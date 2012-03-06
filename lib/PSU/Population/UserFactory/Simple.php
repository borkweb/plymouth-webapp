<?php

/**
 * Simple class to generate an object based on the incoming row.
 */
class PSU_Population_UserFactory_Simple extends PSU_Population_UserFactory {
	public function create( $row ) {
		$user = (object)$row;
		return $user;
	}//end create
}//end PSU_Population_UserFactory_Simple
