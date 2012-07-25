<?php

namespace PSU\AR\PaymentPlan;

class Contracts extends \PSU\Collection {
	public static $child = '\PSU\AR\PaymentPlan\Contract';
	public $file_id = null;

	public function __construct( $file_id = null ) {
		$this->file_id = null;
	}//end constructor

	/**
	 * retrieve contracts
	 */
	public function get() {
		$args = array();

		if( $this->psu_id ) {
			$args['psu_id'] = $this->psu_id;
			$where .= " AND c.psu_id = :psu_id";
		}//end if

		if( $this->processed ) {
			$where .= " AND c.date_processed IS NOT NULL";
		} elseif( ! $this->include_processed ) {
			$where .= " AND c.date_processed IS NULL";
		}//end if

		if( $this->num_rows ) {
			$where .= " AND rownum <= :num_rows";
			$args['num_rows'] = $this->num_rows;
		}//end if

		if( $this->file_id ) {
			$where .= " AND c.file_id = :file_id";
			$args['file_id'] = $this->file_id;
		}//end if

		$sql = "
			SELECT c.*, 
			       b.pidm,
		         f.file_name,
		         f.file_type,
						 f.file_sub_type,
						 f.file_date
				FROM payment_plan_contract c
						 JOIN payment_plan_feed f
			         ON f.id = c.file_id
		         LEFT JOIN v_bio b
			         ON b.id = psu_id
							AND REGEXP_LIKE( b.id, '[0-9]{9}' )
			 WHERE 1 = 1 {$where} 
			 ORDER BY UPPER(b.last_name), UPPER(b.first_name), b.middle_name, file_id, c.id";

		$results = \PSU::db('banner')->Execute( $sql, $args );

		return $results ? $results : array();
	}//end get

	public function set( $var, $val ) {
		$this->$var = $val;

		return $this;
	}//end set

	public function total() {
		$total = 0;

		foreach( $this as $charge ) {
			$total += $charge->total();
		}//end foreach

		return $total;
	}//end total
}//end class
