<?php
namespace PSU\AR\Deposit;

/**
 * Sum the total of all items, which must have an amount() method.
 */
class Sum {
	public $it;

	public function __construct( \Iterator $it ) {
		$this->it = $it;
	}//end constructor

	/**
	 * Static factory.
	 */
	public static function create( \Iterator $it ) {
		return new self( $it );
	}//end create

	public function amount() {
		$sum = 0;

		foreach( $this->it as $item ) {
			if( \PSU\AR::detail_code( $item->detail_code )->type_ind == 'P' ) {
				$amount = -1 * $item->amount;
			} else {
				$amount = $item->amount;
			}//end else

			$sum += $amount;
		}

		return $sum;
	}//end amount

	public function amount_formatted() {
		return \PSU_MoneyFormatter::create()->format( $this->amount() );
	}//end amount_formatted
}//end class
