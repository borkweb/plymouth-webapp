<?php
namespace PSU\AR;

class AidAuthorization extends \PSU_DataObject {
	public $aliases = array();

	public function __construct( $row = null ) {
		parent::__construct( $row );

		$this->type_ind = $this->type_ind();
		$this->detail_desc = $this->detail_desc();
	}//end constructor

	/**
	 * returns the receivable's detail description
	 */
	public function detail_desc() {
		return \PSU\AR::detail_code( $this->detail_code )->desc;
	}//end detail_desc

	/**
	 * returns the receivable's type indicator
	 */
	public function type_ind() {
		return \PSU\AR::detail_code( $this->detail_code )->type_ind;
	}//end type_ind
}//end class
