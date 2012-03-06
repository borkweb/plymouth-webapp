<?php

namespace PSU\TeacherCert;

/**
 * 
 */
class CooperatingTeachers extends Collection {
	static $child = 'PSU\TeacherCert\CooperatingTeacher';
	static $parent_key = 'school_id';
	static $table = 'constituent_schools';
	static $_name = 'Cooperating Teachers';
}//end class CooperatingTeachers
