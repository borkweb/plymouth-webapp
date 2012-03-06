<?php

class PSU_AR_Receivables_MiscChargesFilterIterator extends PSU_FilterIterator {
	public function accept() {
		$receivable = $this->current();

		return PSU_AR::detail_code( $receivable->detail_code )->dcat_code == 'MIS';
	}
}//end PSU_AR_Receivables_MiscChargesFilterIterator
