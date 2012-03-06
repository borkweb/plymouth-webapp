<?php

namespace PSU\TeacherCert;

/**
 * 
 */
class PermissionsFactory {
	public static function from_idmobject() {
		$perm = new Permissions;

		$grant = array();

		if( \IDMObject::authZ('role', 'tcert') ) {
			$grant[] = 'tcert';
		}

		if( \IDMObject::authz('permission', 'tcert_admin') ) {
			$grant[] = 'admin';
		}

		if( \IDMObject::authz('permission', 'tcert_gatesystem_ug') ) {
			$grant[] = 'gatesystem_ug';
		}

		if( \IDMObject::authz('permission', 'tcert_gatesystem_gr') ) {
			$grant[] = 'gatesystem_gr';
		}

		if( \IDMObject::authz('permission', 'mis') ) {
			$grant[] = 'superadmin';
		}

		if( \IDMObject::authz('role', 'faculty') ) {
			$grant[] = 'faculty';
		}

		$perm->grant( $grant );

		$perm->pidm = $_SESSION['pidm'];

		return $perm;
	}//end from_idmobject
}//end class PermissionsFactory
