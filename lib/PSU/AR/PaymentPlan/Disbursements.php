<?php

class PSU_AR_PaymentPlan_Disbursements implements IteratorAggregate {
	public $disbursements;

	public function count() {
		return sizeof( (array) $this->disbursements );
	}//end count

	/**
	 * retrieve disbursements
	 */
	public function get() {
		$args = array();

		if( $this->psu_id ) {
			$args['psu_id'] = $this->psu_id;
			$where .= " AND d.psu_id = :psu_id";
		}//end if

		if( $this->processed ) {
			$where .= " AND d.date_processed IS NOT NULL";
		} else {
			$where .= " AND d.date_processed IS NULL";
		}//end if

		if( $this->num_rows ) {
			$where .= " AND rownum <= :num_rows";
			$args['num_rows'] = $this->num_rows;
		}//end if

		$sql = "
			SELECT d.*, 
		         f.file_name,
		         f.file_type,
						 f.file_sub_type,
						 f.file_date
				FROM payment_plan_disbursement d
						 JOIN payment_plan_feed f
			         ON f.id = d.file_id
		         JOIN spriden s
			         ON s.spriden_id = psu_id
			        AND s.spriden_change_ind IS NULL
			 WHERE 1 = 1 {$where} 
			 ORDER BY UPPER(spriden_last_name), UPPER(spriden_first_name), spriden_mi, file_id, d.id";

		$results = PSU::db('banner')->Execute( $sql, $args );

		return $results ? $results : array();
	}//end get

	public function getIterator() {
		return new ArrayIterator( $this->disbursements );
	}//end getIterator

	public function load( $rows = null ) {
		if( $rows === null ) {
			$rows = $this->get();
		}//end if

		$this->disbursements = array();

		foreach( $rows as $row ) {
			$data = new PSU_AR_PaymentPlan_Disbursement( $row );
			$this->disbursements[] = $data;
		}//end foreach
	}//end load

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
}//end class PSU_AR_PaymentPlan_Disbursements
