<?php

use PSU\TeacherCert;

respond( '/[i:sgs_id]/[*]', function( $request, $response, $app ) {
	$sgs_id = $request->param('sgs_id');

	$app->populate( TeacherCert\Student\GateSystem::get( $sgs_id ) );

	$app->populate( 'schools', new TeacherCert\Schools );
	$app->populate( 'school_model', new TeacherCert\Model\Student\School );
	$app->populate( 'teacher_model', new TeacherCert\Model\Student\CooperatingTeacher );
	$app->populate( 'cancel_url', $app->resolver( $app->student_gate_system ) );

	$app->breadcrumbs->push( new PSU\Template\Breadcrumb( $app->student_gate_system->gate_system()->name, $app->resolver( $app->gate_system ) ) );
	$app->breadcrumbs->push( new PSU\Template\Breadcrumb( sprintf( '%s (%s)', $app->student_gate_system->student()->person()->formatName('f l'), $app->student_gate_system->student()->person()->id ), $app->resolver( $app->student_gate_system ) ) );

	$app->populate_constituents = function() use ($app) {
		$rc = TeacherCert\ActiveRecord::$rowcache = new TeacherCert\RowCache;
		$where = sprintf( 'id IN (SELECT constituent_id FROM psu_teacher_cert.constituent_schools WHERE school_id = %d)', $app->student_school->school_id );
		$rc->cache( 'PSU\TeacherCert\Constituent', 'id', $where );

		$constituent_schools = new TeacherCert\CooperatingTeachers( $app->student_school->school_id );
		$schools_options = TeacherCert\Model::collection( $constituent_schools, 'name=constituent_name' );

		usort( $schools_options, function($a, $b){
			return strnatcasecmp( $a[1], $b[1] );
		});

		$app->teacher_model->constituent_school_id->options = $schools_options;
	};
});

respond( 'GET', '/[i:sgs_id]/[add|edit|delete:action]/[i:ss_id]?', function( $request, $response, $app ) {
	$ss_id = $request->param( 'ss_id' );

	if( 'edit' === $request->param( 'action' ) ) {
		$app->populate( 'student_school', TeacherCert\Student\School::get( $ss_id ) );
		$app->populate_constituents();

		// populate form with existing school data
		$f = get_object_vars($app->student_school);
		if( isset( $_SESSION['tcert-student-school-save'] ) ) {
			$sf = $_SESSION['tcert-student-school-save'];
			unset( $sf['school_id'] );
			$f = PSU::params( $sf, $f );
		}
		$app->school_model->form( $f );

		unset( $_SESSION['tcert-student-school-save'] );

		$app->school_model->school_id->readonly = true;

		$app->breadcrumbs->push( new PSU\Template\Breadcrumb( 'Edit School' ) );

		$app->populate( 'delete_url', $app->resolver( $app->student_school, 'delete' ) );
	} elseif( 'delete' === $request->param( 'action' ) ) {
		if( ! $app->permissions->can_delete( $app->student_gate_system->gate_system()->level_code ) ) {
			$response->denied();
		}

		$ss = TeacherCert\Student\School::get( $ss_id );
		$ss->delete();

		$_SESSION['messages'][] = 'Student school record has been deleted.';

		$response->redirect( $app->resolver( $app->student_gate_system ) );
	} else {
		// new school; might have to repopulate form data from failed save
		$app->school_model->form( $_SESSION['tcert-student-school-save'] );
		unset($_SESSION['tcert-student-school-save']);

		$app->breadcrumbs->push( new PSU\Template\Breadcrumb( 'Add School' ) );
	}

	$app->tpl->display( 'student-school.tpl' );
});

respond( 'POST', '/[i:sgs_id]/[*]', function( $request, $response, $app ) {
	$response->deny_to_readonly();

	$sgs_id = $request->param('sgs_id');

	if( 'teacher' === $request->param('target') ) {
		$action = $request->param('action');

		if( 'add-teacher' === $action ) {
			$ss_id = $request->param('student_school_id');
			$app->populate( 'student_school', TeacherCert\Student\School::get( $ss_id ) );
			$app->populate_constituents();

			$app->teacher_model->form( $request->params() );
			$app->teacher_model->student_gate_system_id = $sgs_id;

			$teacher = new TeacherCert\Student\CooperatingTeacher( $app->teacher_model->form() );
		} else {
			// update existing teacher
			$teacher_id = $request->param('id');
			$teacher = new TeacherCert\Student\CooperatingTeacher( $teacher_id );

			if( 'Add Voucher' === $action ) {
				$teacher->add_voucher();
			} elseif( 'Remove Voucher' === $action ) {
				$teacher->remove_voucher();
			} elseif( 'Remove Teacher' === $action ) {
				$teacher->delete();
			}
		}

		if( $teacher->save() ) {
			$_SESSION['successes'][] = 'Your changes have been saved.';
		} else {
			$_SESSION['errors'][] = 'An error occured saving your changes: ' . htmlentities( PSU::db('banner')->ErrorMsg() );
		}

		$response->refresh();
	}

	$app->school_model->form( $request->params() );

	if( $id = $request->param('id') ) {
		$action = 'edit';
		$school = TeacherCert\Student\School::get( $id );

		// dump the incoming form data back into the School
		$fields = $app->school_model->form();

		// read-only fields
		unset($fields['school_id']);

		foreach( $fields as $key => $value ) {
			$school->$key = $value;
		}
	} else {
		$action = 'add';
		$school = new TeacherCert\Student\School( $app->school_model->form() );
		$school->student_gate_system_id = $sgs_id;
	}

	$uri = sprintf( "%s/student-school/%d/edit/%d", $GLOBALS['BASE_URL'],
		$app->student_gate_system->id, $school->id );

	if( $success = $school->save() ) {
		if( 'edit' === $action ) {
			$_SESSION['successes'][] = 'Your changes have been saved.';
		} else {
			$_SESSION['successes'][] = 'The school has been added to this gate system.';

			// need to populate new id into url
			$uri = sprintf( "%s/student-school/%d/edit/%d", $GLOBALS['BASE_URL'],
				$app->student_gate_system->id, $school->id );
		}

		unset( $_SESSION['tcert-student-school-save'] );
	} else {
		$_SESSION['errors'][] = 'Your changes could not be saved: ' . PSU::db('banner')->ErrorMsg();
		$_SESSION['tcert-student-school-save'] = $app->school_model->form();

		// Operation failed; might need to return the user back to
		// the "add a school" screen.
		if( 'edit' !== $action ) {
			$uri = sprintf( "%s/student-school/%d/add", $GLOBALS['BASE_URL'],
				$app->student_gate_system->id, $school->id );
		}
	}

	$response->redirect( $uri );
});
