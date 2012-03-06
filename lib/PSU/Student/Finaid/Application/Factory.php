<?php

class PSU_Student_Finaid_Application_Factory {
	public function fetch_by_pidm_aidy_seqno( $pidm, $aidy, $seqno ) {
		$args = array(
			'pidm' => $pidm,
			'aidy' => $aidy,
			'seqno' => $seqno,
		);

		$where = array(
			'rcrapp1_pidm = :pidm',
			'rcrapp1_aidy_code = :aidy',
			'rcrapp1_seq_no = :seqno',
		);

		$rset = $this->query( $args, $where );
		return new PSU_Student_Finaid_Application( $rset );
	}

	public function query( $args, $where = array() ) {
		$where[] = '1=1';

		$where_sql = ' AND ' . implode( ' AND ', $where );

		$sql = "
			SELECT
				rcrapp4_fath_ssn, rcrapp4_fath_last_name, rcrapp4_fath_first_name_ini, rcrapp4_fath_birth_date,
				rcrapp4_moth_ssn, rcrapp4_moth_last_name, rcrapp4_moth_first_name_ini, rcrapp4_moth_birth_date
			FROM
				rcrapp1 LEFT JOIN rcrapp4 ON
					rcrapp1_aidy_code = rcrapp4_aidy_code AND
					rcrapp1_pidm = rcrapp4_pidm AND
					rcrapp1_infc_code = rcrapp4_infc_code AND
					rcrapp1_seq_no = rcrapp4_seq_no
			WHERE
				rcrapp1_infc_code = 'EDE'
				$where_sql
		";

		$rset = PSU::db('banner')->GetRow( $sql, $args );

		return $rset;
	}
}
