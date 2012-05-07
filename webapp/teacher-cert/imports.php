<?php
ini_set('memory_limit', -1);
require_once 'autoload.php';

$args = getopt('i:d');

if(!$args['i']){
	if($_GET['i']){
		$args['i'] = $_GET['i'];
	} else {
		die('You must specify an instance using -i');
	}//end else
}

PSU::get()->banner = PSU::db( $args['i'] );
if(isset($args['d'])) PSU::db('banner')->debug = true;

\PSU::db('banner')->StartTrans();

$tables = array(
	'deletes',
	'checklist_item_answers',
	'checklist_items',
	'constituent_positions',
	'constituents',
	'constituent_saus',
	'constituent_schools',
	'districts',
	'gate_systems',
	'gates',
	'saus',
	'school_approval_levels',
	'school_types',
	'schools',
	'student_checklist_item_answers',
	'student_clinical_faculty',
	'student_gate_systems',
	'student_gates',
	'student_school_constit',
	'student_schools',
);

// (purge - this is done first so no duplicates get accidentally placed)
foreach( $tables as $table ) {
	$sql="delete from psu_teacher_cert.{$table}";
	$action=PSU::db('banner')->Execute($sql);

	if( 'student_checklist_item_answers' == $table ) {
		$table = 'student_checklist_item_ans';
	} elseif( 'student_school_constit' == $table ) {
		$table = 'student_school_constit';
	}//end if

	$sql = "SELECT psu_teacher_cert.{$table}_seq.nextval FROM dual";
	$seq = PSU::db('banner')->GetOne( $sql );

	$sql = "ALTER SEQUENCE psu_teacher_cert.{$table}_seq INCREMENT BY -{$seq} MINVALUE 0";
	\PSU::db('banner')->Execute( $sql );

	$sql = "SELECT psu_teacher_cert.{$table}_seq.nextval FROM dual";
	$seq = PSU::db('banner')->GetOne( $sql );

	$sql = "ALTER SEQUENCE psu_teacher_cert.{$table}_seq INCREMENT BY 1 MINVALUE 0";
	\PSU::db('banner')->Execute( $sql );
}//end foreach

 /*  (to verify just cut and paste this into sqlDeveloper)
select * from psu_teacher_cert.checklist_item_answers;
select * from psu_teacher_cert.checklist_items;
select * from psu_teacher_cert.constituent_positions;
select * from psu_teacher_cert.constituent_saus;
select * from psu_teacher_cert.constituent_schools;
select * from psu_teacher_cert.districts;
select * from psu_teacher_cert.gate_systems;
select * from psu_teacher_cert.gates;
select * from psu_teacher_cert.saus;
select * from psu_teacher_cert.school_approval_levels;
select * from psu_teacher_cert.school_types;
select * from psu_teacher_cert.schools;
select * from psu_teacher_cert.student_checklist_item_answers;
select * from psu_teacher_cert.student_clinical_faculty;
select * from psu_teacher_cert.student_gate_systems;
select * from psu_teacher_cert.student_gates;
select * from psu_teacher_cert.student_school_constit;
select * from psu_teacher_cert.student_schools;
select * from psu_teacher_cert.constituents;
*/

// populate required DETELES

\PSU::db('banner')->Execute("INSERT INTO psu_teacher_cert.deletes (id, pidm, table_name) VALUES (1, 50080, 'constituents')");
\PSU::db('banner')->Execute("INSERT INTO psu_teacher_cert.deletes (id, pidm, table_name) VALUES (2, 50080, 'constituent_schools')");
\PSU::db('banner')->Execute("INSERT INTO psu_teacher_cert.deletes (id, pidm, table_name) VALUES (3, 50080, 'constituent_saus')");

 //populate GATE_SYSTEMS
$data = array(
	'id' => '99999',
	'name' => 'Graduate',
	'level_code' => 'gr',
);

$data['slug'] = str_replace('_', '-', PSU::createSlug(  $data['name']  ) );
$data['activity_date'] = date('Y-m-d');


$gate = new \PSU\TeacherCert\GateSystem( $data );
$gate->save('insert');

$data = array(
	'id' => '99999',
	'name' => 'Undergraduate',
	'level_code' => 'ug',
);

$data['slug'] = str_replace('_', '-', PSU::createSlug(  $data['name']  ) );
$data['activity_date'] = date('Y-m-d');


$gate = new \PSU\TeacherCert\GateSystem( $data );
$gate->save('insert');

