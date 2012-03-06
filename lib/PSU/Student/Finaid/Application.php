<?php

class PSU_Student_Finaid_Application {
	public $pidm;
	public $aid_year;
	public $seqno;

	const FATHER = 'fath';
	const MOTHER = 'moth';

	public $father;
	public $mother;

	/**
	 * Boolean
	 */
	public $current;

	public function __construct( $pidm, $aid_year, $seqno = null ) {
		$this->pidm = $pidm;
		$this->aid_year = $aid_year;
		$this->seqno = $seqno;
	}

	public function __toString() {
		return '';
	}

	public function load( $app_row = null ) {
		if( $app_row === null ) {
			$app_row = $this->get_application( $this->pidm, $this->aid_year, $this->seqno );
		}

		$this->current = $app_row['rcrapp1_curr_rec_ind'];
		$this->seqno = $app_row['rcrapp1_seq_no'];

		$father_fields = $this->parent_fields( $app_row, self::FATHER );
		$this->father = new PSU_Student_Finaid_Application_Parent( $father_fields );

		$mother_fields = $this->parent_fields( $app_row, self::MOTHER );
		$this->mother = new PSU_Student_Finaid_Application_Parent( $mother_fields );
	}

	/**
	 * @param $fields Iterator
	 * @param $type string the type to filter for: FATHER or MOTHER
	 */
	public function parent_fields( $fields, $type ) {
		if( is_array($fields) ) {
			$fields = new ArrayIterator( $fields );
		}

		return new PSU_Student_Finaid_Application_ParentRowFilter( $fields, $type );
	}

	/**
	 * Check to see if the parents in this application are identical to the parents
	 * in another application.
	 */
	public function parents_match( $other_application ) {
		$father_match = $this->father->equals( $other_application->father );
		$mother_match = $this->mother->equals( $other_application->mother );

		return $father_match && $mother_match;
	}//end parents_match

	public function get_application( $pidm, $aid_year, $seqno = null ) {
		$args = array(
			'pidm' => $pidm,
			'aidy' => $aid_year,
		);

		// get either the requested, or the "current" application
		if( $seqno ) {
			$args['seqno'] = $seqno;
			$where_sql = 'AND rcrapp1_seq_no = :seqno';
		} else {
			$where_sql = "AND rcrapp1_curr_rec_ind = 'Y'";
		}

		$sql = "
			SELECT
				rcrapp1_curr_rec_ind, rcrapp1_seq_no,
				rcrapp4_fath_ssn, rcrapp4_fath_last_name, rcrapp4_fath_first_name_ini, rcrapp4_fath_birth_date,
				rcrapp4_moth_ssn, rcrapp4_moth_last_name, rcrapp4_moth_first_name_ini, rcrapp4_moth_birth_date
			FROM
				rcrapp1 LEFT JOIN rcrapp4 ON
					rcrapp1_aidy_code = rcrapp4_aidy_code AND
					rcrapp1_pidm = rcrapp4_pidm AND
					rcrapp1_infc_code = rcrapp4_infc_code AND
					rcrapp1_seq_no = rcrapp4_seq_no
			WHERE
				rcrapp1_infc_code = 'EDE' AND
				rcrapp1_pidm = :pidm AND
				rcrapp1_aidy_code = :aidy
				$where_sql
		";

		$row = PSU::db('banner')->GetRow( $sql, $args );
		return $row;
	}
}
