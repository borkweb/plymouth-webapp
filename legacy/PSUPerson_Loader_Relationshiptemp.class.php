<?php

require_once 'PSUPerson_Loader.class.php';
require_once 'MyRelationships.class.php';

class PSUPerson_Loader_Relationshiptemp extends PSUPerson_Loader implements PSUPerson_Loader_Interface
{
	public $data = array();
	public $priority = 10;

	public static $loaders = array(
		'first_name' => 'identifiers',
		'last_name' => 'identifiers'
	);

	public function __construct( PSUPerson $person ) {
		parent::__construct();
		$this->person = $person;
	}//end __construct

	public function loader_preflight( $identifier ) {
		if( ! PSU::is_wpid( $identifier, PSU::MATCH_TEMPID ) ) {
			return false;
		}

		$this->person->identifier = $identifier;
		$this->person->wpid = $this->person->wp_id = $identifier;
		$this->person->identifier_type = 'relationship-tempid';

		return true;
	}//end loader_preflight

	public function _load_wp_email() {
		$this->person->wp_email = MyRelationshipPerson::tempid_email( $this->person->wp_id );
	}

	public function _load_identifiers() {
		if( !isset($this->person->rel_id) ) {
			throw new Exception('cannot display name data for a temporary person without the relationship id');
		}

		$user = MyRelationshipPerson::tempid_meta_select( $this->person->wp_id, $this->person->rel_id );

		$this->person->first_name = $user['first_name'];
		$this->person->last_name = $user['last_name'];
	}//end _load_identifiers

	/**
	 * Load Mercury relationship data.
	 */
	function _load_myrelationships() {
		require_once 'MyRelationships.class.php';

		$this->person->myrelationships = new MyRelationships( $this->person );
	}//end _load_myrelationships

}//end PSUPerson_Loader_Relationshiptemp