function item( $name, $type, $arg_type = 'yesno', $args = null ) {
	if( ! $args ) {
		if( 'date' == $type ) {
			$answers = array(
				array( 
					'type' => 'date',
					'answer' => 'date',
					'is_complete' => 'Y',
					'is_default' => 'Y',
					'sort_order' => 0,
				),
			);
		} elseif( 'text' == $type ) {
			$answers = array(
				array( 
					'type' => 'text',
					'answer' => 'text',
					'is_complete' => 'Y',
					'is_default' => 'Y',
					'sort_order' => 0,
				),
			);
		} elseif( 'yesno' == $arg_type ) {
			$answers = array(
				array( 
					'type' => 'select',
					'answer' => 'No',
					'is_complete' => 'N',
					'is_default' => 'Y',
					'sort_order' => 0,
				),
				array( 
					'type' => 'select',
					'answer' => 'Yes',
					'is_complete' => 'Y',
					'is_default' => NULL,
					'sort_order' => 1,
				),
			);
		} elseif( 'na' == $arg_type ) {
			$answers = array(
				array( 
					'type' => 'select',
					'answer' => 'No',
					'is_complete' => 'N',
					'is_default' => 'Y',
					'sort_order' => 0,
				),
				array(
					'type' => 'select',
					'answer' => 'N/A',
					'is_complete' => 'Y',
					'is_default' => NULL,
					'sort_order' => 1,
				),
				array(
					'type' => 'select',
					'answer' => 'Yes',
					'is_complete' => 'Y',
					'is_default' => NULL,
					'sort_order' => 2,
				),
			);
		}//end else
	} else {
		$answers = $args;
	}//end else

	return array(
		'name'=> $name,
		'answers' => $answers,
	);
}//end item

// create Graduate Gates
$gates = array(
	array(
		'gate_system_id' => 1,
		'name' => 'Gate 1: Candidacy',
		'slug' => 'gr-gate-1',
		'sort_order' => 1,
		'items' => array(
			item( 'Official Program of Study', 'select' ),
			item( 'Met with Coordinator', 'date' ),
			item( 'Graduate Catalog Link', 'select' ),
			item( "NH 610's and 612's", 'select' ),
			item( 'Praxis Requirements', 'select' ),
			item( 'ePortfolio Expectations', 'select' ),
			item( 'Intro to Field Based Expectations', 'select' ),
			item( 'Intro to Internship/Practicum', 'select' ),
			item( 'GPA >= 3.0', 'select' ),
			item( 'Admission to Gate 2', 'date' ),
		), //end items
	),
	array(
		'gate_system_id' => 1,
		'name' => 'Gate 2: Application',
		'slug' => 'gr-gate-2',
		'sort_order' => 2,
		'items' => array(
			item( 'Intent to Complete - Form', 'select' ),
			item( 'Met with Coordinator', 'date' ),
			item( 'Received Placement Confirmation Form', 'select' ),
			item( 'Resume', 'select' ),
			item( 'Professional Statement', 'select' ),
			item( 'GPA >= 3.0', 'select' ),
			item( 'Early and Diverse Field Based Experiences', 'select', NULL, array(
				array( 
					'type' => 'select',
					'answer' => 'N/A',
					'is_complete' => 'N',
					'is_default' => 'Y',
					'sort_order' => 0,
				),
				array( 
					'type' => 'select',
					'answer' => '1',
					'is_complete' => 'Y',
					'is_default' => 'N',
					'sort_order' => 1,
				),
				array( 
					'type' => 'select',
					'answer' => '2',
					'is_complete' => 'Y',
					'is_default' => 'N',
					'sort_order' => 2,
				),
				array( 
					'type' => 'select',
					'answer' => '3',
					'is_complete' => 'Y',
					'is_default' => 'N',
					'sort_order' => 3,
				),
		 	)),
			item( 'Early and Diverse Field Based Experiences - Details', 'text' ),
			item( 'Placement 1', 'select' ),
			item( 'Placement 2', 'select' , 'na' ),
			item( 'Placement(s) Approved', 'select' ),
			item( 'Admission to Gate 3', 'date' ),
		),
	),
	array(
		'gate_system_id' => 1,
		'name' => 'Gate 3: Experience',
		'slug' => 'gr-gate-3',
		'sort_order' => 3,
		'items' => array(
			item( 'Petition for Certification/Graduation', 'select' ),
			item( 'Placement 1: Midterm Evaluation - Mentor', 'select' ),
			item( 'Placement 1: Final Evaluation - Mentor', 'select' ),
			item( 'Placement 1: Midterm Evaluation - Clinical', 'select' ),
			item( 'Placement 1: Final Evaluation - Clinical', 'select' ),
			item( 'Placement 2: Midterm Evaluation - Mentor', 'select' , 'na' ),
			item( 'Placement 2: Final Evaluation - Mentor', 'select' , 'na'),
			item( 'Placement 2: Midterm Evaluation - Clinical', 'select' , 'na'),
			item( 'Placement 2: Final Evaluation - Clinical', 'select' , 'na'),
			item( 'Approved ePortfolio', 'select' ),
			item( 'Program Wide Evaluations Completed', 'select' ),
			item( 'GPA >= 3.0', 'select' ),
			item( 'Admission to Gate 4', 'date' ),
		),
	),
	array(
		'gate_system_id' => 1,
		'name' => 'Gate 4: Certification',
		'slug' => 'gr-gate-4',
		'sort_order' => 4,
		'items' => array(
			item( 'Approval for Certification', 'date' ),
		),
	),

);

