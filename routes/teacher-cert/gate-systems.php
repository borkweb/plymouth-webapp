<?php

use \PSU\TeacherCert\GateSystems,
	  \PSU\TeacherCert\GateSystem;

/**
 * redirect to force a slash
 */
respond( '', function( $request, $response ) {
	$response->redirect( $request->uri() . '/' );
});

/**
 * display gate_system browser
 */
respond( 'GET', '/', function( $request, $response, $app ) {
	$gate_systems = new GateSystems;
	$gate_systems->load();

	$app->tpl->assign( 'gate_systems', $gate_systems );
	$app->tpl->display( 'admin/gate-systems.tpl' );
});

/**
 * add gate_system browser
 */
respond( 'POST', '/', function( $request, $response, $app ) {
	$response->deny_to_readonly();

	$gate_system = new Gatesystem( $_POST );
	if( $gate_system->save() ) {
		$_SESSION['successes'][] = 'Gate System added successfully!';
	} else {
		$_SESSION['errors'][] = 'The Gate System failed to save.';
	}//end else

	$response->redirect( $request->uri() );
});

/**
 * view specific gate_system
 */
respond( 'GET', '/[i:gate_system]/[:action]?', function( $request, $response, $app ) {
	$gate_system = preg_replace( '/[^a-zA-Z0-9\-_]/', '', $request->param('gate_system') );
	$action = $request->param('action');

	$gate_system = new GateSystem( $gate_system );

	$app->tpl->assign( array(
		'gate_system' => $gate_system,
		'edit' => 'edit' == $action ? TRUE : FALSE,
	));

	$app->tpl->display( 'admin/gate-system.tpl' );
});
