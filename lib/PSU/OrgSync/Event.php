<?php

require_once( 'phpquery/phpQuery.php' );

class PSU_OrgSync_Event extends PSU_OrgSync{

	public $event_obj;
	public $data = array();

	public function __construct( $event_obj ) {

		parent::__construct();

		$this->event_obj = $event_obj;
		$this->data['event_id'] = pq( $this->event_obj )->find('event_id')->text();
		$this->data['name'] = pq( $this->event_obj )->find('name')->text();
		$this->data['public_event'] = pq( $this->event_obj )->find('public_event')->text();
		$this->data['approved'] = pq( $this->event_obj )->find('approved')->text();
		$this->data['occurs_at'] = pq( $this->event_obj )->find('occurs_at')->text();
		$this->data['ends_at'] = pq( $this->event_obj )->find('ends_at')->text();
		$this->data['event_category'] = pq( $this->event_obj )->find('event_category')->text();
		$this->data['location'] = pq( $this->event_obj )->find('location')->text();
		$this->data['description'] = pq( $this->event_obj )->find('description')->text();
		$this->data['plain_text_description'] = pq( $this->event_obj )->find('plain_text_description')->text();
		$this->data['occcurs_until'] = pq( $this->event_obj )->find('occurs_until')->text();
		$this->data['rsvps'] = pq( $this->event_obj )->find('rsvps')->text();
		$this->data['organization_id'] = pq( $this->event_obj )->find('organization_id')->text();
		$this->data['organization_short_name'] = pq( $this->event_obj )->find('short_name')->text();

	}//end __construct

	/**
	 * Magic get function to return objects from the data array.
	 *
	 * @param 		$key 	The key to look for in the event.
	 * @return 				Returns the element of the even in data, otherwise returns null. This is a dumb object.
	 */
	public function &__get( $key ) {
		return isset( $this->data[ $key ] ) ? $this->data[ $key ] : null;
	}//end $__get

}//end PSU_OrgSync
