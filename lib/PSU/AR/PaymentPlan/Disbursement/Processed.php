<?php

namespace PSU\AR\PaymentPlan\Disbursement;

class Processed extends \PSU\Collection {
	public static $child = '\PSU\AR\Receivable';
	public $file_id = null;
	public $file_name = null;

	public function __construct( $file_id, $file_name ) {
		$this->file_id = $file_id;
		$this->file_name = $file_name;
	}//end constructor

	public function get() {
		$sql = "
			SELECT 
				d.date_processed,
				t.*
			FROM
				payment_plan_disbursement d
				JOIN v_bio b
				  ON b.id = d.psu_id
				JOIN tbraccd t
				  ON t.tbraccd_pidm = b.pidm
				 AND t.tbraccd_document_number = :document_number
			WHERE
				d.file_id = :file_id
		";

		$args = array(
			'document_number' => \PSU\AR\PaymentPlan::document_number( $this->file_id ),
			'file_id' => $this->file_id,
		);

		$results = \PSU::db('banner')->Execute( $sql, $args );

		return $results;
	}//end get
}//end class
