<?php

require_once( 'phpquery/phpQuery.php' );

/**
 * Class built to encapsulate the curl calls to the OrgSync API
 */
class PSU_OrgSync {

	/**
	 * This is the OrgSync URL that we can make various curl calls to
	 */
	public $api_base_url;
	public $data = array( 'organizations' => array() );

	/**
	 * Constructor that sets the api_base_url if passed in, otherwise sets it to a default value
	 *
	 * @param 	$api_base_url 	Optional parameted to custom set the api_base_url
	 */
	public function __construct( $api_base_url = null ) {
		$this->api_base_url = $api_base_url ?: 'https://api.orgsync.com/api/';
	}// end __construct

	/**
	 * Function to return the base url for OrgSync in order to perform phpQuery calls to retrieve XML
	 *
	 * @return 		Returns the base URL that has been set in the constructor.
	 */
	function api_base_url() {
		return $this->api_base_url;
	}//end api_base_url

	/**
	 * Returns the unique API key needed to make calls to OrgSync for PSU
	 *
	 * @return 		Returns the unique OrgSync API key for PSU
	 */
	function api_key() {
		static $key = null;

		if( $key === null ) {
			// TODO dependency injection
			$config = \PSU\Config\Factory::get_config();
			$key = $config->get( 'orgsync', 'api_key' );
		}

		return $key;
	}//end api_key

	/**
	 * Retrieve a list of accounts under the organization (PSU) in the form account_id -> email_address
	 * Also store thiis information in the data array within this object under accounts
	 * 
	 * @return 		Returns the associative array of accounts
	 */
	public function get_accounts() {

		/**
		 * TODO: pull out object instantiation into independent functioni wherever this is being used.
		 */
		$accounts_obj = phpQuery::newDocumentFileXML( $this->api_base_url.'accounts/list/?key='.$this->api_key() );
		$this->node_loop_assign( $accounts_obj, 'account', 'account_id', 'email_address' );
		return $this->data['accounts'];

	}//end get_accounts


	/**
	 * Retrieve forms for a PSU organization if provided. If no ID is provided, retrieve all forms for PSU.
	 * If all forms are retrieved, plo them in this objects data, under forms.
	 *
	 * @param 	$org_id 	Optional parameter of a specific organization id under PSU
	 * @return 				Returns the associative array of forms
	 */
	public function get_forms( $org_id = null ){

		$forms_obj = phpQuery::newDocumentFileXML( $this->api_base_url.'forms/?key='.$this->api_key() );

		if( isset( $org_id ) ) {

			$org_forms = array();

			foreach( $forms_obj->find('form') as $node ) {

				$node = pq( $node );

				//the find on "id" is what gathers the organization id
				if( $node->find('id')->text() == $org_id ) {
					$org_forms[ $node->find('form_id')->text() ] = $node->find('name')->text();
				}//end if

			}//end foreach

			return $org_forms;

		}//end if

		$this->node_loop_assign( $forms_obj, 'form', 'form_id', 'name' );
		return $this->data['forms'];
	}//end get_forms

	/**
	 * Retrieve a list of the organizations at PSU, and store them under this objects data array under organizations
	 * in the form of id -> long_name
	 *
	 * @return 		Returns the associative array of organizations
	 */
	public function get_organizations(){

		$org_obj = phpQuery::newDocumentFileXML( $this->api_base_url.'organizations/?key='.$this->api_key() );
		$this->node_loop_assign( $org_obj, 'organization', 'id', 'long_name' );
		return $this->data['organizations'];

	}//end get_organizations

	/**
	 * Returns detail information about organizations grouped in an associative array indexed by category.
	 *
	 * @param 	$category 	Optional parameter category (string) to limit the results to organizations from the specified category
	 * @return  			Returns an associative array keyed on Category of associative arrays of organizations if no parameter 
	 * 						is passed, otherwise returns an associative array of organizations that were in the passed category.
	 */
	public function get_organizations_by_category( $category = null ) {

		$orgs = $this->get_organizations_detail();

		foreach( $orgs as $org_id => $org ) {
			$categories[ $org['category'] ][ $org_id ] = $org;
		}//end foreach

		ksort( $categories );

		//If we took in a category, just return the organiztions under that
		if( $category ) {
			return $categoriess[ $category ];
		}//if 

		return $categories;

	}//end get_organizations_by_category

	/**
	 * Returns an associative array of all organizations containing detailed information.
	 *
	 * @return 		An associative array containing organizations with shot_name, long_name, category, and description
	 */
	public function get_organizations_detail() {

		$orgs_obj = phpQuery::newDocumentFileXML( $this->api_base_url.'organizations/?key='.$this->api_key() );

		foreach( $orgs_obj->find( 'organization' ) as $node ) {
			$node = pq( $node );
			$orgs[ $node->find( 'id' )->text() ] = array(
													'short_name' => $node->find( 'short_name' )->text(),
													'long_name' => $node->find( 'long_name')->text(),
													'category' => $node->find( 'group_type' )->text(),
													'description' => $node->find( 'description' )->text(),
												);
		}//end foreach

		return $orgs;

	}//end get_organizations_detail

	/**
	 * Load an organization object into the organizations data of this object under it's organization id
	 *
	 * @param 	$org_id 	The organization id that is to be retrieved
	 * @return 		 		The organization object now stored in data under its id
	 */
	public function load_organization( $org_id ) {
		$org = new PSU_OrgSync_Organization( $org_id );
		$org->org_sync = $this;
		$this->data['organizations'][ $org_id ] = $org;
		return $org;
	}//end load_organization

	/**
	 * function built to help by doing some of the gruntwork for phpQuery to 
	 * loop, and build up our internal data within this object
	 *
	 * @param 	$pq_obj 		The phpQuery object returned with the requested OrgSync data
	 * @param 	$data_type 	The type of data object that we are gatering eg. Accounts, Forms, etc...
	 * @param 	$key 				The index key of the information that we are gathering for this type
	 * @param 	$value 			The data that we want associated with the key specified
	 */
	public function node_loop_assign( $pq_obj, $data_type, $key, $value ) {
		
		foreach( $pq_obj->find( $data_type ) as $node ) {
			$node = pq( $node );
			$this->data[$data_type.'s'][ $node->find( $key )->text() ] = $node->find( $value )->text();
		}//end foreach
		
	}//end node_loop_assign
}//end Class PSU_OrgSync
