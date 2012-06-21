<?php

use \PSU\TeacherCert\Gates as Gates,
	  \PSU\TeacherCert\Gate as Gate;

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
 * add gate browser
 */
respond( 'POST', '/', function( $request, $response, $app ) {
	$gate = new Gate( $_POST );
	if( $gate->save() ) {
		$_SESSION['successes'][] = 'Gate added successfully!';
	} else {
		$_SESSION['errors'][] = 'The Gate failed to save.';
	}//end else

	$response->redirect( $request->uri() );
});

/**
 * view specific gate
 */
respond( 'GET', '/[i:gate](/[:action])?', function( $request, $response, $app ) {
	$gate = preg_replace( '/[^a-zA-Z0-9\-_]/', '', $request->param('gate') );
	$action = $request->param('action');

	$gate = new Gatesystem( $gate );

	$app->tpl->assign( array(
		'gate' => $gate,
		'edit' => 'edit' == $action ? TRUE : FALSE,
	));
	$app->tpl->display( 'gate.tpl' );
});
