<?php

use PSU\TeacherCert;

respond( '/', function( $request, $response, $app ) {
	if( ! $app->permissions->pidm ) {
		die( 'Could not find your user identifier.' );
	}

	$student = PSUPerson::get( $app->permissions->pidm );
	$response->redirect( $GLOBALS['BASE_URL'] . '/student/' . $student->id );
});

respond( '/[:psu_id]/?[*]?', function( $request, $response, $app ){
	if( ! $app->permissions->pidm ) {
		die( 'Could not find your user identifier.' );
	}

	$person = PSUPerson::get( $request->param('psu_id') );

	if( $app->permissions->pidm != $person->pidm &&
		! $app->permissions->can_search() ) {
		$response->denied();
	}

	$app->populate( new TeacherCert\Student( $person->pidm ) );
});

respond( '/[:psu_id]', function( $request, $response, $app ){
	$app->tpl->display( 'student-gate-systems.tpl' );
});

respond( '/[:psu_id]/[i:sgs_id]', function( $request, $response, $app ){
	$sgs_id = $request->param('sgs_id');

	$student_gate_system = new TeacherCert\Student\GateSystem( $sgs_id );

	// does current user match the requested gate system?
	if( $student_gate_system->pidm != $app->student->pidm ) {
		$response->denied();
	}

	$app->populate( $student_gate_system );

	$app->populate( 'student_gate_model', new TeacherCert\Model\Student\GateSystem );
	$app->student_gate_model->form( get_object_vars( $app->student_gate_system ) );
	$app->student_gate_model->readonly( true );

	$app->tpl->body_style_classes[] = 'tcert-readonly';
	$app->tpl->display( 'student.tpl' );
});
