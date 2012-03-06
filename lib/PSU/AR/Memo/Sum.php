<?php

/**
 * Sum the total of all items, which must have an amount() method.
 */
class PSU_AR_Memo_Sum {
	public $it;

	public function __construct( Iterator $it ) {
		$this->it = $it;
	}

	/**
	 * Static factory.
	 */
	public static function create( Iterator $it ) {
		return new self( $it );
	}

	public function amount() {
		$sum = 0;

		foreach( $this->it as $item ) {
			if( PSU_AR::detail_code( $item->detail_code )->type_ind == 'P' ) {
				$amount = -1 * $item->amount;
			} else {
				$amount = $item->amount;
			}//end else

			$sum += $amount;
		}

		return $sum;
	}

	public function amount_formatted() {
		return PSU_MoneyFormatter::create()->format( $this->amount() );
	}
}
