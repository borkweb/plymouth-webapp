<?php

/**
 * Sum the total of all items, which must have an amount() method.
 */
class PSU_Student_Finaid_Award_Sum {
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

	public function accepted() {
		$sum = 0;

		foreach( $this->it as $item ) {
			$sum += $item->accepted;
		}

		return $sum;
	}

	public function accepted_formatted() {
		return PSU_MoneyFormatter::create()->format( $this->accepted() );
	}
}
