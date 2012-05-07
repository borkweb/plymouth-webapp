<?php

use PSU\TeacherCert;

/**
 * Intended to be called via Ajax by JavaScript; return just the body using
 * a 200 OK.
 */

respond( 'GET', '/students', function( $request, $response, $app ) {
	header('Content-type: application/json');

	$gate_system = $request->param( 'qry-students-gs' );
	$filter = $request->param( 'q' );
	$limit = $request->param( 'limit', 20 );

	$query = new TeacherCert\StudentQuery( compact('gate_system', 'filter') );
	$query->query();

	$gate_system = TeacherCert\GateSystem::get( $gate_system );

	$result = array(
		'data' => array(
			'gate_system' => array(
				'name' => $gate_system->name,
				'slug' => $gate_system->slug,
				'id' => $gate_system->id,
			),
			'filter' => $filter,
			'students' => array(),
		),
	);

	$i = 0;
	foreach( $query as $student ) {
		$sgs = $student->gate_systems( $gate_system );

		// Student must be in selected gate system
		if( false == $sgs ) {
			continue;
		}

		$result['data']['students'][] = array(
			'last_name' => $student->person()->last_name,
			'first_name' => $student->person()->first_name,
			'pidm' => $student->pidm,
			'id' => $student->person()->id,
			'sgs_id' => $sgs->id,
		);

		if( $i++ >= $limit ) {
			break;
		}
	}

	echo json_encode($result);
});

respond( 'GET', '/checklist-items', function( $request, $response, $app ) {
	header('Content-type: application/json');

	$response = $app->client->get('teacher-cert/checklist-items')->send();
	echo $response->getBody();
});
