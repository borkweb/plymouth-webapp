<?php
namespace PSU\AR\MiscBillingCharge;

class Athletics extends \PSU\AR\MiscBillingCharge {
	public $fields = array(
		'description',
	);
	public static $default_detail_code = 'IYAE';

	public function __construct( $row ) {
		$row['id'] = $row['id'] ?: -1;
		$row['data_source'] = $row['data_source'] ?: 'athletics';
		$row['detail_code'] = $row['detail_code'] ?: static::$default_detail_code;
		$row['entry_date'] = $row['entry_date'] ?: date('Y-m-d H:i:s');
		$row['username'] = \PSU::nvl( $row['username'], $_SESSION['username'], 'script' );

		parent::__construct( $row );
	}//end constructor
}//end class
