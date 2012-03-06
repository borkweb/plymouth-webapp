<?php

class PSU_AR_MiscBillingCharge_Infirmary extends PSU_AR_MiscBillingCharge {
	public $external_data;
	public $blank_external = array(
		'description' => null,
		'med_code' => null,
		'confidential' => null,
		'diagnosis' => null,
		'med_tests' => null,
		'physician' => null,
		'procedure_date' => null,
	);

	public function __construct( $row ) {
		$row['id'] = $row['id'] ?: -1;
		$row['data_source'] = $row['data_source'] ?: 'infirmary';
		$row['detail_code'] = $row['detail_code'] ?: 'IYIC';
		$row['entry_date'] = $row['entry_date'] ?: date('Y-m-d H:i:s');
		$row['username'] = PSU::nvl( $row['username'], $_SESSION['username'], 'script' );

		parent::__construct( $row );

		$this->external_data();
	}//end constructor

	public function external_data() {
		if( ! isset( $this->external_data ) ) {
			$sql = "SELECT * FROM misc_billing_infirmary WHERE charge_id = :charge_id";
			$charge_id = $this->parent_id ?: $this->id;
			
			$this->external_data = PSU::db('banner')->GetRow( $sql, array('charge_id' => $charge_id) );
		}//end if

		if( ! $this->external_data['charge_id'] ) {
			echo 'here<br>';
			$this->external_data = $this->blank_external;
			$this->external_data['charge_id'] = $this->id;
		}//end if

		if( $this->external_data['charge_id'] <= 0 ) {
			$this->external_data['charge_id'] = $this->id;
		}//end if

		return $this->external_data;
	}//end external_data

	public function save_external_data() {
		$sql = "
			MERGE INTO misc_billing_infirmary target	
			USING (
				SELECT :charge_id charge_id,
				       :description description,
							 :med_code med_code,
			         :confidential confidential,
		           :diagnosis diagnosis,
		           :med_tests med_tests,
		           :physician physician,
		           :procedure_date procedure_date,
			         sysdate activity_date
			    FROM dual
			) source
			ON ( target.charge_id = source.charge_id )
			WHEN MATCHED THEN
				UPDATE SET
					target.description = source.description,
					target.med_code = source.med_code,
					target.confidential = source.confidential,
					target.diagnosis = source.diagnosis,
					target.med_tests = source.med_tests,
					target.physician = source.physician,
					target.procedure_date = source.procedure_date,
					target.activity_date = source.activity_date
			WHEN NOT MATCHED THEN
				INSERT (
					target.charge_id,
					target.description,
					target.med_code,
					target.confidential,
					target.diagnosis,
					target.med_tests,
					target.physician,
					target.procedure_date,
					target.activity_date
				) VALUES (
					source.charge_id,
					source.description,
					source.med_code,
					source.confidential,
					source.diagnosis,
					source.med_tests,
					source.physician,
					source.procedure_date,
					source.activity_date
				)
		";

		PSU::db('banner')->Execute( $sql, $this->external_data() );
	}//end save_external_data
}//end class PSU_AR_MiscBillingCharge_Infirmary
