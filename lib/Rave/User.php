<?php

namespace Rave;

class User{
	
	private $inRave = false;
	public $error = '';

	// these are in a specific order as required by XML Schema for raveUser XML element
	// http://www.getrave.com/restinfo/serviceSchema.xsd
	private $fields = array(
		'firstName' => 'string',
		'lastName' => 'string',
		'email' => 'string',

		'alternateEmail1' => 'string',
		'alternateEmail2' => 'string',
		'sisId' => 'string',
		'gender' => 'string',
		'ssoId' => 'string',
		'languagePreference' => 'string',
		'administrationRole' => 'string',

		'mobileNumber1' => 'string',
		'mobileCarrier1' => 'string',
		'mobile1Confirmed' => 'boolean',
		'useMobile1ForVoice' => 'boolean',

		'mobileNumber2' => 'string',
		'useMobile2ForVoice' => 'boolean',
		'mobileCarrier2' => 'string',

		'useMobile3ForVoice' => 'boolean',
		'mobileNumber3' => 'string',
		'mobileCarrier3' => 'string',

		'voiceOnlyPhoneNumber1' => 'string',
		'voiceOnlyPhoneExtension1' => 'string',
		'voiceOnlyPhoneNumber2' => 'string',
		'voiceOnlyPhoneExtension2' => 'string',
		'voiceOnlyPhoneNumber3' => 'string',
		'voiceOnlyPhoneExtension3' => 'string',

		'institutionRole' => 'string',
		'userAttribute1' => 'string',
		'userAttribute2' => 'string',
		'userAttribute3' => 'string',
		'userAttribute4' => 'string',
	);

	public function __construct( $row ) {
		// frequently row will be a SimpleXML Object, but could also be an associative array
		// the (string) cast seems unneccessary, but without it, we were getting SimpleXML objects
		$this->initial = array(); // store the initial loaded values so we can tell during save what changed
		foreach( $row as $k => $v ) {
			$this->$k = (string) $v;
			$this->initial[$k] = (string) $v;
		} // end foreach
	} // end __construct

	/**
	 * confirms a pending primary phone
	 * @param code for confirmation
	 */
	public function confirmPhone( $code ) {
		if( isset( $this->email ) ) {
			try {
				REST::confirmPhone( $this->email, $code );	
				return true;
			} // end try
			catch( InvalidConfirmationCode $e ) {
				return false;
			} // end catch
			catch( PhoneAlreadyConfirmed $e ) {
				return true; // if they are already confirmed, but are confirming again... let them.
			} // end catch
		} // end if
		else {
			Error::handle( 'Cannot confirm phone, object not properly initialized' );
		} // end else
	} // end confirmPhone

	/**
	 * delete the user from Rave
	 */
	public function delete() {
		$res = REST::deleteUser( $this->email );
		
		$this->inRave = false;

		// this may not be the right thing to do...	
		foreach( $this->fields as $element => $type ) {
			unset( $this->$element );
		} // end foreach

		return $res;
	} // end delete

	/**
	 * get a user object
	 * @param id can be either email or sisId
	 */
	public static function get( $id ) {
		try{
			$identifier = ( strpos( $id, '@' ) === false ) ? 'sisId' : 'email';
			$user = new User( array( $identifier => $id ) );
			$user->refresh();

			return $user;
		}
		catch( \Exception $e ) {
			return false;
		} // end catch
	} // end get

	/**
	 * generate XML from a user object
	 */
	protected function getXML( ) {
		$xml ='<?xml version="1.0" encoding="UTF-8"?>
			<raveUser>';

		foreach( $this->fields as $field => $type ) {
			if( isset( $this->$field ) ) {
				$xml .= '<'.$field.'>'.$this->$field.'</'.$field.'>';
			} // end if
		} // end foreach

		$xml .= '</raveUser>';
		
		return $xml;
	} // end getUserXML

	/**
	 * determine whether or not a field has changed since loading it
	 * @param field to chacek
	 */
	private function isChanged( $field ) {
		return ( $this->$field != $this->initial[$field] );
	} // end isChanged

