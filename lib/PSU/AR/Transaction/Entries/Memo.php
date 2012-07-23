<?php

namespace PSU\AR\Transaction\Entries;

use \PSU\AR\Transaction\Entries;

class Memo extends Entries {
	protected function record( $row ) {
		return new \PSU\AR\Memo( $row );
	}//end record

	protected function sum_factory( $it ) {
		return \PSU\AR\Memo\Sum::create( $it );
	}//end sum_factory
}//end class
