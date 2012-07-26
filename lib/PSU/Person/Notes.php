<?php
namespace PSU\Person;

class Notes extends \PSU\Collection{

	protected $_wp_id;
	protected $_table = 'psu_identity.person_notes';
	static $_name = 'Notes';
	static $child = 'PSU\\Person\\Note';

	public function __construct( $wp_id = null ) {
		$this->_wp_id = $wp_id;
	}//end __construct

	public function count() {
		$sql = "SELECT COUNT(1) 
			     FROM  {$this->_table} 
		         WHERE wp_id = :wp_id 
			      AND deleted = 0";
		$data = array(
			'wp_id' => $this->_wp_id,
			);
		$count = \PSU::db('banner')->GetOne($sql, $data);
		return $count;
	}//end count

	
	public function get() {
		$sql = "SELECT * 
			     FROM {$this->_table} 
		         WHERE wp_id = :wp_id 
			      AND deleted = 0";
		$data = array(
			'wp_id' => $this->_wp_id,
		);
		return \PSU::db('banner')->GetAll($sql, $data);
	}//end get

	public function sort( $callback ) {
		$this->load();
		usort( $this->children, $callback );
	}

	/**
	 * Column list for ordering the result set.
	 */
	protected function _get_order() {
		return null;
	}//end _get_order

}//end class
