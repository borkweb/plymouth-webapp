<?php

respond( '/[i:sgs_id]/remove-clinical_faculty/[i:scf_id]/?', function( $request, $response, $app ) {
	$response->deny_to_readonly();

	$sgs_id = $request->param( 'sgs_id' );
	$scf_id = $request->param( 'scf_id' );

	$faculty = PSU\TeacherCert\Student\ClinicalFaculty::get( $scf_id );
	
	if( $faculty->delete() ) {
		$_SESSION['successes'][] = 'The faculty member has been removed from this gate system.';
	} else {
		$_SESSION['errors'][] = 'There was an error removing that faculty member.';
	}
});

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
