<?php

namespace PSU\TeacherCert;

class Constituent extends ActiveRecord {
	static $_name = 'Constituent';
	static $table = 'constituents';

	public function students() {
		$sql = "
			SELECT 
				*
			  FROM 
			 	psu_teacher_cert.v_student_coop_teachers
			 WHERE 
			 	constituent_id = :constituent_id
		";

		return $students = \PSU::db('banner')->GetAll( $sql, array('constituent_id' => $this->id) );
	}//end students

	public function _prep_args() {
		$args = array(
			'the_id' => $this->id ?: 0,
			'first_name' => $this->first_name,
			'mi' => $this->mi,
			'last_name' => $this->last_name,
			'prefix' => $this->prefix,
			'suffix' => $this->suffix,
			'email' => $this->email,
			'delete_id' => $this->delete_id,
		);

		return $args;
	}//end _prep_args
}//end class Constituent
