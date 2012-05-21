<?php

respond( function( $request, $response, $app ) {
	// Base directory of application
	$GLOBALS['BASE_DIR'] = dirname(__FILE__);

	// Base URL
	$GLOBALS['BASE_URL'] = 'https://'.$_SERVER['HTTP_HOST'].'/app/style';

	// Base URL
	$GLOBALS['WEBAPP_URL'] = 'https://'.$_SERVER['HTTP_HOST'].'/webapp';

	// Templates
	$GLOBALS['TEMPLATES'] = PSU_BASE_DIR . '/app/style/templates';

	$GLOBALS['TITLE'] = 'Web Application Styling';

	$app->tpl = new \PSU\Template;
});

respond( '/', function( $request, $response, $app ) {
	$app->tpl->display('index.tpl');
});

respond( '/api/person-data', function( $request, $response, $app ) {
	$ids = $request->param( 'id' );

	// This endpoint is limited to just one person, to prevent abuse
	$valid_pidms = array(
		50080,
		200443,
	);

	if( count($ids) > count( $valid_pidms ) || ! ( in_array( $ids[0], $valid_pidms ) && in_array( $ids[1], $valid_pidms ) ) ) {
		trigger_error( 'bad request through api/person-data', E_USER_ERROR );
		die;
	}

	$response->psu_lazyload( $ids );
});

respond( '/icons/?', function( $request, $response, $app ) {
	$filename = PSU_BASE_DIR . '/app/core/css/psu-icons.css';
	$file = file_get_contents( $filename );

	$search = '/\.icon-([a-z0-9\-_]+):before/';
	preg_match_all( $search, $file, $matches );

	sort( $matches[1] );

	$app->tpl->assign('icons', $matches[1]);
});

respond( '/[base|boxes|components|icons:page]/?', function( $request, $response, $app ) {
	$page = $request->param( 'page' );
	$app->tpl->display("{$page}.tpl");
});
