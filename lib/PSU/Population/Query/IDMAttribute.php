<?php

namespace PSU\Population\Query;

/**
 * Return a set of matched users for the PSU_Population object.
 */
class IDMAttribute extends \PSU_Population_Query {
	public $type = 2;
	public $attribute = null;

	public function __construct( $attribute, $type = 'role' ) {
		$this->attribute = $attribute;

		if( is_numeric( $type ) ) {
			$this->type = $type;
		} else {
			$this->type = $type == 'role' ? 2 : 1;
		}//end else
	}//end constructor

	public function query( $args = array() ) {
		$defaults = array(
			'type_id' => $this->type,
			'attribute' => $this->attribute,
		);

		$args = \PSU::params( $args, $defaults );

		$sql = "
			SELECT distinct wp_id
			  FROM (
						SELECT wp_id
							FROM v_idm_attributes
						 WHERE attribute = :attribute
							 AND type_id = :type_id
						 ORDER BY lower(last_name), 
									 lower(first_name), 
									 lower(middle_name)
							)
		";

		$results = \PSU::db('banner')->GetCol( $sql, $args );
		return $results;
	}
}
