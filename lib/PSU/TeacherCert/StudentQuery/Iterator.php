<?php

namespace PSU\TeacherCert\StudentQuery;

use PSU\TeacherCert\Student;

class Iterator implements \Iterator, \Countable {
	public $row;
	public $obj;

	public function __construct( $results ) {
		$this->results = $results;
	}

	public function count() {
		$count = 0;
		foreach( $this->results as $item ) {
			$count++;
		}//end foreach
		$this->rewind();
		return $count;
	}

	public function current() {
		return $this->obj;
	}

	public function key() {
	}

	public function next() {
		if( $this->row = $this->results->FetchRow() ) {
			$this->obj = new Student( $this->row['pidm'] );
		} else {
			$this->obj = null;
		}
	}

	public function rewind() {
		$this->results->MoveFirst();
	}

	public function valid() {
		if( false == $this->row ) {
			$this->next();
		}

		return !!$this->row;
	}
}
