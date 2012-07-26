<?php

namespace PSU\AR\PaymentPlan\Feed;

abstract class Collection extends \PSU\Collection {
	public static $child = '\PSU\AR\PaymentPlan\Feed';
	public $num = null;
	public $type = null;

	public function __construct( $num = 10 ) {
		$this->num = $num;
	}//end if

	public function get() {
		$sql = "
			SELECT *
			  FROM (
							SELECT f.*,
										 Dense_Rank() OVER (PARTITION BY file_type ORDER BY id DESC) rank	
								FROM payment_plan_feed f
							 ORDER BY id DESC
						 )
			 WHERE file_type = :file_type
			   AND rank <= :num
		";

		$args = array(
			'num' => $this->num,
			'file_type' => $this->type,
		);

		$results = \PSU::db('banner')->GetAll( $sql, $args );

		return $results;
	}//end get
}//end class
