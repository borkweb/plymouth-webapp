<?php

require_once( 'phpquery/phpQuery.php' );

class PSU_OrgSync_Form_Submission extends PSU_OrgSync{

	public $data = array();
	public $submission_object;

	/**
	 * Constructor that loads up the submission id.
	 *
	 * @param       $submission_id      The unique id of the submission of the form
	 */
	public function __construct( $submission_id ) {
		
		parent::__construct();
		$this->load( $submission_id );

	}//end __construct

	/**
	 * Function to load info about a partiucular form submission. Currently
	 * the function only loads in the submission ID
	 *
	 * @param 		$submission_id 		The unique id of the submission of the form
	 */
	public function load( $submission_id ) {
		
		$this->submission_object = phpQuery::newDocumentFileXML( $this->api_base_url.'forms/view_submission/'.$submission_id.'?key='.$this->api_key() );
		$this->data['submission_id'] = pq('submission_id')->text();

	}//end load

	/**
	 * Magic get function. It will return the contents of the data array if 
	 * it is set, just as it would for an object variable.
	 *
	 * @param 		$key 		The object attribute being looked for.
	 */
	public function &__get( $key ){
		if( isset( $this->data[ $key ] ) ) {
			return $this->data[ $key ];
		}//end if

		return null;
	}//end magic get

}//end class PSU_OrgSync_Form_Submission
