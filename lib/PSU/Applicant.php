<?php

namespace PSU;

class Applicant {

	public $data = array();
	public $loaders = array(
		'address' => 'addresses'
		'addresses' => 'addresses'
		'first_name' => 'person',
		'last_name' => 'person',
		'middle_name' => 'person',
	);

	public function __construct( $aidm, $app_seqno = FALSE ) {

		/**
		 * If this aidm already has a pidm assigned, bail and pass back the 
		 * PSUPerson object as that will be much more robust as an 
		 * information source.
		 */
		if( $pidm = $this->has_pidm( $aidm ) {
			return new \PSUPerson( $pidm );
		}//end if

		$this->data['aidm'] = $aidm;
		$this->data['app_seqno'] = $app_seqno ?: $this->max_app();

	}//end constructor

	public function &__get( $key ) {

		if( isset( $this->data[ $key ] ) {
			return $this->data[ $key ];
		}//end if

		$func = '_load_' . $key;
		if( method_exists( $this, $func ) ) {
			return $this->$func();
		} elseif( in_array( $key , array_keys( $loaders ) ) ) {
			$func = '_load_' . $loaders[ $key ];
			$this->$func();
			return $this->data[ $key ];
		}//end elseif

		$this->load();

		return ( $this->data[ $key ] ) ?: FALSE;
		
	}//end get

	public static function get( $identifier ) {

		return $identifier ? new Applicant( $identifier ) : FALSE;
		
	}//end get

	public function has_pidm( $aidm ) {
		$sql = "
			SELECT sarctrl_pidm
			  FROM sarctrl
			 WHERE sarctrl_aidm = :aidm
		";

		$pidm = PSU::db('banner')->GetOne( $sql, array( 'aidm' => $aidm ) );
		return ( is_numeric( $pidm ) ? $pidm : FALSE;
	}//end 

	public function load() {

	}//end load

	public function _load_address( $force = FALSE ) {

		if( $this->data['address'] && !$force ) {
			return $this->data['address'];
		}//end if

		$sql = "
			SELECT saraddr_appl_seqno AS application_sequence_number,
						 saraddr_seqno AS address_sequence_number,
						 saraddr_load_ind AS load_ind,
						 saraddr_activity_date AS address_activity_data,
						 saraddr_street_line1 AS street1,
						 saraddr_street_line2 AS street2,
						 saraddr_city AS city,
						 saraddr_stat_code AS state_code,
						 saraddr_zip AS zip
				FROM saraddr
			 WHERE saraddr_aidm = :aidm
			   AND saraddr_pers_seqno = :person_seqno
				 AND saraddr_app_seqno = :app_seqno
		";

		$this->data['address'] = \PSU::db('banner')->GetRow( $sql, array( 'aidm' => $this->aidm, 'person_seqno' => $this->person_seqno, 'app_seqno' => $this->app_seqno ) );
		return $this->data['address'];
	}//end function

	publc function _load_person() {
		$sql = "
			SELECT sarpers_first_name AS first_name,
						 sarpers_middle_name1 || ' ' ||sarmers_middle_name2 AS middle_name,
						 sarpers_last_name AS last_name,
						 sarpers_seqno AS person_seqno
						 sarpers_birth_dte AS dob,
						 sarpers_gender AS gender
				FROM sarpers
			 WHERE sarpers_aidm = :aidm
				 AND sarpers_rltn_cde IS NULL
		";

		foreach( (array) \PSU::db('banner')->GetRow( $sql, array( 'aidm' => $this->aidm ) )  as $key => $val ) {
			$this->data[ $key ] = $val;
		}//end foreach
	}//end function

	public function max_app() {
		$sql = "
			SELECT MAX(sarpses_appl_seqno) 
			  FROM sarpses
			 WHERE sarpses_aidm = :aidm
		";

		$args = array(
			'aidm' => $this->aidm,
		);

		$this->data[ 'application_number' ] = \PSU::db('banner')->GetOne( $sql, $aidm ) );
	}//end function

}//end class
