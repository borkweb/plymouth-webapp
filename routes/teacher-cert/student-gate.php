<?php

use PSU\TeacherCert\Student\Gate as StudentGate,
	PSU\TeacherCert\Student\GateSystem as StudentGateSystem,
	PSU\TeacherCert;

respond( '/add/[:student_gate_system]/[:gate]', function( $request, $response, $app ) {
	$student_gate_system_id = $request->param('student_gate_system');
	$gate_id = $request->param('gate');

	$app->populate( 'student_gate_system', new StudentGateSystem( $student_gate_system_id ) );
	$app->populate( 'gate', new TeacherCert\Gate( $gate_id ) );

	$gates = $app->student_gate_system->gates();

	$url = "{$GLOBALS['BASE_URL']}/student-gate/";
	if( ! $gates[ $app->gate->id ]->student_gate_id ) {
		$args = array(
			'the_id' => 999,
			'student_gate_system_id' => $app->student_gate_system->id,
			'gate_id' => $gate_id,
		);
		$gate = new TeacherCert\Student\Gate( $args );
		if( ! $gate->save( 'insert' ) ) {
			$_SESSION['errors'][] = "There was an error loading this student's Gate for editing. Please contact ITS.";
		} else {
			$response->redirect( $url . $gate->id );
		}//end if
	}//end if

	$response->redirect( $url . $gates[ $app->gate->id ]->student_gate_id );
});

respond( '/[:student_gate]', function( $request, $response, $app ) {
	$id = $request->param('student_gate');
	$app->populate( StudentGate::get( $id ) );
});

respond( 'GET', '/[:student_gate]', function( $request, $response, $app ) {
	$app->tpl->display('student-gate.tpl');
});

respond( 'POST', '/[:student_gate]', function( $request, $response, $app ) {
	$response->deny_to_readonly();

	$id = $request->param('student_gate');

	$app->gate = StudentGate::get( $id );
	$app->tpl->assign( 'gate', $app->gate );

	$gate_system_url = sprintf( "%s/gate-system/%s/%d", $GLOBALS['BASE_URL'],
		$app->gate_system->slug, $app->gate->student_gate_system()->id );

	// Cancel, just redirect out
	if( $request->param('cancel') ) {
		$response->redirect($gate_system_url);
	}

	$answers = $request->param( 'checklist_item' );
	$app->gate->set_answers( $answers );

	$response->redirect($gate_system_url);
});