$stored_slugs = array();
foreach( $gates as $key => $gate_data ) {
	$gate_data['id'] = $key + 1;

	$items = $gate_data['items'];
	unset( $gate_data['items'] );

	$gate = new \PSU\TeacherCert\Gate( $gate_data );
	$gate->save('insert');

	if( $gate->id ) {
		foreach( $items as $item_data ) {
			$answers = $item_data['answers'];
			unset( $item_data['answers'] );

			$item_data['gate_id'] = $gate->id;
			$item_data['slug'] = 'gr-' . str_replace('_', '-', PSU::createSlug(  $item_data['name']  ) );

			if( $stored_slugs['item'][ $item_data['slug'] ] ) {
				$stored_slugs['item'][ $item_data['slug'] ]++;
				$item_data['slug'] .= '-' . $stored_slugs['item'][ $item_data['slug'] ];
			} else {
				$stored_slugs['item'][ $item_data['slug'] ] = 1;
			}//end else

			$item = new \PSU\TeacherCert\ChecklistItem( $item_data );
			$item->save('insert');

			if( $item->id ) {
				foreach( $answers as $answer_data ) {
					$answer_data['checklist_item_id'] = $item->id;

					$answer = new \PSU\TeacherCert\ChecklistItemAnswer( $answer_data );
					$answer->save('insert');
				}//end foreach
			}//end if
		}//end foreach
	}//end if
}//end gate

//populate GATES
	$sql="SELECT	psu_teacher_cert.gate_systems.id gate_system_id
					FROM	psu_teacher_cert.gate_systems
				 WHERE	substr(psu_teacher_cert.gate_systems.name,1,1) = 'U'";
	$gate_system_id=PSU::db('banner')->GetOne($sql);
for($i=1; $i<=4; $i++)
{
	$data = array(
	'id'=>99999,
	'gate_system_id'=>$gate_system_id,
	'name'=>"Gate ".$i,
	'slug'=>str_replace('_', '-', PSU::createSlug("UG Gate ".$i)),
	'sort_order'=>$i,
	'activity_date'=>date('Y-m-d'));

	$gate = new \PSU\TeacherCert\Gate( $data );
	$gate->save('insert');
}

//populate CHECKLIST_ITEMS
$sql="
	SELECT	stvadmr_desc,
					g.id gate_id,
					stvadmr_code
		FROM  stvadmr
					JOIN sxbgate
						ON sxbgate_admr_code = stvadmr_code
					JOIN psu_teacher_cert.gates g
						ON g.gate_system_id = 2
					 AND g.sort_order = substr( sxbgate_admt_code, 2, 1 )
";
$results=PSU::db('banner')->Execute($sql);
while($row=$results->FetchRow())
{
	$data = array(
	'id'=>'9999',
	'gate_id'=>$row['gate_id'],
	'name'=>$row['stvadmr_desc'],
	'slug'=>str_replace('_','-', PSU::createSlug($row['stvadmr_desc'])),
	'legacy_code'=> $row['stvadmr_code'],
	'activity_date'=>date('Y-m-d'));

	if( $stored_slugs['item'][ $data['slug'] ] ) {
		$stored_slugs['item'][ $data['slug'] ]++;
		$data['slug'] .= '-' . $stored_slugs['item'][ $data['slug'] ];
	} else {
		$stored_slugs['item'][ $data['slug'] ] = 1;
	}//end else

	$items = new \PSU\TeacherCert\ChecklistItem( $data );
	$items->save('insert');
}

// populate checklist_items that are no longer attached to a gate
$sql = "
SELECT DISTINCT stvadmr_desc,
			 NULL gate_id,
			 stvadmr_code
  FROM sxrcans
	     JOIN stvadmr
			   ON stvadmr_code = sxrcans_admr_code
WHERE NOT EXISTS(
  SELECT 1
	    FROM psu_teacher_cert.checklist_items ci
			   WHERE ci.legacy_code = sxrcans_admr_code
			 )
";
$results=PSU::db('banner')->Execute($sql);
while($row=$results->FetchRow())
{
	$data = array(
	'id'=>'9999',
	'gate_id'=>$row['gate_id'],
	'name'=>$row['stvadmr_desc'],
	'slug'=>str_replace('_','-', PSU::createSlug($row['stvadmr_desc'])),
	'legacy_code'=> $row['stvadmr_code'],
	'activity_date'=>date('Y-m-d'));

	$slug = $data['slug'];
	if( PSU::db('banner')->GetOne("SELECT 1 FROM psu_teacher_cert.checklist_items WHERE slug = :slug", array( 'slug' => $slug ) ) ) {
		$data['name'] .= ' (old)';
		$data['slug'] = str_replace('_', '-', PSU::createSlug(  $data['name']  ) );
	}//end while

	$items = new \PSU\TeacherCert\ChecklistItem( $data );
	$items->save('insert');
}

//populate CHECKLIST_ITEM_ANSWERS
$sql="SELECT	ci.id checklist_item_id,
							sxbcopt_type type,
							sxbcopt_answer answer,
							sxbcopt_complete is_complete,
							sxbcopt_default is_default,
							sxbcopt_order sort_order,
							sysdate activity_date
				FROM	sxbcopt
				      JOIN stvadmr ON stvadmr_code = sxbcopt_admr_code
							JOIN psu_teacher_cert.checklist_items ci ON ci.legacy_code = sxbcopt_admr_code
			 WHERE	ci.legacy_code=stvadmr_code
			   AND	sxbcopt_admr_code=stvadmr_code";
