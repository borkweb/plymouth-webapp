<?php

namespace PSU\AR\PaymentPlan\Contract;

class Processed extends \PSU\Collection {
	public static $child = '\PSU\AR\Memo';
	public $file_id = null;
	public $file_name = null;

	public function __construct( $file_id, $file_name ) {
		$this->file_id = $file_id;
		$this->file_name = $file_name;
	}//end constructor

	public function get() {
		$sql = "
			SELECT 
				c.date_processed,
				m.*
			FROM
				payment_plan_contract c
				JOIN v_bio b
				  ON b.id = c.psu_id
				JOIN tbrmemo m
				  ON tbrmemo_pidm = b.pidm
				 AND tbrmemo_create_user = 'tms_' || c.tms_customer_number
				 AND tbrmemo_data_origin = 'feed_' || :file_sub_type
			WHERE
				c.file_id = :file_id
		";

		$args = array(
			'file_id' => $this->file_id,
			'file_sub_type' => preg_replace( '/[0-9]{4}_.+_(Grads?_)?([0-9]+)[^0-9]+$/', '\2', $this->file_name ),
		);

		$results = \PSU::db('banner')->GetAll( $sql, $args );

		return $results;
	}//end get
}//end class
