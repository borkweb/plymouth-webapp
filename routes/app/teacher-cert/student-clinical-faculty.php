<?php

respond( '/[i:sgs_id]/[*]', function( $request, $response, $app ) {
	$sgs_id = $request->param( 'sgs_id' );
	
	$app->populate( new \PSU\TeacherCert\Student\GateSystem( $sgs_id ) );
});

respond( 'POST', '/[i:sgs_id]/[*]', function( $request, $response, $app ) {
	$response->deny_to_readonly();

	$id = $request->param( 'clinical_faculty_id' );

	$args = array(
		'student_gate_system_id' => $app->student_gate_system->id,
		'constituent_id' => $id,
	);

	$faculty = new PSU\TeacherCert\Student\ClinicalFaculty( $args );
	
	if( $faculty->save() ) {
		$_SESSION['successes'][] = 'The new faculty member has been added to this gate system.';
	} else {
		$_SESSION['errors'][] = 'There was an error adding that faculty member.';
	}

	$response->back();
});