$results=PSU::db('banner')->Execute($sql);
while($row=$results->FetchRow())
{
	$data = array(
	'id'=>9999,
	'checklist_item_id'=>$row['checklist_item_id'],
	'type'=>strtolower( $row['type'] ) == 's' ? "select" : "date",
	'answer'=>$row['answer'],
	'is_complete'=>$row['is_complete'],
	'is_default'=>$row['is_default'],
	'sort_order'=>$row['sort_order'],
	'activity_date'=>date('Y-m-d'));
	$items = new \PSU\TeacherCert\ChecklistItemAnswer( $data );
	$items->save('insert');
}


//populate SAUS
$sql="SELECT	sxvtsau_code code,
	            sxvtsau_desc name,
							sxvtsau_street_line1 street_line1,
							sxvtsau_street_line2 street_line2,
							sxvtsau_city city,
							sxvtsau_stat_code state,
							sxvtsau_zip zip,
							sxvtsau_phone_area phone_area,
							sxvtsau_phone_number phone_number,
							sxvtsau_fax_area fax_area,
							sxvtsau_fax_number fax_number
				FROM	sxvtsau";
$results=PSU::db('banner')->Execute($sql);
while($row=$results->FetchRow())
{
	$data = array(
	'id'=>9999,
	'legacy_code'=>$row['code'],
	'name'=>$row['name'],
	'slug'=>str_replace('_','-', PSU::createSlug( $row['name'] )),
	'street_line1'=>$row['street_line1'],
	'street_line2'=>$row['street_line2'],
	'city'=>$row['city'],
	'state'=>$row['state'],
	'zip'=>$row['zip'],
	'phone'=>$row['phone_area']!=''? "(".$row['phone_area'].") ".$row['phone_number']: $row['phone_number'],
	'fax'=>$row['fax_area']!=''? "(".$row['fax_area'].") ".$row['fax_number']: $row['fax_number'],
	'activity_date'=>date('Y-m-d'));
	$items = new \PSU\TeacherCert\SAU( $data );
	$items->save('insert');
}
//populate DISTRICTS
$sql="SELECT	sxvdist_desc name
				FROM	sxvdist";
$results=PSU::db('banner')->Execute($sql);
while($row=$results->FetchRow())
{
	$data = array(
	'id'=>9999,
	'name'=>$row['name'],
	'slug'=>str_replace('_','-', PSU::createSlug( $row['name'] )),
	'activity_date'=>date('Y-m-d'));
	$items = new \PSU\TeacherCert\District( $data );
	$items->save('insert');
}


//populate SCHOOL TYPES
$sql="SELECT	sxvsctp_desc  name
				FROM	sxvsctp";
$results=PSU::db('banner')->Execute($sql);
while($row=$results->FetchRow())
{
	$data = array(
	'id'=>9999,
	'name'=>$row['name'],
	'slug'=>str_replace('_','-', PSU::createSlug( $row['name'] )),
	'activity_date'=>date('Y-m-d'));
	$query="INSERT	into psu_teacher_cert.school_types(
								id,
								name,
								slug,
								activity_date
					)VALUES(
								:id,
								:name,
								:slug,
								:activity_date
					)";
		$action=PSU::db('banner')->Execute($query,$data);
}


//populate SCHOOL APPROVAL LEVELS
$sql="SELECT	sxvtapr_desc  name
				FROM	sxvtapr";
$results=PSU::db('banner')->Execute($sql);
while($row=$results->FetchRow())
{
	$data = array(
	'id'=>9999,
	'name'=>$row['name'],
	'slug'=>str_replace('_','-', PSU::createSlug( $row['name'] )),
	'activity_date'=>date('Y-m-d'));
	$query="INSERT	into psu_teacher_cert.school_approval_levels(
								id,
								name,
								slug,
								activity_date
					)VALUES(
								:id,
								:name,
								:slug,
								:activity_date
					)";
		$action=PSU::db('banner')->Execute($query,$data);
}


//populate SCHOOLS

$sql="SELECT	(SELECT s.id FROM psu_teacher_cert.saus s JOIN sxvtsau ON sxvtsau_desc = s.name WHERE sxvtsau_code = sxbsbgi_tsau_code) saus_id,
							(SELECT s.id FROM psu_teacher_cert.school_approval_levels s JOIN sxvtapr ON sxvtapr_desc = s.name WHERE sxvtapr_code = sxbsbgi_tapr_code) tapr_id,
							(SELECT s.id FROM psu_teacher_cert.school_types s JOIN sxvsctp ON sxvsctp_desc = s.name WHERE sxvsctp_code = sxbsbgi_sctp_code) sctp_id,
							(SELECT s.id FROM psu_teacher_cert.districts s JOIN sxvdist ON sxvdist_desc = s.name WHERE sxvdist_code = sxbsbgi_dist_code) dist_id,
							sxbsbgi_code code,
							sxbsbgi_desc name,
							sxbsbgi_grade_span grade_span,
							sxbsbgi_enrollment enrollment,
							sxbsbgi_street_line1 street_line1,
							sxbsbgi_street_line2 sgreet_line2,
							sxbsbgi_city city,
							sxbsbgi_stat_code state,
							sxbsbgi_zip zip,
							sxbsbgi_phone_area phone_area,
							sxbsbgi_phone_number phone_number,
							sxbsbgi_fax_area fax_area,
							sxbsbgi_fax_number fax_number
				FROM sxbsbgi";
