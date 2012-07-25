<?php

ini_set('memory_limit', -1);

respond(function( $request, $response, $app ) {
	$app->running = array();
	$app->running['contract'] = shell_exec('ps -ef | grep payment_plan_contract.php | grep -v grep');
	$app->running['disbursement'] = shell_exec('ps -ef | grep payment_plan_disbursement.php | grep -v grep');

	if( $app->running['contract'] ) {
		$_SESSION['messages'][] = 'The Payment Plan Contract processing script is currently running. Reload to check the status.';
		$app->tpl->assign('contracts_processing', true);
	}//end if

	if( $app->running['disbursement'] ) {
		$_SESSION['messages'][] = 'The Payment Plan Disbursement processing script is currently running. Reload to check the status.';
		$app->tpl->assign('disbursements_processing', true);
	}//end if
});

respond( '/?', function( $request, $response, $app ) {
	$contracts = new PSU\AR\PaymentPlan\Feed\Contracts( 10 );
	$disbursements = new PSU\AR\PaymentPlan\Feed\Disbursements( 4 );

	$app->tpl->assign( 'date_format', '%b %e @ %l:%M %P' );
	$app->tpl->assign( 'contracts', $contracts );
	$app->tpl->assign( 'disbursements', $disbursements );
	$app->tpl->display('payment-plans.tpl');
});

respond('/process/[contract|disbursement:script]', function( $request, $response, $app ) {
	$script = $request->script;
	if( $app->running[ $script ] ) {
		PSU::redirect( $GLOBALS['BASE_URL'] . '/payment-plans' );
	}//end if

	$user = PSU::isDev() ? 'nrporter' : 'webguru';

	if( PSU::isDev() && ! IDMObject::authZ('permission', 'mis') ) {
		$_SESSION['errors'][] = 'Only MIS can run this script in development';
		PSU::redirect( $GLOBALS['BASE_URL'] . '/payment-plans' );
	}//end if

	if( 'contract' == $script ) {
		$command ='/usr/local/bin/php ~'.$user.'/scripts/payment_plan_'.$script.'.php -i '.strtolower( PSU::db('banner')->database ) . ' &';
	} else {
		$command ='/usr/local/bin/php ~'.$user.'/scripts/payment_plan_'.$script.'.php --instance='.strtolower( PSU::db('banner')->database ) . ' &';
	}//end else

	echo shell_exec( $command );
	
	$_SESSION['successes'][] = 'The Payment Plan '.ucwords( $script ).' processing script has begun.  Please check back shortly.';

	PSU::redirect( $GLOBALS['BASE_URL'] . '/payment-plans' );
});
