<?php
namespace PSU\AR\MiscBillingCharge;

class MetaContainer implements \IteratorAggregate {
	public $billing_id;
	public $parent_id;
	public $meta;

	public function __construct( $billing_id, $parent_id = null ) {
		$this->billing_id = $billing_id;
		$this->parent_id = $parent_id;
	}//end __construct

	/**
	 * retrieve meta
	 */
	public function get() {
		$args = array(
			'billing_id' => $this->billing_id,
		);

		if( $this->parent_id ) {
			$args['parent_id'] = $this->parent_id;
			$where .= " AND parent_id = :parent_id";
		}//end if

		$sql = "SELECT * FROM misc_billing_meta WHERE billing_id = :billing_id {$where}";

		$results = \PSU::db('banner')->Execute( $sql, $args );

		return $results ? $results : array();
	}//end get

	public function getIterator() {
		return new \ArrayIterator( $this->meta );
	}//end getIterator

	public function load( $rows = null ) {
		if( $rows === null ) {
			$rows = $this->get();
		}//end if

		$this->meta = array();

		foreach( $rows as $row ) {
			$data = new \PSU\AR\MiscBillingCharge\Meta( $row );
			$this->meta[ $row['meta_key'] ] = $data;
		}//end foreach
	}//end load

	public function value( $key ) {
		return isset( $this->meta[ $key ] ) ? $this->meta[ $key ] : null ;
	}//end value
}//end class
