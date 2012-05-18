<?php
namespace PSU\Runner;


/**
 * AddressVerification
 *
 * Class for Runner CLEAN_Address() Verification scripts
 *
 * @author Dan Bramer <djbramer@plymouth.edu>
 * @copyright 2012, Plymouth State University, ITS
 */

class AddressVerification
{

	public static function Batch_Verify_SARADDR($in) {
		$defaults = array (
			'fn_max_verify'         => 3000000
			,'fb_update'             => true 
			,'fb_only_unverified'    => false
			,'fn_days_back'          => null
			,'fb_skip_international' => true
			,'fd_from_date'          => null
			,'fd_to_date'            => null
		);

		$args = array_merge($defaults,$in);
		$bind = array();

		$sql = "BEGIN clean_address_banner.Batch_Verify_SARADDR(";
		foreach ($args as $key => $val) {
			if ( NULL === $val ) {
				continue;
			}
			if ( is_bool($val) || "false" == strtolower($val) || "true" == strtolower($val)) {
				$val = "true" == strtolower($val) || true == $val ? "true" : "false";
				$sql .= "{$key} => {$val},";
			} else {
				$sql .= "{$key} => :{$key},";
				$bind[$key] = $val;
			}
		}
		$sql = substr($sql,0,-1);
		$sql .= "); END;	";

		
		$stmt = \PSU::db('banner')->PrepareSP($sql);
		foreach ($bind as $key => $val) {
			\PSU::db('banner')->InParameter($stmt,$args[$key],$key);
		}

		\PSU::db('banner')->Execute($stmt);
	}

	public static function Batch_Verify_SPRADDR($in) {
		$defaults = array (
			'fn_max_verify'         => 3000000
			,'fb_update'             => true 
			,'fb_only_unverified'    => false
			,'fv_address_type'       => null
			,'fn_days_back'          => null
			,'fb_skip_international' => true
			,'fb_verify_inactive'    => false
			,'fd_from_date'          => null
			,'fd_to_date'            => null
			,'fb_set_activity_date_user' => false
			,'fv_set_source_code'        => null
		);

		$args = array_merge($defaults,$in);
		$bind = array();

		$sql = "BEGIN clean_address_banner.Batch_Verify_SPRADDR(";
		foreach ($args as $key => $val) {
			if ( NULL === $val ) {
				continue;
			}
			if ( is_bool($val) || "false" == strtolower($val) || "true" == strtolower($val)) {
				$val = "true" == strtolower($val) || TRUE === $val ? "true" : "false";
				$sql .= "{$key} => {$val},";
			} else {
				$sql .= "{$key} => :{$key},";
				$bind[$key] = $val;
			}
		}
		$sql = substr($sql,0,-1);
		$sql .= "); END;	";

		
		$stmt = \PSU::db('banner')->PrepareSP($sql);
		foreach ($bind as $key => $val) {
			\PSU::db('banner')->InParameter($stmt,$args[$key],$key);
		}

		\PSU::db('banner')->Execute($stmt);
	}


}
