<?php

class PSU_AR_BillingControls extends PSU_DataObject {
	public function __construct( $data = null ) {
		if( ! $data ) {
			$data = self::load();
		}//end if
		parent::__construct( $data );
	}//end constructor	

	public static function load() {
		$data = array();

		$sql = "SELECT * FROM tbbctrl";
		$data = PSU::db('banner')->GetRow($sql);
		$data = PSU::cleanKeys('tbbctrl_', '', $data);

		return $data;
	}//end load
}//end class PSU_AR_BillingControls
