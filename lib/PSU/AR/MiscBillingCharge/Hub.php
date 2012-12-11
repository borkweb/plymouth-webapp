<?php
namespace PSU\AR\MiscBillingCharge;

class Hub extends \PSU\AR\MiscBillingCharge {
	public static $default_detail_code = 'IYHE';

	public $fields = array(
		'description',
	);

	public function __construct( $row ) {
		$row['id'] = $row['id'] ?: -1;
		$row['data_source'] = $row['data_source'] ?: 'hub';
		$row['entry_date'] = $row['entry_date'] ?: date('Y-m-d H:i:s');
		$row['detail_code'] = $row['detail_code'] ?: static::$default_detail_code;
		$row['username'] = \PSU::nvl( $row['username'], $_SESSION['username'], 'script' );

		parent::__construct( $row );

		if( ! $this->meta('description') ) {
			$this->set_meta('description', \PSU\AR::detail_code( $row['detail_code'] )->desc );
		}//end if
	}//end constructor

	public static function detail_codes() {
		return array(
			'IYHE' => \PSU\AR::detail_code( 'IYHE' ),
			'IYHD' => \PSU\AR::detail_code( 'IYHD' ),
			'IYHT' => \PSU\AR::detail_code( 'IYHT' ),
			'IYHR' => \PSU\AR::detail_code( 'IYHR' ),
		);
	}//end detail_codes
}//end class
