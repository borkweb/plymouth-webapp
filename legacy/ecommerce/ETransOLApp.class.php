<?php

require_once('ecommerce/ETrans.class.php');
require_once('PSUPerson.class.php');

/**
 * ETransOLApp
 *
 * Class designed to handle the special needs associated with the online 
 * application fee. This extends the functionality of the base ETrans 
 * ecommerce class for specific variable decleration.
 */
class ETransOLApp extends ETrans {

	/**
	 * This function is built to process incoming records in the 
	 * ecommerce_transaction table in banner. It is currenly in a concept 
	 * state and not yet fully implemented.
	 *
	 * @TODO Create function to process rows once we know where to store 
	 * the payment information.
	 */
	public function process() {
		return false;
	}//end process

	/**
	 * url
	 *
	 * Override the base ETrans url function to specifically work for OL 
	 * applicants. Build up the gateway with OL App specific variables, 
	 * and then return the formatted URL as provided by the _url ETrans 
	 * function  with the specified params.
	 *
	 * @param int $aidm The aidm of the applicant in question.
	 * @param string $app_id The specific application id for the fee
	 * @param string $program (Optional) Program of study
	 * @return string The formatted URL of the payment gateway
	 */
	public function url($aidm, $app_id, $program = 'Online') {

		/**
		 * Create a new applicant with the provided aidm. The applicant 
		 * class checks if the user already has a pidm before hand, and will 
		 * return a PSU Person object if that is the case. From there, we 
		 * set the applicant identifier that we are using to be that pidm if 
		 * they already exist, otherwise we use the aidm as confirmed to 
		 * exist by the successful creation of an applicant object.
		 */
		$applicant = new PSU\Applicant( $aidm );
		$applicant_id = ($applicant instanceof PSU\Person) ? $applicant->pidm : $applicant->aidm;

		if( !$applicant_id ) {
			throw new Exception( 'Could not find any information attached to AIDM "' . $aidm .'"' );
		}

		/**
		 * Online application fee specific URL parameters. These match up 
		 * with columns in the ecommerce_transaction table.
		 */
		$params = array(
			'orderNumber' => $applicant_id,
			'amount' => 5000,
			'amountDue' => 5000,
			'orderType' => 'Admission UG OL App',
			'orderDescription' => 'Undergraduate Online Admission App Fee',
			'name' => trim( $applicant->last_name . ', ' . $applicant->first_name . ' ' .$applicant->middle_name ),
			'userChoice2' => $program,
			'userChoice3' => $app_id,
		);

		/**
		 * Rely on the ETrans object to build up a redirect URL to relocate 
		 * the user after they have passed through the payment gateway.
		 */
		$this->setURLParam('redirectUrl', $this->base_url . '/receipt.html');
		$this->setURLParam('retriesAllowed', 5);
		
		$this->setURLParam('redirectUrlParameters', implode(',', $this->_redirect_params));

		/**
		 * Return the specific URL based on execution location
		 */
		return $this->_url( PSU::isdev() ? 'test' : 'prod', $params);
	}//end funciton

	/**
	 * __construct
	 *
	 * Base constructor object, passing directly to the parent 
	 * constructor. See ETrans class for paramter decleration.
	 */
	public function __construct( $params = false, $prod = false ) {
		parent::__construct( $params, $prod );
	}//end __construct
}//end class ETransOLApp
