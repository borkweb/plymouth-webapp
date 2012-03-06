<?php

namespace PSU;

class Email extends \PSU_DataObject {
	public $aliases = array();

	public function __construct( $row = null ) {
		if( ! is_array( $row ) ) {
			$row = array(
				'address' => $row,
			);
		}//end if

		parent::__construct( $row );

		/*****
		 * Gotta not do this for now until we write appropriate checks
		 *
		if( ! self::valid() ) {
			throw new \InvalidArgumentException('You must pass in a valid email address. (passed: '.$this->address.')'); 
		}//end if
		 */
	}//end constructor

	/**
	 * returns generic description
	 */
	public function description() {
		return 'Email';
	}//end description

	/**
	 * returns the domain of the email address
	 */
	public function domain() {
		return substr( $this->address, strpos( $this->address, '@' ) + 1 );
	}//end domain

	/**
	 * returns the username 
	 */
	public function username() {
		return substr( $this->address, 0, strpos( $this->address, '@' ) );
	}//end username

	/**
	 * returns whether or not an email is valid
	 */
	public function valid() {
		return filter_var( $this->address, FILTER_VALIDATE_EMAIL ) ? TRUE : FALSE;
	}//end validate

	public function __toString() {
		return $this->address;
	}//end __toString
}//end Email