$results=PSU::db('banner')->Execute($sql);
while($row=$results->FetchRow())
{
	$data = array(
	'id'=>9999,
	'legacy_code'=>$row['code'],
	'sau_id'=>$row['saus_id'],
	'district_id'=>$row['dist_id'],
	'school_type_id'=>$row['sctp_id'],
	'school_approval_level_id'=>$row['tapr_id'],
	'name'=>$row['name'],
	'slug'=>str_replace('_','-', PSU::createSlug( $row['name'] )),
	'grade_span'=>$row['grade_span'],
	'enrollment'=>$row['enrollment'],
	'street_line1'=>$row['street_line1'],
	'street_line2'=>$row['street_line2'],
	'city'=>$row['city'],
	'state'=>$row['state'],
	'zip'=>$row['zip'],
	'phone'=>$row['phone_area']!=''? "(".$row['phone_area'].") ".$row['phone_number']: $row['phone_number'],
	'fax'=>$row['fax_area']!=''? "(".$row['fax_area'].") ".$row['fax_number']: $row['fax_number'],
	'activity_date'=>date('Y-m-d'));
	$slug_counter = 0;
	$slug = $data['slug'];
	while( PSU::db('banner')->GetOne("SELECT 1 FROM psu_teacher_cert.schools WHERE slug = :slug", array( 'slug' => $slug ) ) ) {
		$slug_counter++;
		$slug = $data['slug'] .'-'.$slug_counter; 
	}//end while

	if( $slug_counter ) {
		$data['slug'] = $slug;
	}//end if

	$items = new \PSU\TeacherCert\School( $data );
	$items->save('insert');
}

//populating STUDENT GATE SYSTEMS

$sql="SELECT	DISTINCT sxrtcrt_pidm pidm,
							psu_teacher_cert.gate_systems.id gate_system_id,
							sxrtcrt_applied_date apply_date,
							sxrtcrt_approved_date approve_date,
							(SELECT sxrgate_complete_date FROM sxrgate WHERE sxrgate_pidm = sxrtcrt_pidm AND sxrgate_admt_code = 'T4') complete_date
				FROM  sxrtcrt,
							psu_teacher_cert.gate_systems,
              psu_teacher_cert.gates,
              psu_teacher_cert.checklist_items
			 WHERE  substr(psu_teacher_cert.gate_systems.name,1,1)='U'
         AND  psu_teacher_cert.gates.id=psu_teacher_cert.checklist_items.gate_id
         AND  psu_teacher_cert.gates.gate_system_id=psu_teacher_cert.gate_systems.id
    ORDER BY  sxrtcrt_pidm";
$results=PSU::db('banner')->Execute($sql);
while($row=$results->FetchRow())
{
	$data = array(
	'id'=>9999,
	'pidm'=>$row['pidm'],
	'gate_system_id'=>$row['gate_system_id'],
	'apply_date'=>$row['apply_date'],
	'approve_date'=>$row['approve_date'],
	'complete_date'=>$row['complete_date'],
	'exit_date'=>'',
	'activity_date'=>date('Y-m-d'));
	$query="INSERT	into psu_teacher_cert.student_gate_systems(
								id,
								pidm,
								gate_system_id,
								apply_date,
								approve_date,
								complete_date,
								exit_date,
								activity_date
					)VALUES(
								:id,
								:pidm,
								:gate_system_id,
								:apply_date,
								:approve_date,
								:complete_date,
								:exit_date,
								:activity_date
					)";
		$action=PSU::db('banner')->Execute($query,$data);
}


//populating STUDENT GATE

$sql="SELECT	psu_teacher_cert.student_gate_systems.id student_gate_system_id,
							psu_teacher_cert.gates.id gate_id,
							sxrgate_complete_date complete_date
				FROM  psu_teacher_cert.gates,
							psu_teacher_cert.student_gate_systems,
							psu_teacher_cert.gate_systems,
              sxrgate
			 WHERE  psu_teacher_cert.gates.gate_system_id=psu_teacher_cert.gate_systems.id
				 AND  psu_teacher_cert.student_gate_systems.gate_system_id=psu_teacher_cert.gate_systems.id
         AND  psu_teacher_cert.student_gate_systems.pidm=sxrgate_pidm
         AND  substr(sxrgate_admt_code,2,1)=psu_teacher_cert.gates.sort_order
    ORDER BY  psu_teacher_cert.student_gate_systems.pidm";
$results=PSU::db('banner')->Execute($sql);
while($row=$results->FetchRow())
{
	$data = array(
	'id'=>9999,
	'student_gate_system_id'=>$row['student_gate_system_id'],
	'gate_id'=>$row['gate_id'],
	'complete_date'=>$row['complete_date'],
	'activity_date'=>date('Y-m-d'));
	$query="INSERT	into psu_teacher_cert.student_gates(
								id,
								student_gate_system_id,
								gate_id,
								complete_date,
								activity_date
					)VALUES(
								:id,
								:student_gate_system_id,
								:gate_id,
								:complete_date,
								:activity_date
					)";
		$action=PSU::db('banner')->Execute($query,$data);
}