	/**
	 * refreshes object data from Rave
	 * @param you can pass a user object/array to avoid a REST request
	 */
	private function refresh( $user = false ) {
		if( !$user ) {
			if( isset( $this->sisId ) ) {
				$user = REST::findUserBySisId( $this->sisId );
			} // end if
			elseif( isset( $this->email ) ) {
				$user = REST::findUserByEmail( $this->email );
			} // end elseif
			else {
				Error::handle( 'Refresh not possible, object is not properly initialized' );
			} // end else
		} // end if

		$this->inRave = true;

		foreach( $user as $k => $v ) {
			if( $this->fields[ $k ] == 'boolean' ) {
				// $v is typically coming in here as a SimplXML Object, which is highly annoying
				$v = (string)$v;
				if( $v === 'false' || $v === '' || $v === false) {
					$this->$k = false;
					$this->initial[$k] = false;
				} // end if
				else {
					$this->$k = true;
					$this->initial[$k] = true;
				} // end else
			} // end if
			else {
				$this->$k = (string) $v;
				$this->initial[$k] = (string) $v;
			} // end else
		} // end foreach
	} // end refresh 

	/**
	 * saves user info to Rave
	 * @param confirm indicator, if you want to send the confirmation text message
	 */
	public function save( $confirm = false ) {
		// TODO: we should validate the object before sending off to Rave
		
		$phone_ok = $this->validatePhone('1') 
			&& $this->validatePhone('2') 
			&& $this->validatePhone('3');
		if( ! $phone_ok ) {
			return false;
		} // end if

		if( ! $this->validateEmail() ) {
			return false;
		} // end validateEmail

		if( $this->inRave ) {
			$user = REST::updateUser( $this->getXML() );
		} // end if
		else {
			$user = REST::registerUser( $this->getXML() );
			$this->inRave = true;
		} // end else

		$this->refresh( $user );

		if( $confirm ) {
			try {
				REST::sendConfCode( $this->email );
			} // end try
			catch( \Rave\PhoneAlreadyConfirmed $e ) {
				return true;
			} // end catch
		} // end if confirm

		return true; // success!
	} // end save

	/**
	 * Transform a string 'true'/'false' into an actual boolean
	 */
	private function transformBoolean( $var ) {
		if( $var == 'true' || $var === true ) {
			return true;
		} // end if
		return false;
	} // end transformBoolean

	/**
	 * unconfirm a phone number
	 */
	public function unconfirmPhone() {
		$this->mobile1Confirmed = false;
		return $this->save();
	} // end unconfirmPhone
	/**
	 * validate the carrier
	 * @param which number to check (1, 2 or 3)
	 * @param boolean indicating whether or not we should lookup the carrier
	 */
	private function validateCarrier( $which, $lookup = false ) {
		$carrier = 'mobileCarrier'.$which;

		if( isset( $this->$carrier ) ) {
			if( !filter_var( $this->$carrier, FILTER_VALIDATE_INT ) ) {
				$this->error = 'Carrier number did not validate';
				return false;
			} // end if
		} // end if

		if( $lookup ) {
			try{
				$number = 'mobileNumber'.$which;
				$carrier_xml_obj = \Rave\REST\SiteAdmin::lookupCarrier( $this->$number ); 
				$this->$carrier = $carrier_xml_obj['id'];
			} // end try
			catch( UnknownCarrier $e ) {
				$this->error = 'Unknown carrier';
				return false;
			} // end catch
		} // end if

		return true;
	} // end validateCarrier

	/**
	 * validate the email address
	 */
	private function validateEmail() {
		if( !filter_var( $this->email, FILTER_VALIDATE_EMAIL ) ) {
			$this->error = 'Email address did not validate';
			return false;
		} // end if
		return true;
	} // end validateEmail

	/**
	 * private function to validate and set the right variables for a changed phone
	 * @param which number to check (1, 2 or 3)
	 */
	private function validatePhone( $which ) {
		$number = 'mobileNumber'.$which;
		$carrier = 'mobileCarrier'.$which;
		$confirmed = 'mobile'.$which.'Confirmed';

		if( isset( $this->$number ) ) {
			$options = array(); 
			$options['options']['min_range'] = 1000000000;
			$options['options']['max_range'] = 9999999999; 
			if( !filter_var( $this->$number, FILTER_VALIDATE_INT, $options ) ) {
				$this->error = 'Phone number did not validate';
				return false;
			} // end if

			if( $this->isChanged($number) ) {
				$this->$confirmed = false; // if we don't change this, Rave assumes it is still confirmed
				return $this->validateCarrier( $which, true );
			} // end if
			else {
				return $this->validateCarrier( $which, false );
			}
		} // end if

		// while no number is set, it is all still valid
		// unset the carrier
		unset( $this->$carrier );
		$this->$confirmed = false; // if we don't change this, Rave assumes it is still confirmed
		return true; 
	} // end validatePhone

} // end User
