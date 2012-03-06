<?php

require_once( 'phpquery/phpQuery.php' );

class PSU_OrgSync_Organization extends PSU_OrgSync{

	public $data = array();
	public $org_sync;
	public $organization_obj;

	/**
	 * Constructor to creat an Organization object from OrgSync data. Calls construct on the parent OrgSync class,
	 * and calls the internal load function to get the specific object.
	 *
	 * @param 	$org_id 	The id of the specific organization in PSU that we want an object for
	 */
	public function __construct( $org_id ) {

		parent::__construct();
		$this->load( $org_id );

	}//end __construct

	/**
	 * Loads the aspects of the organization be making an api calling out to OrgSync
	 *
	 * @param 	$org_id 	The id of a PSU organization within OrgSync
	 */
	public function load( $org_id ) {

		$this->organization_obj = phpQuery::newDocumentFileXML( $this->api_base_url.'organizations/'.$org_id.'?key='.$this->api_key() );
		$this->data['id'] = pq('id')->slice(0,1)->text();

	}//end load

	public function parse_looper( $key, $parent='organization' ) {

		if ( pq( $key )->children()->length() > 1 ) {

			$parsed = array();

			foreach( pq( $key )->children() as $node ) {
				$parsed[] = pq( $node )->text();
			}//end foreach

			return $parsed;

		} else {
			return pq( $key )->text();
		}//end if else

	}//end parse_looper

	/**
	 * Alias of the phpQuery built in dump function to output the xml that is retrieved from OrgSync.
	 *
	 * @return 		Returns the xml restrieved from OrgSync and used to create the phpQuery object.
	 */
	public function xml_dump() {
		return $this->organization_obj->dump();
	}//end xml_dump

	/**
	 * Function called to retrieve the Banner Activity Code stored in a custom field
	 * for organizations. This is the code that organizations are identified by in 
	 * Banner, and serves as our crossover point.
	 *
	 * @return 		Returns the banner activity code stored in the organization info, or NULL if it is not set.
	 */
	public function _load_banner_activity_code() {

		return pq('element name:contains(Banner Activity Code)')->next()->text();

	}//end _load_banner_activity_code

	/**
	 * Function called to retrieve the events owned by an organization, and store the info in the data array
	 *
	 * @return 		Returns a array of event objects indexed by their ids
	 *
	 * TODO: make the function take in start_date, end_date, and keyword for more specific event querying
	 */
	public function _load_events( $start_date = null, $end_date = null, $keyword = null ) {

		$events_obj = phpQuery::newDocumentFileXML( $this->api_base_url.'events/'.$this->data['id'].'?key='.$this->api_key() );
		$this->data['events_obj'] = $events_obj;
		$this->data['number_of_events'] = $events_obj->find('number_of_results')->text();

		foreach( $events_obj->find('event') as $node ) {
			$event = new PSU_OrgSync_Event( $node );
			$this->data['events'][ $event->event_id ] = $event;
		}//end foreach

		return $this->data['events'];
	}//end _load_events

	/**
	 * Returns the organizations custom profile information as an associative array.
	 *
	 * @return 		Returns an associative array with the element name pointing towards the stored data.
	 */
	public function _load_custom_profile() {
		foreach( pq('custom_profile element') as $el ) {
			$profile[ str_replace(' ', '_', strtolower(pq( $el )->find('name')->text())) ] = pq( $el )->find('data')->text(); 
		}
		return $profile;
	}

	/**
	 * Function called to load the forms for an organization
	 *
	 * @return 		Returns an array of form names indexed by form ids
	 */
	public function _load_forms() {
		$this->data['forms'] = PSU_OrgSync::get_forms( $this->data['id'] );
		return $this->data['forms'];
	}//end _load_forms

	/**
	 * Magic get function used for Organizations. First checks if the requested param
	 * is already set, then checks for a custom loader function. If that also does't exist,
	 * then the text at the key of the pq object is returned if it is top level, otherwise 
	 * the parse looper funtionality is called to dig for it.
	 *
	 * TODO: The parse looper function currently isn't robust enough, or necessary in the current spec. Instead, I'm just returning the top level that a passed key retrieves.
	 *
	 * @paream 		$key 	The actual data being serached for within the organization.
	 * @return 				Returns the data associated with the key for the organization if it exists/
	 */
	public function &__get( $key ) {

		if( isset( $this->data[ $key ] ) ) {

			return $this->data[ $key ];

		} elseif( method_exists( $this, '_load_'.$key ) ) {

			//call the _load_ method for the non-organization object
			$func = '_load_'.$key;
			return $this->$func();

		} elseif( isset( $this->organization_obj ) ) {

			return $this->parse_looper( $key );
		}//end elseif

		return null;
	}//end &__get

}//end Class PSU_OrgSync_Organization
