<?php

require 'autoload.php';
require 'includes/TempDataCheck.php';
require 'includes/TempDataChecks.php';

\PSU::db('banner')->debug = true;

$tables = array(
	'constituent_positions' => array(
		'old_table' => 'sxvtrtp',
		'columns' => array(
			'name' => 'desc',
		),
	),
	'constituents' => array(
		'old_table' => array(
			'sxrtsid',
			'sxrtuid',
		),
		'columns' => array(
			'first_name' => 'first_name',
			'mi' => 'mi',
			'last_name' => 'last_name',
			'prefix' => 'prefix',
			'suffix' => 'suffix',
			'email' => 'email',
			'fake_pidm' => 'pidm',
		),
		'twhere' => " AND t.pidm IS NULL AND t.fake_pidm = %s_pidm ",
	),
	'districts' => array(
		'old_table' => 'sxvdist',
		'columns' => array(
			'name' => 'desc',
		),
	),
	'saus' => array(
		'old_table' => 'sxvtsau',
		'legacy' => 'code',
		'columns' => array(
			'name' => 'desc',
			'street_line1' => 'street_line1',
			'street_line2' => 'street_line2',
			'city' => 'city',
			'state' => 'stat_code',
			'zip' => 'zip',
			'phone' => array( 
				'concat' => array('phone_area', 'phone_number'),
			),
			'fax' => array( 
				'concat' => array('fax_area', 'fax_number'),
			),
		),
	),
	'school_approval_levels' => array(
		'old_table' => 'sxvtapr',
		'columns' => array(
			'name' => 'desc',
		),
	),
	'schools' => array(
		'old_table' => 'sxbsbgi',
		'legacy' => 'code',
		'columns' => array(
			'name' => 'desc',
			'grade_span' => 'grade_span',
			'enrollment' => 'enrollment',
			'street_line1' => 'street_line1',
			'street_line2' => 'street_line2',
			'city' => 'city',
			'state' => 'stat_code',
			'zip' => 'zip',
			'phone' => array( 
				'concat' => array('phone_area', 'phone_number'),
			),
			'fax' => array( 
				'concat' => array('fax_area', 'fax_number'),
			),
		),
	),
	'student_checklist_item_answers' => array(
		'old_table' => 'sxrcans',
		'columns' => array(
			'answer_value' => array(
				'field' => 'date',
				'type' => 'date',
			),
		),
	),
	'student_schools' => array(
		'old_table' => 'sxrschl',
		'columns' => array(
			'grade' => 'grade',
			'term_code' => 'term_code',
			'interview_ind' => array(
				'field' => 'interview'
			),
			'placement' => 'placement',
			'notes' => 'note',
		),
	),
	'student_school_constit' => array(
		'old_table' => 'sxrtrsp',
		'columns' => array(
			'term_code' => 'term_code_eff',
			'voucher' => 'voucher',
			'voucher_date' => 'voucher_date',
		),
	),
	'school_types' => array(
		'old_table' => 'sxvsctp',
		'columns' => array(
			'name' => 'desc',
		),
	),
);

if( $_GET['table'] && $tables[ $_GET['table'] ] ) {
} else {
	foreach( $tables as $table => $data ) {
		foreach( $data['columns'] as $new => $old ) {
			$old_count = 0;
			$new_count = 0;
			$core = '';

			if( is_array( $data['old_table'] ) ) {
				$old_tables = $data['old_table'];
			} else {
				$old_tables = array( $data['old_table'] );
			}//end else

			$old_tables_concat = '';
			foreach( $old_tables as $oldt ) {
				$core = null;

				$temp_old = $old;
				if( is_array( $old ) ) {
					if( $old['concat'] ) {
						$temp_old = implode( '||'.$oldt.'_', $old['concat'] );
					} else {
						$temp_old = $old['field'];
					}//end else

					if( 'date' == $old['type'] ) {
						$core = " {$oldt}_{$old['field']} = t.{$new} ";
					}
				}

				$old = $temp_old;

				$core = $core ?: " trim( {$oldt}_{$old} ) = trim( t.{$new} ) ";

				$compare = $data['legacy'] ? " AND {$oldt}_{$data['legacy']} = legacy_code " : "";

				$sql = "
					SELECT count(*)
						FROM psu_teacher_cert.{$table} t
					 WHERE EXISTS( 
									SELECT 1 
										FROM {$oldt} 
									 WHERE {$core} {$compare}
						 						 {$data['twhere']}
								 )
				";

				$sql = sprintf( $sql, $oldt );

				$new_count += \PSU::db('banner')->GetOne( $sql );

				$sql = "
					SELECT count(*)
						FROM {$oldt}
					 WHERE EXISTS( 
									SELECT 1 
										FROM psu_teacher_cert.{$table} t
									 WHERE {$core} {$compare}
						 						{$data['twhere']}
								 )
				";

				$sql = sprintf( $sql, $oldt );

				$ocount = \PSU::db('banner')->GetOne( $sql );
				$old_count += $ocount;
				$old_tables_concat .= $oldt.' ('.$ocount.') ';
			}//end foreach

			$args = array(
				'key' => $table . '.' . $new,
				'new_table' => $table,
				'new_column' => $new,
				'new_count' => $new_count,
				'old_table' => $old_tables_concat,
				'old_column' => str_replace( $oldt.'_', '', $old ),
				'old_count' => $old_count,
			);

			$record = new \PSU\TeacherCert\TempDataCheck( $args );
			$record->save('merge');
		}//end foreach
	}//end foreach
}//end else

\PSU::redirect('columns.php');
