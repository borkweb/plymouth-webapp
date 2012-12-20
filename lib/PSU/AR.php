<?php
namespace PSU;

class AR {
	public $db;

	public function __construct( $datastore = 'banner' ) {
		$this->db = \PSU::db( $datastore );
	}//end constructor

	
/**
 * determine the future term from bursar
 */
	public static function bursar_future_term( $level = 'ug' ) {

		$term = self::bursar_term( $level );
		$year = substr( $term, 0, -2 );

		switch( substr( $term, -2 ) ) {
			case 10:
				return $year.'30';
				break;
			case 30:
				return $year++.'10';
				break;
			case 91:
				return $year.'93';
				break;
			case 93:
				return $year++.'91';
				break;
		}//end switch
		
		return $term;
	}//end bursar_future_term

	// determine current bursar term
	public static function bursar_term( $level = 'ug' ) {
		$sql = "SELECT value FROM gxbparm WHERE param = :param_name";
		return \PSU::db('banner')->GetOne($sql, array( 'param_name' => $level.'_bill_default_term' ));
	}//end bursar_term

/**
 * Creates the SQL string needed to retrieve ecommerce data.
 * A cron job runs regularly to batch e-commerce data transactions
 * on titan, getfiles.php, into Banner. Each batch is assigned a 'fileid'
 * number (presented as "Doc Number" on webapp ecommerce/report).
 * If that cron was delayed, or kicked off early, there could be more than
 * one 'fileid' number for that day.
 *
 * Inputs:
 * - formatted_processors: use the return value of formatProcessors()
 * - begin and end dates
 *   -- gracefully handles a one digit day of month, BTW
 *   -- nothing is found if the begin and end date are today
 * - a processor code to limit the transactions by type, optional
 *   -- valid processor codes are listed in the comment to formatProcessors()
 *   -- if empty, all processor codes are used
 *
 */
	public static function get_history($formatted_processors, $begin_date=NULL, $end_date=NULL, $processor=NULL) {
		$sql = "SELECT t.*
	          FROM ecommerce_transaction t
	         WHERE (
	                (transactiontype = 1 and transactionstatus = 1)
	                OR
	                (transactiontype = 3)
	                OR
	                (transactiontype = 2 and transactionstatus = 1)
	               )
	           AND (
	                (t.activity_date BETWEEN to_date('".date('d-M-y',strtotime($begin_date))."', 'DD-Mon-YY') 
	                 AND 
	                 to_date('".date('d-M-y',strtotime($end_date))."', 'DD-Mon-YY')
	                ) 
	               ) ";
		if($processor) {
			$sql .= "  AND ordertype = '".$formatted_processors[$processor]."'";
		}//end if
		else {
			$sql .= "  AND ordertype IN ('".implode("','", $formatted_processors)."')";
		}//end else

		$sql .= " AND psu_status = 'loaded' ORDER BY fileid, accounttype, timestamp, transactionid";
		return $sql;
	} // get_history

	public static function detail_code( $code ) {
		static $detail_codes;

		if( !$detail_codes ) {
			$detail_codes = new \PSU\AR\DetailCodes;
			$detail_codes->load();
		}//end if

		return $detail_codes->data[ $code ];
	}//end detail_code

	/**
	 * Create a simplified, formatted array from the return value from PSUECommerce.getProcessors()
	 * Originally created for webapp/ecommerce/report
	 * Current (2012) set of valid processor codes, with their SQL 'ordertype' (also a display string)
	 *     flexcash          Res Life FlexCash
	 *     ug_app            Admission UG App
	 *     gr_app            Admission GR App
	 *     pay2print IT      Pay2Print
	 *     business_office   Finance/Business Office
	 */
	public static function format_processors($all_processors) {
		$formatted_processors = array();
		foreach($all_processors as $key=>$proc)
		{
			$formatted_processors[$key] = $proc['name'];
		}//end foreach
		return $formatted_processors;
	}//end format_processors

	/**
	 * generates a receipt number from sobseqn_receipt
	 */
	public static function generateReceiptNumber() {
		$sql = "DECLARE v_receipt NUMBER(8); BEGIN :v_receipt :=  tb_common.f_update_sobseqn_receipt; END;";
		$stmt = \PSU::db('banner')->PrepareSP($sql);
		\PSU::db('banner')->OutParameter($stmt, $receipt, 'v_receipt');
		\PSU::db('banner')->Execute($stmt);
		return $receipt;
	}//end generateReceiptNumber

}//end class
