<?php
namespace PSU\Person;

class Notes extends \PSU\Collection{
/**
	 * The identifier(s) which will be used to filter
	 * the rows in the collection. See _get_sql() for details.
	 */
	protected $_wp_id;
	protected $_table = 'psu_identity.person_notes';
	static $_name = 'Notes';
	static $child = 'PSU\\Note';

	public function __construct( $wp_id = null ) {
		$this->_wp_id = $wp_id;
		self::load_notes();
	}//end __construct

	/**
	 *
	 */
	public function count() {
	\PSU::db('banner')->debug=true;
		$sql = "SELECT COUNT(1) FROM  {$this->_table} WHERE wp_id = :wp_id AND deleted = 0";
		$data = array(
			'wp_id' => $this->_wp_id,
			);
		$count = \PSU::db('banner')->GetOne($sql, $data);
		return $count;
	}//end count

	
	/**
	 * @sa _get_sql()
	 */
	public function get() {
	\PSU::db('banner')->debug=true;
		$sql = "SELECT * FROM {$this->_table} WHERE wp_id = :wp_id AND deleted = 0";
		$data = array(
			'wp_id' => $this->_wp_id,
		);
		return \PSU::db('banner')->GetAll($sql, $data);
	}//end get

	public function load_notes(){
		$notes = self::get();
		foreach( $notes as $note ){
			$this->notes[] = new Note( $note['id'], $this->_wp_id, $note['note'], $note['status']);

		}
	}//end create_notes
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

}//end class Collection
