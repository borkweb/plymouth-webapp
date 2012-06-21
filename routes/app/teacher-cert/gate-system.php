<?php

/**
 * teacher-cert/gate-system/*
 */

use PSU\TeacherCert\Student\GateSystem as StudentGateSystem,
	PSU\Student\Test,
	PSU\TeacherCert;

respond( '/[:gate_system]/?[*]?', function( $request, $response, $app ) {
	$gate_system_id = $request->param('gate_system');

	$app->populate( new TeacherCert\GateSystem( $gate_system_id ) );

	$app->breadcrumbs->push( new \PSU\Template\Breadcrumb( $app->gate_system->name, $app->resolver( $app->gate_system ) ) );
});

respond( '/[:gate_system]/?', function( $request, $response, $app ) {
	$app->tpl->display('gate-system.tpl');
});

respond( '/[:gate_system]/gate/[:gate]/?[*]?', function( $request, $response, $app ) {
	$gate_id = $request->param('gate');

	$gate = TeacherCert\Gate::get( $gate_id );
	$app->populate( 'gate', $gate );

	$app->breadcrumbs->push( $app->gate->name );

	$app->tpl->display('gate.tpl');
});

respond( 'POST', '/[:gate_system](/gate/[:gate])?/add-student/?', function( $request, $response, $app ) {
	$response->deny_to_readonly();

	$student_id = $request->param('student_id');

	$person = \PSUPerson::get( $student_id );

	$redirect = $GLOBALS['BASE_URL'] . '/gate-system/' . $app->gate_system->slug;
	if( $gate ) {
		$redirect .= '/gate/' . $gate->slug;
	}//end if

	if( $app->gate_system->level_code == 'ug' && !$person->student->ug ) {
		$_SESSION['errors'][] = "{$person->formatName('l, f m')} is not an active UG student!";

		$response->redirect( $redirect, 400 );
	} elseif( $app->gate_system->level_code == 'gr' && !$person->student->gr ) {
		$_SESSION['errors'][] = "{$person->formatName('l, f m')} is not an active GR student!";

		$response->redirect( $redirect, 400 );
	}//end if

	$student = new TeacherCert\Student( $person->pidm );
	$in_system = false;

	if( ! $student->gate_systems( $app->gate_system ) ) {
		$data = array(
			'the_id' => 9999,
			'pidm' => $person->pidm,
			'gate_system_id' => $app->gate_system->id,
			'approve_date' => time(),
		);

		$system = new TeacherCert\Student\GateSystem( $data );
		if( $system->save() ) {
			$_SESSION['successes'][] = "{$person->formatName('l, f m')} has been added to the {$app->gate_system->name} Gate System!";
		} else {
			$_SESSION['errors'][] = "{$person->formatName('l, f m')} could not be added to the {$app->gate_system->name} Gate System. Please contact ITS.";
		}//end else
	} else {
		$_SESSION['errors'][] = "{$person->formatName('l, f m')} is already a member of the {$app->gate_system->name} Gate System.";
	}//end else

	$response->redirect( $redirect );
});

respond( '/[:gate_system]/[search:action]', function( $request, $response, $app ) {
	$action = $request->param('action');

	if( 'search' === $action ) {
		$app->breadcrumbs->push( 'Search' );

		$q = $request->param('q');

		// Is $q a PSU ID?
		if( strlen($q) === 9 && ctype_digit($q) ) {
			$response->redirect( $GLOBALS['BASE_URL'] . '/student/' . $q );
		}

		$query = new TeacherCert\Population\GateSystemStudents( $app->gate_system->id );
		$factory = new TeacherCert\Population\StudentFactory;
		$population = new PSU_Population( $query, $factory );

		$population->query( array( 'filter' => $q ) );
		$app->tpl->assign( 'population', $population );

		$app->tpl->display( 'results.tpl' );
	}
});

