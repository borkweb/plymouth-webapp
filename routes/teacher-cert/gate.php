<?php

use \PSU\TeacherCert\Gates,
	\PSU\TeacherCert\Gate,
	\PSU\TeacherCert\Model\Gate as GateModel;

/**
 * namespace-wide initialization
 */
respond( '[*]', function( $request, $response, $app ) {
	$app->gate_model = new GateModel;
	$app->tpl->assign('gate_model', $app->gate_model);
});

/**
 * redirect to force a slash
 */
respond( '', function( $request, $response ) {
	$response->redirect( $request->uri() . '/' );
});

/**
 * display gate browser
 */
respond( 'GET', '/', function( $request, $response, $app ) {
	$gates = new Gates;
	$gates->load();

	$app->tpl->assign( 'gates', $gates );
	$app->tpl->display( 'gates.tpl' );
});

/**
 * add district browser
 */
respond( 'POST', '/', function( $request, $response, $app ) {
	$response->deny_to_readonly();

	try {
		$app->gate_model->form( $_POST );

		if( ! $app->gate_model->complete() ) {
			throw new \PSU\Model\IncompleteException;
		}//end if

		$app->gate = new Gate( $app->gate_model->form() );

		if( ! $app->gate->save() ) {
			throw new \Exception('The Gate failed to save.');
		}//end else

		$_SESSION['successes'][] = 'Gate added successfully!';
	} catch( \PSU\Model\ValidationException $e ) {
		$_SESSION['errors'][] = $e->getMessage();
	} catch( \PSU\Model\IncompleteException $e ) {
		$app->form_incomplete_errors( $app->gate_model );
	} catch( \Exception $e ) {
		$_SESSION['errors'][] = $e->getMessage();
	}//end catch

	$response->redirect( $request->uri() );
});

/**
 * catch-all for viewing/editing a gate
 */
respond( '/[i:gate](/[edit:action])?', function( $request, $response, $app ) {
	$app->action = $request->param('action');

	$gate = preg_replace( '/[^a-zA-Z0-9\-_]/', '', $request->param('gate') );
	$app->gate = new Gate( $gate );

	if( 'edit' !== $app->action ) {
		$app->gate_model->readonly( true );
	}

	$app->gate_model->form( get_object_vars( $app->gate ) );
});

/**
 * view or display edit form for gate
 */
respond( 'GET', '/[i:gate](/[edit:action])?', function( $request, $response, $app ) {
	$app->tpl->assign( array(
		'gate' => $app->gate,
		'edit' => 'edit' == $app->action ? TRUE : FALSE,
	));
	$app->tpl->display( 'gate.tpl' );
});

/**
 * edit district
 */
respond( 'POST', '/[i:gate]/edit', function( $request, $response, $app ) {
	$response->deny_to_readonly();

	try{
		$app->gate_model->form( $_POST );
		$post = $app->gate_model->form();

		foreach( $post as $field => $value ) {
			$app->gate->$field = $value;
		}//end foreach

		if( ! $app->gate->save() ) {
			throw new \Exception('The Gate failed to update.');
		}//end else

		$_SESSION['successes'][] = 'The ' . $app->gate->name . ' Gate has been updated successfully!';
	} catch( \PSU\Model\ValidationException $e ) {
		$_SESSION['errors'][] = $e->getMessage();
	} catch( \PSU\Model\IncompleteException $e ) {
		$app->form_incomplete_errors( $app->gate_model );
	} catch( \Exception $e ) {
		$_SESSION['errors'][] = $e->getMessage();
	}//end catch

	$response->redirect( $GLOBALS['BASE_URL'] . '/gates/' . $app->gate->id );
});
