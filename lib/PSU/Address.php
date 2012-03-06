<?php

namespace PSU;

class Address extends \PSU_DataObject {
	public $aliases = array();

	public function __construct( $data = null ) {
		$this->aliases['zip'] = 'postal_code';

		parent::__construct( $data );
	}//end constructor

	/**
	 * returns generic description
	 */
	public function description() {
		return 'Address';
	}//end description

	public function __toString() {
		$streets = $this->street1;
		$streets .= $this->street2 ? ', '.$this->street2 : '';
		$streets .= $this->street3 ? ', '.$this->street3 : '';
		$streets .= $this->street4 ? ', '.$this->street4 : '';

		$city = $this->city;
		$city .= $this->state_abbr ? ', '.$this->state_abbr : '';
		$city .= $this->country ? ' '. $this->country : '';

		$address = trim( $streets ) . ', ' . trim( $city ) . ' ' . trim( $this->postal_code );

		return $address;
	}//end __toString
}//end Address