respond( '/[:gate_system]/[i:student_gate_system]/[:section]?', function( $request, $response, $app ) {
	$sgs_id = $request->param('student_gate_system');
	$section = $request->param('section');

	if( 'praxis' === $section ) {
		$test_model = new PSU\TeacherCert\Model\Student\Test;
		$app->populate( 'test_model', $test_model );
	}

	$rc = TeacherCert\ActiveRecord::$rowcache = new TeacherCert\RowCache;
	$rc->cache( 'PSU\TeacherCert\ChecklistItem' );
	$rc->cache( 'PSU\TeacherCert\ChecklistItemAnswer' );

	$app->populate( StudentGateSystem::get( $sgs_id ) );
	$app->tpl->page_title = $app->student->person()->formatName();

	$app->populate( 'student_gate_model', new TeacherCert\Model\Student\GateSystem );
	$app->student_gate_model->form( get_object_vars( $app->student_gate_system ) );
});

respond( 'POST', '/[:gate_system]/[i:student_gate_system]', function( $request, $response, $app ) {
	$response->deny_to_readonly();

	$action = $request->param('action');

	if( 'complete' === $action ) {
		$app->student_gate_system->exit_date = null;
		$app->student_gate_system->complete_date = time();
		$app->student_gate_system->save();
	} elseif( 'exit' === $action ) {
		$app->student_gate_system->exit_date = time();
		$app->student_gate_system->complete_date = null;
		$app->student_gate_system->save();
	} elseif( 'incomplete' === $action ) {
		$app->student_gate_system->complete_date = null;
		$app->student_gate_system->save();
	} elseif( 'reopen' === $action ) {
		$app->student_gate_system->exit_date = null;
		$app->student_gate_system->save();
	} elseif( 'teaching-term' === $action ) {
		$app->student_gate_system->teaching_term_code = $request->param('teaching_term_code');
		$app->student_gate_system->save();
	} else {
		$action_e = htmlentities($action);
		$_SESSION['errors'][] = "Unknown action \"{$action_e}\"";
	}

	$response->back();
});

respond( 'POST', '/[:gate_system]/[i:student_gate_system]/praxis', function( $request, $response, $app ) {
	$response->deny_to_readonly();

	$app->test_model->form( $_POST );

	// TODO: check validation stuff

	$args = $app->test_model->form();
	$args['pidm'] = $app->student->pidm;

	$test = new Test( $args );

	if( $test->save() ) {
		$_SESSION['successes'][] = 'Test scores have been updated.';
	} else {
		$_SESSION['errors'][] = 'Could not save the provided test score.';
	}

	$response->refresh();
});

respond( 'GET', '/[:gate_system]/[i:student_gate_system]/praxis', function( $request, $response, $app ) {
	$cancel_url = sprintf("%s/gate-system/%s/%d",
		$GLOBALS['BASE_URL'], $app->student_gate_system->gate_system()->slug,
		$app->student_gate_system->id );

	$app->breadcrumbs->push( new \PSU\Template\Breadcrumb( sprintf( '%s (%s)', $app->student_gate_system->student()->person()->formatName('f l'), $app->student_gate_system->student()->person()->id ), $app->resolver( $app->student_gate_system ) ) );
	$app->breadcrumbs->push( 'Update Praxis' );

	$app->tpl->assign(array(
		'cancel_url' => $cancel_url,
		'action' => 'Save',
	));

	$app->tpl->display("student-praxis.tpl");
});

respond( 'GET', '/[:gate_system]/[i:student_gate_system]', function( $request, $response, $app ) {
	$app->populate( 'teachers', new PSU\TeacherCert\Constituents );

	$app->breadcrumbs->push( new \PSU\Template\Breadcrumb( sprintf( '%s (%s)', $app->student_gate_system->student()->person()->formatName('f l'), $app->student_gate_system->student()->person()->id ) ) );

	$app->tpl->display('student.tpl');
});
