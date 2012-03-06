<?php

class PSU_OrgSync_Account extends PSU_OrgSync {

	public $account_obj;

	/**
	 * Constructor calling a construct on the parent PSU_OrgSync object.
	 * Taking in two parameters of an identifier, and what type of identifier it is,
	 * the constructor calls an internal load function in order to populate the 
	 * object with some basic information.
	 *
	 * @param 	$identifier 		The unique identifier used to get an account from OrgSync
	 * @param 	$identifier_type 	The type of identifier being passed: account_id, email_address, username
	 */
	public function __construct( $identifier, $identifier_type ) {
		parent::__construct();
		$this->load( $identifier, $identifier_type );
	}//end __construct

	/**
	 * Load function called in the constructor to handle the loading of account objects
	 * based on what type of identifying information that we have.
	 *
	 * @param 	$identifier 		The unique identifier used to get an account from OrgSync 
	 * @param   $identifier_type    The type of identifier being passed: account_id, email_address, username
	 */
	public function load( $identifier, $identifier_type ) {

		$callout_url = $this->api_base_url.'accounts/';

		switch( $identifier_type ) {
			case 'account_id':
				$callout_url .= $identifier.'?';
				break;
			case 'email':
				$callout_url .= 'search_by_email/?email='.$identifier.'&';
				break;
			case 'username':
				$callout_url .= 'search_by_username/?username='.$identifier.'&';
				break;
			default:
				//put the search query functionality in here in case someone decides to use it...
				$callout_url .= 'search_by_custom_profile/?q='.$identifier.'&';
				break;
		}//end switch

		$callout_url .= 'key='.$this->api_key();
		$this->account_obj = phpQuery::newDocumentFileXML( $callout_url );
		$this->data['account_id'] = pq('account_id')->text();

	}//end load

	/**
	 * Alias of phpQuery's dump function in order to more easily see aspects of the object that we are working with
	 *
	 * @return 		Returns the xml returned from OrgSync
	 */
	public function xml_dump() {
		return $this->account_obj->dump();
	}

	/**
	 * Internal function called via the magic get in order to return the membership information for
	 * the account in the format of an associative array. Has the organization ids, and admin status
	 *
	 * @return 		Returns an associative array indexed by organization ids containing key value elements of organization_id, and admin
	 */
	public function _load_membership() {

		foreach( $this->account_obj->find('membership') as $node ) {
			$node = pq( $node );
			$this->data['membership'][ $node->find('organization_id')->text() ] = array( 
											'organization_id' => $node->find('organization_id')->text(),
											'admin' => $node->find('admin')->text()
										);
		}//end foreach

		return $this->data['membership'];

	}//end _load_membership

	/**
	 * Magic get function used to get account infrmation. Works in way similar to BannerObject,
	 * first seeing if the param requested has been set in data, then seeing if a function exists to load
	 * it, otherwise looks for the requested param as a key in the phpQuery object, and returns the text found there
	 *
	 * @param 	$key 	The key of the information being looked for within the account object
	 * @return 			Returns the requested account information if it can be found within the object
	 */
	public function &__get( $key ) {

		if( isset( $this->data[ $key ] ) ) {
			return $this->data[ $key ];
		} elseif( method_exists( $this, '_load_'.$key ) ) {
			$func = '_load_'.$key;
			return $this->$func();
		} elseif( isset( $this->account_object ) ) {
			$this->data[ $key ] = pq( $key )->text();
			return $this->data[ $key ];
		}//end elseif

		return null;

	}//end &__get

}//end Class PSU_OrgSync_Account
