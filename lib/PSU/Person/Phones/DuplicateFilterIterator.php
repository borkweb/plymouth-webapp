<?php

namespace PSU\Person\Phones;

class DuplicateFilterIterator extends \PSU_FilterIterator {
	public $phone;
	public $properties = array();

	// don't factor in these when determining
	// duplicated-ness
	public static $exclude_indexes = array(
		'rowid',
		'seqno',
		'activity_date',
		'atyp_code',
		'addr_seqno',
		'user_id',
		'primary_ind',
		'data_origin',
	);

	public function __construct( $phone, $it ) {
		parent::__construct( $it );

		$this->phone = $phone;

		// get the phone properties and store them
		$this->properties = get_object_vars( $this->phone );

		// toss out the properties we don't care about
		$this->properties = $this->clean_properties( $this->properties );
	}//end constructor

	public function accept() {
		$record = $this->current();

		// if the tele_code is the same and the seqno matches, we're looking at 
		//  the same phone we're comparing against.  Exclude it by returning false
		if( $record->tele_code == $this->phone->tele_code && $record->seqno == $this->phone->seqno ) {
			return false;
		}//end if

		// get the current item's properties
		$current = get_object_vars( $record );

		// clear the cruft
		$current = $this->clean_properties( $current );

		// diff the properties
		$diff = array_diff( $current, $this->properties );

		// iff nothing is in the diff, the numbers are the same
		return empty( $diff );
	}//end accept

	/**
	 * clears out unnecessary properties from a property array
	 */
	public function clean_properties( $props ) {
		foreach( self::$exclude_indexes as $prop ) {
			unset( $props[ $prop ] );
		}//end foreach

		return $props;
	}//end clean_properties
}//end PSU\Person\Phones\DuplicateIterator
