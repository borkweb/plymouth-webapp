<?php

namespace PSU\AR\Receivables;

class ExcludeUncollectableParkingFineFilterIterator extends \PSU_FilterIterator {
	public function accept() {
		$data = $this->current();

		return $data->detail_code != 'IYCU';
	}
}//end class
