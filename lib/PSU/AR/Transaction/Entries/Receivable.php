<?php

namespace PSU\AR\Transaction\Entries;

use \PSU\AR\Transaction\Entries;

class Receivable extends Entries {
	protected function record( $row ) {
		return new \PSU\AR\Receivable( $row );
	}//end record

	protected function sum_factory( $it ) {
		return \PSU\AR\Receivable\Sum::create( $it );
	}//end sum_factory
}//end class
