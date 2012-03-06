<?php

class PSU_MoneyFormatter {
	public function format( $value ) {
		setlocale( LC_MONETARY, 'en_US' );
		return money_format( '%n', $value );
	}

	public static function create() {
		return new self();
	}
}
