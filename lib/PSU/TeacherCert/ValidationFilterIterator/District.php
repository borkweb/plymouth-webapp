<?php

namespace PSU\TeacherCert\ValidationFilterIterator;

use \PSU\TeacherCert\ValidationFilterIterator;

class District extends ValidationFilterIterator {
	public function __construct( $item, $it, $inverse = false ) {
		parent::__construct( '\PSU\TeacherCert\District', 'district_id', $item, $it, $inverse );
	}//end constructor
}//end PSU\TeacherCert\Schools\ValidationFilterIterator\District
