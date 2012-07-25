<?php

namespace PSU;

class Ecommerce {
	public static function file_info( $date = null ) {
		$files = self::files( $date );

		$sql = "
			SELECT id,
			       fileid,
			       activity_date,
			       file_name,
						 (SELECT max(activity_date) FROM ecommerce_transaction t WHERE t.fileid = e.fileid) processed_date
				FROM ecommerce_eod e
			 WHERE fileid IN ('" . implode( "','", $files ) . "')
		";

		return \PSU::db('banner')->GetAll( $sql );
	}//end file_info

	public static function files( $date = null ) {
		$date = $date ?: time();

		$sql = "
			SELECT fileid
			FROM ecommerce_transaction eod
					 JOIN (
						SELECT MAX(eod2.activity_date) the_time
						FROM ecommerce_transaction eod2
						WHERE eod2.activity_date + 1 >= :from_date
				 	    AND eod2.fileid <> 'receipt'
					 ) max_date
					 ON 1 = 1
			WHERE TRUNC(eod.activity_date) = TRUNC(max_date.the_time)
		";

		return \PSU::db('banner')->GetCol( $sql, array('from_date' => \PSU::db('banner')->BindDate( $date ) ) );
	}//end files

	public static function report( $date = null ) {
		$report = array();

		$files = self::files( $date );

		if( $timestamp = self::min_timestamp( $files ) ) {
			$sql = "
					SELECT SUM(amount) amount,
								 ordertype,
					       p.type
						FROM (
									SELECT SUM(tbraccd_amount) amount,
												 SUBSTR(tbraccd_payment_id, 1, INSTR( tbraccd_payment_id, '_' ) - 1 ) payment_id
										FROM tbraccd
									 WHERE tbraccd_user like 'NELNET%'
										 AND tbraccd_entry_date >= :from_date
									 GROUP BY SUBSTR(tbraccd_payment_id, 1, INSTR( tbraccd_payment_id, '_' ) - 1 )
								 ) payments
								 JOIN ecommerce_transaction
									ON transactionid = payments.payment_id
								 JOIN ecommerce_processor p
									ON p.name = ordertype
					 GROUP BY ordertype, p.type
					UNION
						SELECT SUM(totalamount) / 100 amount,
									 ordertype,
						       p.type
							FROM ecommerce_transaction
								 JOIN ecommerce_processor p
									ON p.name = ordertype
						 WHERE activity_date >= :from_date
							 AND psu_status = 'loaded'
						GROUP BY ordertype, p.type
					UNION
						SELECT SUM(amount) amount,
									 ordertype,
									 p.type
						FROM (
									SELECT SUM(tbrdepo_amount) amount,
												 tbrdepo_document_number
										FROM tbrdepo
									 WHERE tbrdepo_user = 'NELNET'
										 AND tbrdepo_activity_date >= :from_date
									 GROUP BY tbrdepo_document_number
								 ) payments
								 JOIN ecommerce_transaction
									ON transactionid = payments.tbrdepo_document_number
								 JOIN ecommerce_processor p
									ON p.name = ordertype
					 GROUP BY ordertype, p.type
						ORDER BY ordertype, amount
			";

			$report = \PSU::db('banner')->GetAll( $sql, array('from_date' => \PSU::db('banner')->BindDate( $timestamp ) ) );
		}//end if

		return $report;
	}//end report

	public static function min_timestamp( $files ) {
		$sql = "
			SELECT min(timestamp)
			  FROM ecommerce_eod
			 WHERE fileid IN ('".implode("','", $files)."')
		";

		return \PSU::db('banner')->GetOne( $sql );
	}//end min_timestamp

	public static function pending() {
		$sql = "
      SELECT SUM(pending.amount) amount,
             pending.name,
             pending.type
        FROM (
              SELECT DECODE(t.transactiontype, 2, -1 * t.totalamount, t.totalamount) amount,
                     p.name,
                     p.type
                FROM ecommerce_transaction t,
                     ecommerce_processor p
               WHERE p.name = t.ordertype
                 AND t.psu_status = 'eod'
                 AND (
                      ((t.transactiontype = 1 OR t.transactiontype = 2) AND t.transactionstatus = 1)
                      OR
                      (t.transactiontype = 3 AND (t.transactionstatus IN (5, 6, 8)))
                     )
             ) pending
       GROUP BY pending.name, pending.type
		";
		
		$data = \PSU::db('banner')->GetAll( $sql );

		return empty($data) ? array() : $data;
	}//end pending

	public static function pending_files( $date = null ) {
		$sql = "
			SELECT file_name,
			       load_time
				FROM ecommerce_pending_eod
			 WHERE file_name LIKE 'usnh_psc_commercemanager%'
		";

		return \PSU::db('banner')->GetAll( $sql );
	}//end pending_files
}//end class
