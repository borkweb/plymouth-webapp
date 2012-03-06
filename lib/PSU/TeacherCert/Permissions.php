<?php

namespace PSU\TeacherCert;

/**
 * Known permissions:
 * 
 * * tcert -- base role
 * * admin -- general data admin
 * * gatesystem_ug -- access to gate systems in ug level code; check with has_gatesystem($level)
 * * gatesystem_gr -- see above
 * * superadmin -- access to everything
 * * faculty -- users with faculty role in banner
 */
class Permissions {
	/**
	 * Active user's pidm.
	 */
	public $pidm = null;

	protected $privileges = array();

	/**
	 * True if user is allowed to search for students.
	 */
	public function can_search() {
		return $this->has('tcert') || $this->has('faculty');
	}//end can_search

	/**
	 *
	 */
	public function can_delete( $level_code = null ) {
		return $this->has( 'admin' ) || ( $level_code && $this->has_gatesystem( $level_code ) );
	}//end can_delete

	/**
	 * Grant access to an object.
	 */
	public function grant() {
		$permissions = func_get_args();

		// we got an array of permissions as the first argument
		if( 1 === count( $permissions ) && is_array( $permissions[0] ) ) {
			$permissions = $permissions[0];
		}

		foreach( $permissions as $permission ) {
			$this->privileges[$permission] = true;
		}
	}

	/**
	 * Does user has this permission?
	 */
	public function has( $permission ) {
		// god mode enabled
		if( isset($this->privileges['superadmin']) ) {
			return true;
		}

		return isset( $this->privileges[$permission] );
	}//end has

	/**
	 * True if user can access the given gate system, specified by level code.
	 * @param string $level_code The level code
	 * @return bool
	 */
	public function has_gatesystem( $level_code ) {
		return $this->has( 'gatesystem_' . $level_code );
	}//end has_gatesystem
}//end Permissions
