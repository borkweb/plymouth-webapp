<?php
namespace PSU\SOAP;

class Dell extends \SoapClient {
	private static $DELL_ADDR='http://143.166.84.118/services/AssetService.asmx?WSDL';

	public function __construct($options = array()) {
		if (!isset($options['exceptions'])) {
			$options['exceptions'] = false;
		}

		parent::__construct(static::$DELL_ADDR, $options);
	}//end constructor

	public function info($tag) {
		$args = array(
			'guid'            => '11111111-1111-1111-1111-111111111111',
			'applicationName' => 'GLPI',
			'serviceTags'     => $tag
		);

		$response = parent::__soapCall('GetAssetInformation', array($args));

		if (is_soap_fault($response)||!isset($response->GetAssetInformationResult)) {
			return false;
		}

		$ship_time = strtotime($response->GetAssetInformationResult->Asset->AssetHeaderData->SystemShipDate);
		$info['ShipDate']=date("Y-m-d", $ship_time);
		$warranty_end_date="";
		$warranty_days_remaining = -1;
		if(isset($response->GetAssetInformationResult->Asset->Entitlements) && isset($response->GetAssetInformationResult->Asset->Entitlements->EntitlementData))
		{
			$entitlement_data = $response->GetAssetInformationResult->Asset->Entitlements->EntitlementData;
			$entitlement_data = is_array( $entitlement_data ) ? $entitlement_data : array( $entitlement_data );

			foreach( $entitlement_data as $data ) {
				$temp_days = $data->DaysLeft;

				if( $temp_days > $warranty_days_remaining ) {
					$warranty_days_remaining = $temp_days;
					$warranty_end_date = date( 'Y-m-d', strtotime( $data->EndDate ) );
				}//end if
			}//end foreach
		}//end if

		if($warranty_end_date!="") {
			$info['WarrantyEndDate'] = $warranty_end_date;
		}//end if

		if($warranty_days_remaining > -1) {
			$info['WarrantyDaysRemaining'] = $warranty_days_remaining;
		}//end if

		return $info;
	}//end info
}//end class
