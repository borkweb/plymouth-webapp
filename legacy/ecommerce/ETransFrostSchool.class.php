<?php

require_once('ecommerce/ETransAR.class.php');

/**
 * @ingroup psuecommerce
 */
class ETransFrostSchool extends ETransAR {
	/**
	 * constructor.  Impressed?
	 */
	public function __construct($params = false, $prod = false) {
		parent::__construct($params, $prod);

		PSU::add_filter( 'transaction_split_pre_apply', array( &$this, 'pre_apply' ), 10, 2 );
	}//end constructor

	/**
	 * filter to find frost terms to pre-apply
	 * payments to
	 */
	public function pre_apply( $pre_apply, $bill ) {
		$term_balances = $bill->all_term_balances;
		
		if( $term_balances ) {
			krsort( $term_balances );

			foreach( $term_balances as $term => $value ) {
				if( preg_match( '/[24]0$/', $term ) ) {
					$pre_apply[ $term ] = $value;

					// we only want one term.  Break out of the loop
					break;
				}//end if
			}//end foreach
		}//end if

		return $pre_apply;
	}//end pre_apply

	/**
	 * construct gateway URL
	 */
	public function url($processor, $params = false, $server = 'test', $type = 'commerce_manager') {
		$params = PSU::params($params);
		$person = PSUPerson::get($params['id']);

		$this->setURLParam('orderType', $processor);
		$this->setURLParam('amountDue', $params['amount']);
		$this->setURLParam('currentAmountDue', $params['current_amount']);
		$this->setURLParam('orderNumber', $params['id']);
		$this->setURLParam('orderName', $person->formatName('l, f m'));
		$this->setURLParam('orderDescription', $processor);

		$server = 'prod';
		
		return $this->_url($server, $type);
	}//end url
}//end class ETransFrostSchool
