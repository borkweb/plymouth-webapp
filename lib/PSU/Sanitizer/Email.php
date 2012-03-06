<?php

class PSU_Sanitizer_Email {
	public function clean( $email ) {
		if( $email == false ) {
			return null;
		}

		$email = $this->validate( $email );
		$email = $this->lower( $email );
		$email = $this->consolidatePlymouthDomains( $email );

		return $email ? $email : null;
	}

	public function validate( $email ) {
		return filter_var( $email, FILTER_VALIDATE_EMAIL );
	}

	/**
	 * Lowercase email.
	 */
	public function lower( $email ) {
		return strtolower($email);
	}

	/**
	 * Turn mail.plymouth.edu into plymouth.edu.
	 */
	public function consolidatePlymouthDomains( $email ) {
		return preg_replace( '/@mail\.plymouth\.edu$/i', '@plymouth.edu', $email );
	}//end consolidatePlymouthDomains
}//end class PSU_Sanitizer_Email