//populating STUDENT CHECKLIST ITEM ANSWERS

$sql="SELECT  DISTINCT sgs.id student_gate_system_id,
							ci.id checklist_item_id,
							cia.id answer_id,
							DECODE(sxbcopt_answer,'date',to_char(sxrcans_date,'YYYY-MM-DD'),NULL) answer_value
				FROM sxrcans
						 JOIN sxbcopt
						   ON sxbcopt_code = sxrcans_copt_code
						 JOIN psu_teacher_cert.student_gate_systems sgs
						   ON sgs.pidm = sxrcans_pidm
				     JOIN psu_teacher_cert.checklist_items ci
						   ON ci.legacy_code = sxrcans_admr_code
						 JOIN psu_teacher_cert.checklist_item_answers cia
						   ON cia.checklist_item_id = ci.id
							 AND cia.answer = sxbcopt_answer
				";
$results=PSU::db('banner')->Execute($sql);
while($row=$results->FetchRow())
{
	$data = array(
	'id'=>9999,
	'student_gate_system_id'=>$row['student_gate_system_id'],
	'checklist_item_id'=>$row['checklist_item_id'],
	'answer_id'=>$row['answer_id'],
	'answer_value'=>$row['answer_value']);
	$query="INSERT	into psu_teacher_cert.student_checklist_item_answers(
								id,
								student_gate_system_id,
								checklist_item_id,
								answer_id,
								answer_value,
								activity_date
					)VALUES(
								:id,
								:student_gate_system_id,
								:checklist_item_id,
								:answer_id,
								:answer_value,
								SYSDATE
					)";
		$action=PSU::db('banner')->Execute($query,$data);
}


//populate CONSTITUENT POSITIONS
$sql="SELECT	sxvtrtp_desc  name
				FROM	sxvtrtp";
$results=PSU::db('banner')->Execute($sql);
while($row=$results->FetchRow())
{
	$data = array(
	'id'=>9999,
	'name'=>$row['name'],
	'slug'=>str_replace('_','-', PSU::createSlug( $row['name'] )),
	'activity_date'=>date('Y-m-d'));
	$query="INSERT	into psu_teacher_cert.constituent_positions(
								id,
								name,
								slug,
								activity_date
					)VALUES(
								:id,
								:name,
								:slug,
								:activity_date
					)";
		$action=PSU::db('banner')->Execute($query,$data);
}

//populate CONSTITUENTS

//first get information from constituent_saus
$sql="SELECT  sxrtuid_pidm id,
							sxrtuid_first_name first_name,
							sxrtuid_mi mi,
							sxrtuid_last_name last_name,
							sxrtuid_prefix prefix,
							sxrtuid_suffix suffix,
							sxrtuid_email email,
							sxrtuid_activity_date,
							decode(sxrtuid_deleted, 'Y', '1', NULL) deleted
				FROM  sxrtuid
		ORDER BY  sxrtuid_last_name,
							sxrtuid_first_name";
$results=PSU::db('banner')->Execute($sql);
while($row=$results->FetchRow())
{
	$data = array(
	'id'=>$row['id'],
	'fake_pidm'=>$row['id'],
	'first_name'=>trim($row['first_name']),
	'mi'=>trim($row['mi']),
	'last_name'=>trim($row['last_name']),
	'prefix'=>trim($row['prefix']),
	'suffix'=>trim($row['suffix']),
	'email'=>trim($row['email']),
	'delete_id'=>$row['deleted'],
	'activity_date'=>date('Y-m-d'));
	$query="INSERT into psu_teacher_cert.constituents(
								id,
								fake_pidm,
								first_name,
								mi,
								last_name,
								prefix,
								suffix,
								email,
								delete_id,
								activity_date
					)VALUES(
								:id,
								:fake_pidm,
								:first_name,
								:mi,
								:last_name,
								:prefix,
								:suffix,
								:email,
								:delete_id,
								:activity_date
					)";
	$action=PSU::db('banner')->Execute($query,$data);
}
$sql="SELECT  sxrtsid_pidm id,
							sxrtsid_first_name first_name,
							sxrtsid_mi mi,
							sxrtsid_last_name last_name,
							sxrtsid_prefix prefix,
							sxrtsid_suffix suffix,
							sxrtsid_email email,
							sxrtsid_activity_date,
							decode(sxrtsid_deleted, 'Y', '1', NULL) deleted
				FROM  sxrtsid
			 WHERE	NOT EXISTS(SELECT	fake_pidm FROM psu_teacher_cert.constituents WHERE fake_pidm = sxrtsid_pidm )
		ORDER BY  sxrtsid_last_name,
							sxrtsid_first_name";
