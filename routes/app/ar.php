<?php

respond( function( $request, $response, $app ) {
	PSU::session_start();

	// Base directory of application
	$GLOBALS['BASE_DIR'] = dirname(__FILE__);

	// Base URL
	$GLOBALS['BASE_URL'] = 'https://'.$_SERVER['HTTP_HOST'].'/app/ar';

	// Base URL
	$GLOBALS['WEBAPP_URL'] = 'https://'.$_SERVER['HTTP_HOST'].'/webapp';

	// Templates
	$GLOBALS['TEMPLATES'] = PSU_BASE_DIR . '/app/ar/templates';

	$GLOBALS['TITLE'] = 'Student Account Services Dashboard';

	IDMObject::authN();

	if( ! IDMObject::authZ('permission', 'mis') && ! IDMObject::authZ('role', 'bursar') ) {
		die('You do not have access to this application.');
	}

	$app->tpl = new \PSU\Template;
});

respond( '/?', function( $request, $response, $app ) {
	$contract = new PSU\AR\PaymentPlan\Feed\Contracts( 4 );
	$disbursement = new PSU\AR\PaymentPlan\Feed\Disbursements( 2 );

	$types = array(
		'contract',
		'disbursement',
	);
	foreach( $types as $type ) {
		$report[ $type ] = array();
		foreach( $$type as $feed ) {
			$report[ $type ]['invalid_id'] += $feed->invalid_id_count();
			$report[ $type ]['unprocessed'] += $feed->date_processed_timestamp() ? 0 : 1;

			if( $diff = $feed->processed_difference() ) {
				$report[ $type ]['difference'][ $feed->id ] = $diff;
			}//end if
		}//end foreach
	}//end foreach

	$ecommerce = PSU\Ecommerce::pending_files();
	$pending_ecommerce = $ecommerce ? count( $ecommerce ) : 0;

	$app->tpl->assign( 'contracts', $report['contract'] );
	$app->tpl->assign( 'disbursements', $report['disbursement'] );
	$app->tpl->assign( 'pending_ecommerce', $pending_ecommerce );

	$app->tpl->display('index.tpl');
});


with( '/ecommerce', __DIR__ . '/ar/ecommerce.php' );
with( '/payment-plans', __DIR__ . '/ar/payment-plans.php' );
with( '/schedule', __DIR__ . '/ar/schedule.php' );
