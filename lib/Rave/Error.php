<?php 

namespace Rave;

class Error{

	/**
	 * Helper function for handling errors
	 * @param error message
	 */
	public static function handle( $error ) {
		if( $error == 'Invalid confirmation code.' ) {
			throw new Exception\InvalidConfirmationCode( $error );
		} // end if
		elseif( strpos( $error, 'is already confirmed' ) !== false ) {
			throw new Exception\PhoneAlreadyConfirmed( $error );
		} // end elseif
		elseif( strpos( $error, 'Carrier not found for ' ) !== false ) {
			throw new Exception\UnknownCarrier( $error );
		} // end elseif

		throw new \Exception( $error );
	} // end handle

} // end class Error
