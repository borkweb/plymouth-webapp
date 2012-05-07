<?php

use Guzzle\Service\Plugin\MockPlugin;
use Guzzle\Http\Message\Response;
use PSU\TeacherCert;

respond(function($request, $response, $app){
	if( false ) {
		$response->session('pidm', 175575);
		$app->permissions = new TeacherCert\Permissions(); // DEBUG
		$app->permissions->pidm = 175575;
	}

	if( false ) {
		$app->permissions = new TeacherCert\Permissions(); // DEBUG
		$app->permissions->pidm = $request->session('pidm');
		$app->permissions->grant( 'faculty' );
		$app->permissions->pidm = 200443;
	}
});

respond( 'GET', '/', function( $request, $response, $app ) {
	$mock = new MockPlugin( array('mock/gate-systems.txt') );
	$app->client->getEventManager()->attach( $mock, 9999 );
});

respond( 'GET', '/gate-system/[:gate_system]/[:student_gate_system]', function( $request, $response, $app ) {
	$mock = new MockPlugin( array('mock/gate-system.txt', 'mock/students-gate-systems.txt', 'mock/user_200443.txt') );
	$app->client->getEventManager()->attach( $mock, 9999 );
});

respond( 'GET', '/api/students', function( $request, $response, $app ) {
	$mock = new MockPlugin( array('mock/students.txt') );
	$app->client->getEventManager()->attach( $mock, 9999 );
});