$results=PSU::db('banner')->Execute($sql);
while($row=$results->FetchRow())
{
	$data = array(
	'id'=>$row['id'],
	'fake_pidm'=>$row['id'],
	'first_name'=>trim($row['first_name']),
	'mi'=>trim($row['mi']),
	'last_name'=>trim($row['last_name']),
	'prefix'=>trim($row['prefix']),
	'suffix'=>trim($row['suffix']),
	'email'=>trim($row['email']),
	'delete_id'=>$row['deleted'],
	'activity_date'=>date('Y-m-d'));
	$query="INSERT into psu_teacher_cert.constituents(
								id,
								fake_pidm,
								first_name,
								mi,
								last_name,
								prefix,
								suffix,
								email,
								delete_id,
								activity_date
					)VALUES(
								:id,
								:fake_pidm,
								:first_name,
								:mi,
								:last_name,
								:prefix,
								:suffix,
								:email,
								:delete_id,
								:activity_date
					)";
	$action=PSU::db('banner')->Execute($query,$data);
}
$sql="SELECT  distinct sxrtrtp_relation_pidm id,
							spriden_first_name first_name,
							spriden_mi mi,
							spriden_last_name last_name,
							spbpers_name_prefix prefix,
							spbpers_name_suffix suffix,
							goremal_email_address email
				FROM  sxrtrtp
						  JOIN spriden
							  ON spriden_pidm = sxrtrtp_relation_pidm
							 AND spriden_change_ind IS NULL
							LEFT JOIN spbpers
							  ON spbpers_pidm = sxrtrtp_relation_pidm
							LEFT JOIN goremal
							  ON goremal_pidm = sxrtrtp_relation_pidm
								AND goremal_emal_code = 'CA'
		ORDER BY  spriden_last_name,
							spriden_first_name";
$results=PSU::db('banner')->Execute($sql);
while($row=$results->FetchRow())
{
	$data = array(
	'id'=>$row['id'],
	'pidm'=>$row['id'],
	'first_name'=>$row['first_name'],
	'mi'=>$row['mi'],
	'last_name'=>$row['last_name'],
	'prefix'=>$row['prefix'],
	'suffix'=>$row['suffix'],
	'email'=>$row['email'],
	'activity_date'=>date('Y-m-d'));
	$query="INSERT into psu_teacher_cert.constituents(
								id,
								pidm,
								first_name,
								mi,
								last_name,
								prefix,
								suffix,
								email,
								activity_date
					)VALUES(
								:id,
								:pidm,
								:first_name,
								:mi,
								:last_name,
								:prefix,
								:suffix,
								:email,
								:activity_date
					)";
	$action=PSU::db('banner')->Execute($query,$data);
}



// populate CONSTITUENT SCHOOLS
$sql = "
	SELECT c.id constituent_id,
			   s.id school_id,
				 p.id position_id,
				 decode(sxrtsid_deleted, 'Y', '2', NULL) delete_id
		FROM sxrtsid
		     JOIN sxvtrtp
				   ON sxvtrtp_code = sxrtsid_trtp_code
		     JOIN psu_teacher_cert.constituents c
				   ON c.fake_pidm = sxrtsid_pidm
				 JOIN psu_teacher_cert.schools s
				   ON s.legacy_code = sxrtsid_sbgi_code
				 JOIN psu_teacher_cert.constituent_positions p
					 ON p.name = sxvtrtp_desc
";
$results=PSU::db('banner')->Execute($sql);
while($row=$results->FetchRow())
{
	$data = array(
	'id'=>9999,
	'constituent_id'=>$row['constituent_id'],
	'school_id'=>$row['school_id'],
	'position_id'=>$row['position_id'],
	'delete_id'=>$row['delete_id'],
	'end_date'=>'',
	'activity_date'=>date('Y-m-d'));
	$query="INSERT into psu_teacher_cert.constituent_schools(
								id,
								constituent_id,
								school_id,
								position_id,
								delete_id,
								end_date,
								activity_date
					)VALUES(
								:id,
								:constituent_id,
								:school_id,
								:position_id,
								:delete_id,
								:end_date,
								:activity_date
					)";
		$action=PSU::db('banner')->Execute($query,$data);
}


//populate CONSTITUENT SAUS
$sql = "
	SELECT c.id constituent_id,
			   s.id sau_id,
				 nvl(p.id, 1) position_id,
				 decode(sxrtuid_deleted, 'Y', '3', NULL) delete_id
		FROM sxrtuid
		     JOIN psu_teacher_cert.constituents c
				   ON c.fake_pidm = sxrtuid_pidm
				 JOIN psu_teacher_cert.saus s
				   ON s.legacy_code = sxrtuid_tsau_code
		     LEFT JOIN sxvtrtp
				   ON sxvtrtp_code = sxrtuid_trtp_code
				 LEFT JOIN psu_teacher_cert.constituent_positions p
					 ON p.name = sxvtrtp_desc
";
$results=PSU::db('banner')->Execute($sql);
while($row=$results->FetchRow())
{
	$data = array(
	'id'=>9999,
	'constituent_id'=>$row['constituent_id'],
	'sau_id'=>$row['sau_id'],
	'position_id'=>$row['position_id'],
	'delete_id'=>$row['delete_id'],
	'end_date'=>'',
	'activity_date'=>date('Y-m-d'));
	$query="INSERT into psu_teacher_cert.constituent_saus(
								id,
								constituent_id,
								sau_id,
								position_id,
								end_date,
								delete_id,
								activity_date
					)VALUES(
								:id,
								:constituent_id,
								:sau_id,
								:position_id,
								:end_date,
								:delete_id,
								:activity_date
					)";
		$action=PSU::db('banner')->Execute($query,$data);
}

