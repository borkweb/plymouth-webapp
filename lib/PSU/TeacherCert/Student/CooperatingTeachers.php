<?php

namespace PSU\TeacherCert\Student;

/**
 * 
 */
class CooperatingTeachers extends \PSU\TeacherCert\Collection {
	static $_name = 'Cooperating Teachers';
	static $child = 'PSU\\TeacherCert\\Student\\CooperatingTeacher';
	static $table = 'student_school_constit';

	protected $_collection_key = 'constituent_school_id';
}//end class CooperatingTeachers
