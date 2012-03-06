<?php

// TODO: convert to iteratoraggregate BEFORE this gets used elsewhere.
class PSU_OrgSync_Form extends PSU_OrgSync{

	public $form_obj;

	public function __construct( $form_id ) {
		
		parent::__construct();
		$this->load( $form_id );

	}//end __construct

	public function load( $form_id ) {
		
		$this->form_obj = phpQuery::newDocumentFileXML( $this->api_base_url.'forms/'.$form_id.'?key='.$this->api_key() );
		$this->data['form_id'] = $form_id;

	}//end load

	public function get_submission( $submission_id ) {
		
		return new PSU_OrgSync_Form_Submission( $submission_id );

	}//end get_submission

	public function _load_form_submissions() {

		$submission_ids = explode( ',', pq('submission_ids')->text() );

		foreach( $submission_ids as $submission_id ) {
			$this->data['submissions'][ $submission_id ] = new PSU_OrgSync_Form_Submission( $submission_id );
		}//end foreach

		return $this->data['submissions'];

	}//end _load_form_submissions

	public function &__get( $key ) {

		if( isset( $this->data[ $key ] ) ) {

			return $this->data[ $key ];

		} elseif( isset( $this->data['organization'][ $key ] ) ) {

			return $this->data['organization'][ $key ];

		} elseif( method_exists( $this, '_load_'.$key ) ) {

			$func = '_load_'.$key;
			return $this->$func();

		} elseif( isset( $this->form_object ) ) {

			switch ( $key ) {
				case 'organization':
					$this->data['organization']['id'] = pq('id')->text();
					$this->data['organization']['short_name'] = pq('short_name')->text();
					return $this->data['organization'];
				case 'id':
					$this->data['organization']['id'] = pq('id')->text();
					return $this->data['organization']['id'];
				case 'short_name':
					$this->data['organization']['short_name'] = pq('short_name')->text();
					return $this->data['organization']['short_name'];
				default:
					$this->data[ $key ] = pq( $key )->text();
					return $this->data[ $key ];
			}//end switch

		}//end elseif

		return null;

	}//end &__get
}//end class PSU_OrgSync_Form
