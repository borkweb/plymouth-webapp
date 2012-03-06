<?php

namespace PSU\SubmissionApproval;

class Submission extends \PSU_DataObject implements \PSU\ActiveRecord {
	public function delete() {
		// TODO: add business logic
		echo "I'm deleting myself!<br><br>";
	}//end delete

	public static function get( $key ) {
		// TODO: add business logic
		echo "I'm in get()";
	}//end get

	public static function row( $key ) {
		// TODO: add business logic
		echo "I'm in row()";
	}//end row

	public function save( $method = 'insert' ) {
		// TODO: add business logic
		echo "I'm saving with method = {$method}<br><br>";
	}//end save
}//end class \PSU\SubmissionApproval\Submission
