<?php

namespace PSU;

class Phone extends \PSU_DataObject {
	public $aliases = array();

	/**
	 * Expected properties:
	 *
	 *     - area
	 *     - number
	 *     - extension
	 */
	public function __construct( $data = null ) {
		if( is_string($data) ) {
			$data = $this->parse($data);
		}

		parent::__construct( $data );
	}//end constructer

	/**
	 * returns generic description
	 */
	public function description() {
		return 'Phone';
	}//end description

	/**
	 *
	 */
	public function parse( $number ) {
		$data = array(
			'area' => null,
			'number' => null,
		);

		$number = self::unformat( $number );

		if( 7 === strlen($number) ) {
			$data['number'] = $number;
		} elseif( 10 === strlen($number) ) {
			$data['area'] = substr($number, 0, 3);
			$data['number'] = substr($number, 3);
		} elseif( 11 === strlen($number) ) {
			$data['area'] = substr($number, 1, 3);
			$data['number'] = substr($number, 4);
		} else {
			throw new Phone\Exception('unrecognized phone number length', 1);
		}

		return $data;
	}//end parse

	/**
	 * remove any formating from the phone number
	 */
	public static function unformat( $phone ) {
		return preg_replace('/[^\d]/', '', $phone);
	}// end unformat

	public function __set( $key, $value ) {
		switch($key) {
			case 'area':
			case 'number':
				$number = preg_replace( '/[^\d]/', '', $value );
		}

		if( 'area' === $key ) {
			if( ! preg_match( '/^\d{3}$/', $value ) ) {
				throw new Phone\Exception('area code must be three digits', 2);
			}
		}

		if( 'number' === $key ) {
			if( ! preg_match( '/^\d{7}$/', $value ) ) {
				throw new Phone\Exception('phone number (excluding area code) must be seven digits', 3);
			}
		}

		$this->$key = $value;
	}

	/**
	 *
	 */
	public function __toString() {
		if( ! $this->number ) {
			return '';
		}//end if

		$phone_string = substr($this->number, 0, 3) . '-' . substr($this->number, -4);

		if( $this->area ) {
			$phone_string = '(' . $this->area . ') ' . $phone_string;
		}//end if

		if( $this->extension ) {
			$phone_string .= ' x'.$this->extension;
		}//end if

		return $phone_string; 
	}//end __toString
}//end Phone
