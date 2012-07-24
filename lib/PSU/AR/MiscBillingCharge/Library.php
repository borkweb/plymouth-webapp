<?php
namespace PSU\AR\MiscBillingCharge;

class Library extends \PSU\AR\MiscBillingCharge {
	public $fields = array(
		'description',
		'damaged_lost',
		'title',
		'barcode',
		'call_number',
		'checked_out_date',
		'overdue_date',
		'return_date',
		'comments',
	);

	public static $default_detail_code = 'IYLF';

	public function __construct( $row ) {
		$row['id'] = $row['id'] ?: -1;
		$row['data_source'] = $row['data_source'] ?: 'library';
		$row['detail_code'] = $row['detail_code'] ?: static::$default_detail_code;
		$row['entry_date'] = $row['entry_date'] ?: date('Y-m-d H:i:s');
		$row['username'] = \PSU::nvl( $row['username'], $_SESSION['username'], 'script' );

		parent::__construct( $row );

		if( ! $this->meta('description') ) {
			$this->set_meta('description', 'Library Billing Fee');
		}//end if
	}//end constructor
}//end class
