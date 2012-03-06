<?php

class PSU_AR_MiscBillingCharge_Judicial extends PSU_AR_MiscBillingCharge {
	public $fields = array(
		'description',
	);

	public function __construct( $row ) {
		$row['id'] = $row['id'] ?: -1;
		$row['data_source'] = $row['data_source'] ?: 'judicial';
		$row['detail_code'] = $row['detail_code'] ?: 'IYJA';
		$row['entry_date'] = $row['entry_date'] ?: date('Y-m-d H:i:s');
		$row['username'] = PSU::nvl( $row['username'], $_SESSION['username'], 'script' );

		parent::__construct( $row );
	}//end constructor
}//end class PSU_AR_MiscBillingCharge_Judicial
