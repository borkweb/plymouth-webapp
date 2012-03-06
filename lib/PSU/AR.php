<?php

class PSU_AR {
	public $db;

	public function __construct( $datastore = 'banner' ) {
		$this->db = PSU::db( $datastore );
	}//end constructor

	// determine current bursar term
	public static function bursar_term( $level = 'ug' ) {
		$sql = "SELECT value FROM gxbparm WHERE param = :param_name";
		return PSU::db('banner')->GetOne($sql, array( 'param_name' => $level.'_bill_default_term' ));
	}//end bursar_term

	public static function detail_code( $code ) {
		static $detail_codes;

		if( !$detail_codes ) {
			$detail_codes = new PSU_AR_DetailCodes;
			$detail_codes->load();
		}//end if

		return $detail_codes->data[ $code ];
	}//end detail_code

	/**
	 * generates a receipt number from sobseqn_receipt
	 */
	public static function generateReceiptNumber() {
		$sql = "DECLARE v_receipt NUMBER(8); BEGIN :v_receipt :=  tb_common.f_update_sobseqn_receipt; END;";
		$stmt = PSU::db('banner')->PrepareSP($sql);
		PSU::db('banner')->OutParameter($stmt, $receipt, 'v_receipt');
		PSU::db('banner')->Execute($stmt);
		return $receipt;
	}//end generateReceiptNumber
}//end class PSU_AR
