<?php

$association = array(
	'checklist_item_answers' => array(
		'table' => 'sxbcopt',
	),
	'checklist_items' => array(
		'table' => 'stvadmr',
		'where' => array(
			'EXISTS(SELECT 1 FROM sxbcopt WHERE sxbcopt_admr_code = stvadmr_code )',
		),
	),
	'constituent_positions' => array(
		'table' => 'sxvtrtp',
	),
	'constituent_saus' => array(
		'table' => 'sxrtuid',
	),
	'constituent_schools' => array(
		'table' => 'sxrtsid',
		'notes' => 'Constituent attatched to non-existent school',
	),
	'constituents' => '',
	'districts' => array(
		'table' => 'sxvdist',
	),
	'gates' => array(
		'table' => 'stvadmt',
		'where' => array(
			"stvadmt_code IN ('T1','T2','T3','T4')",
		),
	),
	'saus' => array(
		'table' => 'sxvtsau',
	),
	'school_approval_levels' => array(
		'table' => 'sxvtapr',
	),
	'school_types' => array(
		'table' => 'sxvsctp',
	),
	'schools' => array(
		'table' => 'sxbsbgi',
	),
	'student_checklist_item_answers' => array(
		'table' => 'sxrcans',
	),
	'student_clinical_faculty' => array(
		'table' => 'sxrtrtp',
	),
	'student_school_constit' => array(
		'table' => 'sxrtrsp',
		'notes' => 'Constituent does not exist',
	),
	'student_schools' => array(
		'table' => 'sxrschl',
	),
);

$tables = array();

foreach( $association as $new => $old ) {
	if( 'constituents' == $new ) {
		$tables[ $new ] = constituents();
	} else {
		$tables[ $new ] = simple( $new, $old );
	}//end else

	if( $tables[ $new ]['new'] != $tables[ $new ]['old'] ) {
		$tables[ $new ]['differences'] = differences( $new, $old );
	}//end if

	if( $old['notes'] ) {
		$tables[ $new ]['notes'] = $old['notes'];
	}//end fi
}//end foreach

function differences( $new, $old ) {
	$data = array();

	switch ($new) {
		case 'checklist_item_answers':
			$sql = "
				SELECT * 
					FROM psu_teacher_cert.checklist_item_answers
				 WHERE NOT EXISTS( 
					SELECT 1 
						FROM sxbcopt
					 WHERE type = decode(sxbcopt_type, 'S', 'select', 'date')
					   AND answer = sxbcopt_answer
						 AND is_complete = sxbcopt_complete
						 AND is_default = sxbcopt_default
					)
			";
			$data = \PSU::db('banner')->GetAll( $sql );
			break;
	}//end switch

	return $data;
}//end differences

function simple( $new, $old ) {
	$data = array();

	$sql = "SELECT count(*) FROM psu_teacher_cert.{$new}";
	$data['new'] = \PSU::db('banner')->GetOne( $sql );

	if( is_array( $old ) ) {
		$where = array('1=1');

		if( $old['where'] ) {
			$where = array_merge( $where, $old['where'] );
		}//end if

		$sql = "SELECT count(*) FROM {$old['table']} WHERE ".implode(' AND ', $where );
		$data['old'] = \PSU::db('banner')->GetOne( $sql );
	} else {
		$sql = "SELECT count(*) FROM {$old}";
		$data['old'] = \PSU::db('banner')->GetOne( $sql );
	}//end else

	return $data;
}

function constituents() {
	$data = array();

	$sql = "SELECT count(*) FROM psu_teacher_cert.constituents";
	$data['new'] = \PSU::db('banner')->GetOne( $sql );

	// add 10 million to the sxrtuid and sxrtsid pidms to ensure they don't collide with the clinical faculty pidms
	$sql = "
		SELECT count(*) FROM (
		SELECT 10000000 + sxrtuid_pidm pidm 
			FROM sxrtuid
		UNION
		SELECT 10000000 + sxrtsid_pidm pidm
		  FROM sxrtsid
		UNION
		SELECT sxrtrtp_relation_pidm pidm
		  FROM sxrtrtp
		) bork
	";
	$data['old'] = \PSU::db('banner')->GetOne( $sql );

	return $data;
}
