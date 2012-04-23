<?php

class APEAuthZ {
	/**
	 * returns whether or not the current user can
	 * view advancement data
	 */
	public static function advancement() {
		return IDMObject::authz('permission', 'mis') || IDMObject::authZ('banner', 'developmentofficer');
	}//end advancement

	/**
	 * returns whether or not the current user can
	 * view family data
	 */
	public static function family() {
		return IDMObject::authz('permission', 'mis') 
			|| IDMObject::authZ('banner', 'developmentofficer') 
			|| IDMObject::authZ('banner', 'bannerinb') 
			|| IDMObject::authZ('role', 'calllog') 
			|| (IDMObject::authZ('role', 'student_worker') && preg_match('/158\.136\.74/', $_SERVER['REMOTE_ADDR']));
	}//end family

	/**
	 * returns whether or not the current user has access to the employee clearance checklist page
	 */
	public static function employee_clearance() {
		return IDMObject::authz('role', 'supervisor') || IDMObject::authZ('role', 'ape_checklist_employee_exit');
	}//end employee_clearance

	/**
	 * returns whether or not the current user can view hr data
	 */
	public static function hr() {
		return IDMObject::authz('permission', 'mis') || IDMObject::authZ('permission', 'ape_checklist_employee_exit_hr');
	}//end hr

	/**
	 * returns whether or not the current user is a student worker sitting at the help desk
	 */
	public static function infodesk() {
		return IDMObject::authZ('role', 'calllog') || (IDMObject::authZ('role', 'student_worker') && preg_match('/158\.136\.74/', $_SERVER['REMOTE_ADDR']));
	}//end infodesk

	/**
	 * returns whether or not the current user can
	 * view student data
	 */
	public static function student() {
		return IDMObject::authZ('role', 'registrar') 
			|| IDMObject::authZ('role', 'faculty') 
			|| IDMObject::authZ('permission', 'ape_student') 
			|| (IDMObject::authZ('banner', 'bannerinb') && IDMObject::authZ('role', 'staff'));
	}//end student
}//end class APEAuthZ
