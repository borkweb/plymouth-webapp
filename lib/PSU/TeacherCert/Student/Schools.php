<?php

namespace PSU\TeacherCert\Student;

use \PSU\TeacherCert\Student\School;

class Schools extends \PSU\TeacherCert\Collection {
	static $_name = 'Student Schools';
	static $child = 'PSU\\TeacherCert\\Student\\School';
	static $parent_key = 'student_gate_system_id';
	static $table = 'student_schools';
}//end \PSU\TeacherCert\Student\Schools
