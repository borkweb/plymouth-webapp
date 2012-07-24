<?php
namespace PSU\AR\Receivables;

class MiscChargesFilterIterator extends \PSU_FilterIterator {
	public function accept() {
		$receivable = $this->current();

		return \PSU\AR::detail_code( $receivable->detail_code )->dcat_code == 'MIS';
	}
}//end class
