<?php

class PSU_AR_TermControls extends PSU_DataObject {
	public function __construct( $term_code = null, $data = null ) {
		if( ! $data ) {
			$data = self::load( $term_code );
		}//end if
		parent::__construct( $data );
	}//end constructor	

	public static function load( $term_code = null ) {
		$data = array();

		if( ! $term_code ) {
			$term_code = \PSU\Student::currentTermCode();
		}//end if

		$sql = "SELECT * FROM tbbterm WHERE tbbterm_term_code = :term_code";
		$data = PSU::db('banner')->GetRow($sql, array( 'term_code' => $term_code ));
		$data = PSU::cleanKeys('tbbterm_', '', $data);

		return $data;
	}//end load
}//end class PSU_AR_TermControls
