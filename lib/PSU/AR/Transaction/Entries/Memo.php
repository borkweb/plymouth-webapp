<?php

namespace PSU\AR\Transaction\Entries;

use \PSU\AR\Transaction\Entries;

class Memo extends Entries {
	protected function record( $row ) {
		return new \PSU_AR_Memo( $row );
	}//end record

	protected function sum_factory( $it ) {
		return \PSU_AR_Memo_Sum::create( $it );
	}//end sum_factory
}//end class \PSU\AR\Transaction\Entries\Memo
