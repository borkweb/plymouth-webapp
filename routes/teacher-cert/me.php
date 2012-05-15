<?php

use PSU\TeacherCert;

respond( function( $request, $response, $app ){
	if( ! $app->permissions->pidm ) {
		die( 'Could not find your user identifier.' );
	}

	$app->populate( new TeacherCert\Student( $app->permissions->pidm ) );
});

respond( '/', function( $request, $response, $app ){
	$app->tpl->display( 'me-gates.tpl' );
});

respond( '/[i:sgs_id]', function( $request, $response, $app ){
	$sgs_id = $request->param('sgs_id');

	$student_gate_system = new TeacherCert\Student\GateSystem( $sgs_id );

	// does current user match the requested gate system?
	if( $student_gate_system->pidm != $app->permissions->pidm ) {
		$response->denied();
	}

	$app->populate( $student_gate_system );

	$app->populate( 'student_gate_model', new TeacherCert\Model\Student\GateSystem );
	$app->student_gate_model->form( get_object_vars( $app->student_gate_system ) );
	$app->student_gate_model->readonly( true );

	$app->tpl->body_style_classes[] = 'tcert-student-view';
	$app->tpl->display( 'me-gate-system.tpl' );
});