//populate STUDENT CLINICAL FACULTY

$sql="SELECT  sgs.id student_gate_system_id,
							c.id constituent_id,
							sxrtrtp_attr_code association_attribute,
							sxrtrtp_activity_date start_date
				FROM sxrtrtp
						 JOIN psu_teacher_cert.student_gate_systems sgs
						   ON sgs.pidm = sxrtrtp_pidm
						 JOIN psu_teacher_cert.constituents c
						   ON c.pidm = sxrtrtp_relation_pidm";
$results=PSU::db('banner')->Execute($sql);
while($row=$results->FetchRow())
{
	$data = array(
	'id'=>9999,
	'student_gate_system_id'=>$row['student_gate_system_id'],
	'constituent_id'=>$row['constituent_id'],
	'association_attribute'=>$row['association_attribute'],
	'start_date'=> \PSU::db('banner')->BindDate( $row['start_date'] ),
	'end_date'=>'',
	'activity_date'=>date('Y-m-d'));
	$query="INSERT into psu_teacher_cert.student_clinical_faculty(
								id,
								student_gate_system_id,
								constituent_id,
								association_attribute,
								start_date,
								end_date,
								activity_date
					)VALUES(
								:id,
								:student_gate_system_id,
								:constituent_id,
								:association_attribute,
								:start_date,
								:end_date,
								:activity_date
					)";
		$action=PSU::db('banner')->Execute($query,$data);
}

//populate STUDENT SCHOOLS

$sql="SELECT  sgs.id student_gate_system_id,
							s.id school_id,
							sxrschl_term_code term_code,
							sxrschl_grade grade,
							sxrschl_interview interview_ind,
							sxrschl_placement placement,
							sxrschl_note notes
				FROM  sxrschl
							JOIN psu_teacher_cert.student_gate_systems sgs
							  ON sgs.pidm = sxrschl_pidm
							JOIN psu_teacher_cert.schools s
							  ON s.legacy_code = sxrschl_sbgi_code";
$results=PSU::db('banner')->Execute($sql);
while($row=$results->FetchRow())
{
	$data = array(
	'id'=>9999,
	'student_gate_system_id'=>$row['student_gate_system_id'],
	'school_id'=>$row['school_id'],
	'term_code'=>$row['term_code'] ?: \PSU\Student::getCurrentTerm('ug'),
	'grade'=>$row['grade'],
	'interview_ind'=>$row['interview_ind'],
	'placement'=>$row['placement'],
	'notes'=>$row['notes'],
	'activity_date'=>date('Y-m-d'));
	$query="INSERT into psu_teacher_cert.student_schools(
								id,
								student_gate_system_id,
								school_id,
								term_code,
								grade,
								interview_ind,
								placement,
								notes,
								activity_date
					)VALUES(
								:id,
								:student_gate_system_id,
								:school_id,
								:term_code,
								:grade,
								:interview_ind,
								:placement,
								:notes,
								:activity_date
					)";
		$action=PSU::db('banner')->Execute($query,$data);
}


//populate STUDENT_SCHOOL_CONSTITUENT

$sql="SELECT  DISTINCT gs.id student_gate_system_id,
							cs.id constituent_school_id,
							sxrtrsp_attr_code association_attribute,
							sxrtrsp_voucher voucher,
							sxrtrsp_voucher_date voucher_date,
							sxrtrsp_activity_date start_date
			  FROM sxrtrsp
				     JOIN psu_teacher_cert.constituents c
						   ON c.fake_pidm = sxrtrsp_relation_pidm
						JOIN psu_teacher_cert.student_gate_systems gs
							ON gs.pidm = sxrtrsp_pidm
							JOIN psu_teacher_cert.constituent_schools cs
							ON cs.constituent_id = c.id
							";
$results=PSU::db('banner')->Execute($sql);
while($row=$results->FetchRow())
{
	$data = array(
	'id'=>9999,
	'student_gate_system_id'=>$row['student_gate_system_id'],
	'constituent_school_id'=>$row['constituent_school_id'],
	'association_attribute'=>$row['association_attribute'],
	'voucher'=>$row['voucher'],
	'voucher_date'=>$row['voucher_date'],
	'start_date'=> \PSU::db('banner')->BindDate( $row['start_date'] ),
	'end_date'=>'',
	'activity_date'=>date('Y-m-d'));
	$query="INSERT into psu_teacher_cert.student_school_constit(
								id,
								student_gate_system_id,
								constituent_school_id,
								association_attribute,
								voucher,
								voucher_date,
								start_date,
								end_date,
								activity_date
					)VALUES(
								:id,
								:student_gate_system_id,
								:constituent_school_id,
								:association_attribute,
								:voucher,
								:voucher_date,
								:start_date,
								:end_date,
								:activity_date
					)";
		$action=PSU::db('banner')->Execute($query,$data);
}


\PSU::db('banner')->CompleteTrans();
