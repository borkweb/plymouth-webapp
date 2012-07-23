<?php
namespace PSU\AR\AidAuthorization;

/**
 * Sum the total of all items, which must have an amount() method.
 */
class Sum {
	public $it;

	public function __construct( \Iterator $it ) {
		$this->it = $it;
	}

	/**
	 * Static factory.
	 */
	public static function create( \Iterator $it ) {
		return new self( $it );
	}

	public function amount() {
		$sum = 0;

		foreach( $this->it as $item ) {
			$sum += $item->amount;
		}

		return $sum;
	}

	public function amount_formatted() {
		return \PSU_MoneyFormatter::create()->format( $this->amount() );
	}
}
