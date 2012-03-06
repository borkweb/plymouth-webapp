<?php

namespace PSU\TeacherCert;

class Model extends \PSU\Model {
	public function __construct( $datastore = null, $filemanager = null ) {
		parent::__construct( $datastore, $filemanager );

		$this->_validation_init();
	}//end constructor

	/**
	 * Static method to return a list of Teacher Cert objects suitable for a FormSelect.
	 *
	 * Additional args passed via $args:
	 *
	 *  - value: object property for FormSelect value
	 *  - name: object property for FormSelect name 
	 *
	 * @param string|object $object Class name, or a preexisting object.
	 * @param array $args Additional arguments.
	 */
	public static function collection( $object, $args = '' ) {
		static $records = null;

		$args = \PSU::params( $args, array(
			'value' => 'id',
			'name' => 'name',
		));

		if( is_object($object) ) {
			$key = spl_object_hash( $object );
		} else {
			$key = $object;
			$object = new $object;
		}

		if( null === $records[ $key ] ) {
			$value = $args['value'];
			$name = $args['name'];

			foreach( $object as $item ) {
				if( is_callable( array( $item, $name) ) ) {
					$records[ $key ][] = array( $item->$value, $item->$name() );
				} else {
					$records[ $key ][] = array( $item->$value, $item->$name );
				}
			}
		}

		return $records[ $key ];
	}//end collection

	/**
	 * initialize a validation hook based on a field name
	 */
	public function validation_init( $field ) {
		$method = 'validate_' . $field;

		if( method_exists( $this, $method ) ) {
			$action = $this->$field->clean_class() . '_' . $method;

			\PSU::add_action( $action, array( $this, $method ) );
		}//end if
	}//end validation_init

	/**
	 * Take in an object, and apply its properties to this model.
	 */
	public function ingest_object( $obj ) {
		$this->form( get_object_vars( $obj ) );
	}//end ingest_object

	/**
	 * attempt to initialize a validation action for
	 * each object property
	 */
	private function _validation_init() {
		$properties = array_keys( $this->data );

		foreach( $properties as $property ) {
			$this->validation_init( $property );
		}//end foreach
	}//end _validation_init
}//end \PSU\TeacherCert\Model
